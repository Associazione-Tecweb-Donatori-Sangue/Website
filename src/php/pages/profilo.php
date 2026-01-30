<?php
require_once "../utility.php";
require_once "../db.php";

// 1. Controllo sicurezza: solo utenti loggati
requireLogin();

// Se è ADMIN, reindirizzo al profilo admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: profilo_admin.php");
    exit();
}

// 2. Carico il template HTML
$paginaHTML = caricaTemplate('profilo.html');

// --- LOGICA GESTIONE FOTO PROFILO ---
$fotoPath = "../../images/profilo.jpg"; 
$isDefaultClass = "is-default";

try {
    $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user && !empty($user['foto_profilo'])) {
        $nomeFile = $user['foto_profilo'];
        $percorsoFisico = "../../images/profili/" . $nomeFile;
        
        if (file_exists($percorsoFisico)) {
            $fotoPath = $percorsoFisico . "?v=" . time();
            $isDefaultClass = ""; 
        }
    }
} catch (PDOException $e) {
    // Errore DB: resta default
}

$paginaHTML = str_replace('[FOTO_PROFILO]', htmlspecialchars($fotoPath), $paginaHTML);
$paginaHTML = str_replace('[CLASS_DEFAULT]', $isDefaultClass, $paginaHTML);

// --- SEZIONE DATI DONATORE ---
$htmlDonatore = "";
try {
    $stmt = $pdo->prepare("SELECT * FROM donatori WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $datiDonatore = $stmt->fetch();

    if (!$datiDonatore) {
        $htmlDonatore = '<div class="btn-wrapper"><a href="/php/pages/registrazione_donatore.php" class="btn-std">Completa la registrazione come donatore</a></div>';
    } else {
        $dataNascitaFormatted = date("d/m/Y", strtotime($datiDonatore['data_nascita']));

        $htmlDonatore = '
        <section class="dati_donatore_box">
            <h3 class="dashboard-title">Il tuo profilo Donatore</h3>
            <dl class="data-list">
                <dt>Nome e Cognome:</dt>
                <dd>' . htmlspecialchars($datiDonatore['nome']) . ' ' . htmlspecialchars($datiDonatore['cognome']) . '</dd>
                
                <dt>Data di Nascita:</dt>
                <dd>' . $dataNascitaFormatted . '</dd>
                
                <dt>Luogo di Nascita:</dt>
                <dd>' . htmlspecialchars($datiDonatore['luogo_nascita']) . '</dd>
                
                <dt>Codice Fiscale:</dt>
                <dd class="text-uppercase">' . htmlspecialchars($datiDonatore['codice_fiscale']) . '</dd>
                
                <dt>Residenza:</dt>
                <dd>' . htmlspecialchars($datiDonatore['indirizzo']) . '</dd>

                <dt>Email:</dt>
                <dd>' . htmlspecialchars($datiDonatore['email']) . '</dd>
                
                <dt>Telefono:</dt>
                <dd>' . htmlspecialchars($datiDonatore['telefono']) . '</dd>

                <dt>Gruppo Sanguigno:</dt>
                <dd class="evidenziato">' . htmlspecialchars($datiDonatore['gruppo_sanguigno']) . '</dd>
                
                <dt>Sesso:</dt>
                <dd>' . htmlspecialchars($datiDonatore['sesso']) . '</dd>
                
                <dt>Peso:</dt>
                <dd>' . htmlspecialchars($datiDonatore['peso']) . ' Kg</dd>
            </dl>
            <div class="btn-wrapper"><a href="/php/pages/registrazione_donatore.php" class="btn-std">Modifica i tuoi dati</a></div>
        </section>';
        
        $_SESSION['dati_donatore'] = $datiDonatore;
    }
} catch (PDOException $e) {
    $htmlDonatore = "<p>Errore nel recupero dati.</p>";
}

$paginaHTML = str_replace('[sezioneDonatore]', $htmlDonatore, $paginaHTML);

// --- SEZIONE TABELLE PRENOTAZIONI PASSATE E FUTURE ---
$sezioneFuture = "";
$sezionePassate = "";

try {
    $stmt = $pdo->prepare("
        (SELECT p.id, p.data_prenotazione, p.ora_prenotazione, p.tipo_donazione, 
                s.nome as nome_sede, 'futura' as stato
        FROM lista_prenotazioni p 
        JOIN sedi s ON p.sede_id = s.id 
        WHERE p.user_id = ? AND p.data_prenotazione >= CURDATE()
        ORDER BY p.data_prenotazione ASC, p.ora_prenotazione ASC)
        
        UNION ALL
        
        (SELECT p.id, p.data_prenotazione, p.ora_prenotazione, p.tipo_donazione, 
                s.nome as nome_sede, 'passata' as stato
        FROM lista_prenotazioni p 
        JOIN sedi s ON p.sede_id = s.id 
        WHERE p.user_id = ? AND p.data_prenotazione < CURDATE()
        ORDER BY p.data_prenotazione DESC, p.ora_prenotazione DESC
        LIMIT 5)
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $prenotazioni = $stmt->fetchAll();

    $future = array_filter($prenotazioni, fn($p) => $p['stato'] === 'futura');
    $passate = array_filter($prenotazioni, fn($p) => $p['stato'] === 'passata');

    // Tabella future
    if (count($future) > 0) {
        $righeTabella = "";
        foreach ($future as $p) {
            $dataIt = date("d/m/Y", strtotime($p['data_prenotazione']));
            $oraIt = substr($p['ora_prenotazione'], 0, 5);
            $righeTabella .= '<tr>
                <td>' . $dataIt . '</td>
                <td>' . $oraIt . '</td>
                <td>' . htmlspecialchars($p['tipo_donazione']) . '</td>
                <td>' . htmlspecialchars($p['nome_sede']) . '</td>
                <td>
                    <button type="button" class="btn-table delete btn-annulla-prenotazione" data-id-prenotazione="' . $p['id'] . '" data-data="' . $dataIt . '" data-ora="' . $oraIt . '">Annulla</button>
                </td>
            </tr>';
        }
        $sezioneFuture = '
        <div class="table-container">
            <table class="data-table" aria-describedby="titolo-prenotazioni">
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Ora</th>
                        <th scope="col">Tipo Donazione</th>
                        <th scope="col">Sede</th>
                        <th scope="col">Azioni</th>
                    </tr>
                </thead>
                <tbody>' . $righeTabella . '</tbody>
            </table>
        </div>';
    } else {
        $sezioneFuture = '<p class="text-standard testo-centered-message">Nessuna prenotazione in programma.</p>';
    }

    // Tabella passate
    if (count($passate) > 0) {
        $righeTabella = "";
        foreach ($passate as $p) {
            $dataIt = date("d/m/Y", strtotime($p['data_prenotazione']));
            $oraIt = substr($p['ora_prenotazione'], 0, 5);
            $righeTabella .= '<tr>
                <td>' . $dataIt . '</td>
                <td>' . $oraIt . '</td>
                <td>' . htmlspecialchars($p['tipo_donazione']) . '</td>
                <td>' . htmlspecialchars($p['nome_sede']) . '</td>
                <td><span class="status-completed">Completata</span></td>
            </tr>';
        }
        $sezionePassate = '
        <div class="table-container">
            <table class="data-table" aria-describedby="titolo-storico">
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Ora</th>
                        <th scope="col">Tipo Donazione</th>
                        <th scope="col">Sede</th>
                        <th scope="col">Stato</th>
                    </tr>
                </thead>
                <tbody>' . $righeTabella . '</tbody>
            </table>
        </div>';
    } else {
        $sezionePassate = '<p class="text-standard testo-centered-message">Nessuna donazione precedente.</p>';
    }
} catch (PDOException $e) {
    $sezioneFuture = '<p class="errore testo-centered-message">Errore caricamento dati.</p>';
}

// --- COSTRUZIONE HTML FINALE CON ID PER ARIA-LABEL ---
$nuovoContenutoTabelle = '
    <section>
        <h3 id="titolo-prenotazioni" class="tertiary-title">Prenotazioni Future</h3>
        ' . $sezioneFuture . '

        <h3 id="titolo-storico" class="tertiary-title titolo-margin-top">Storico Donazioni (Ultime 5)</h3>
        ' . $sezionePassate . '
    </section>
';

$paginaHTML = str_replace('[tabellePrenotazioni]', $nuovoContenutoTabelle, $paginaHTML);

$nomeUtente = '<h1>' . htmlspecialchars(ucfirst($_SESSION['username'])) . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

$msgHTML = getMessaggioFlashHTML();
if (!empty($msgHTML)) {
    $paginaHTML = str_replace('<main id="content" class="main-standard">', '<main id="content" class="main-standard">' . $msgHTML, $paginaHTML);
}

$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>Profilo</span></p>';
echo costruisciPagina($paginaHTML, $breadcrumb, "profilo.php");
?>