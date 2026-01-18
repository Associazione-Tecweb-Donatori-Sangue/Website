<?php
require_once "../utility.php";
require_once "../db.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// se l'utente è ADMIN, viene reindirizzato al profilo admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: profilo_admin.php");
    exit();
}

$paginaHTML = file_get_contents('../../html/profilo.html');

// --- LOGICA GESTIONE FOTO PROFILO ---
$fotoPath = "../../images/profilo.jpg"; 
$isDefaultClass = "is-default"; // Assumiamo sia default all'inizio

try {
    $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Se il campo non è vuoto, verifichiamo il file
    if ($user && !empty($user['foto_profilo'])) {
        $nomeFile = $user['foto_profilo'];
        $percorsoFisico = "../../images/profili/" . $nomeFile;
        
        if (file_exists($percorsoFisico)) {
            $fotoPath = $percorsoFisico;
            $isDefaultClass = ""; // C'è una foto valida: togliamo is-default per mostrare il tasto
        }
    }
} catch (PDOException $e) {
    // Errore DB: resta tutto default
}

// Sostituzione dei placeholder
$paginaHTML = str_replace('[FOTO_PROFILO]', htmlspecialchars($fotoPath), $paginaHTML);
$paginaHTML = str_replace('[CLASS_DEFAULT]', $isDefaultClass, $paginaHTML);

// --- SEZIONE DATI DONATORE ---
$htmlDonatore = "";
try {
    $stmt = $pdo->prepare("SELECT * FROM donatori WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $datiDonatore = $stmt->fetch();

    if (!$datiDonatore) {
        $htmlDonatore = '<div class="button_std"><a href="/php/pages/registrazione_donatore.php" class="button">Completa la registrazione come donatore</a></div>';
    } else {
        $dataNascitaFormatted = date("d/m/Y", strtotime($datiDonatore['data_nascita']));

        $htmlDonatore = '
        <section class="dati_donatore_box">
            <h3 class="titolo-dashboard">Il tuo profilo Donatore</h3>
            <dl class="lista_dati">
                <dt>Nome e Cognome:</dt>
                <dd>' . htmlspecialchars($datiDonatore['nome']) . ' ' . htmlspecialchars($datiDonatore['cognome']) . '</dd>
                
                <dt>Data di Nascita:</dt>
                <dd>' . $dataNascitaFormatted . '</dd>
                
                <dt>Luogo di Nascita:</dt>
                <dd>' . htmlspecialchars($datiDonatore['luogo_nascita']) . '</dd>
                
                <dt>Codice Fiscale:</dt>
                <dd style="text-transform: uppercase;">' . htmlspecialchars($datiDonatore['codice_fiscale']) . '</dd>
                
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
            <div class="button_std" style="margin-top: 20px;"><a href="/php/pages/registrazione_donatore.php" class="button">Modifica i tuoi dati</a></div>
        </section>';
        
        $_SESSION['dati_donatore'] = $datiDonatore;
    }
} catch (PDOException $e) {
    $htmlDonatore = "<p>Errore nel recupero dati.</p>";
}

$paginaHTML = str_replace('[sezioneDonatore]', $htmlDonatore, $paginaHTML);

// --- SEZIONE TABELLE PRENOTAZIONI PASSATE E FUTURE ---
// 1. QUERY FUTURE
$htmlFuture = "";
try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.data_prenotazione, p.ora_prenotazione, p.tipo_donazione , s.nome as nome_sede 
        FROM lista_prenotazioni p 
        JOIN sedi s ON p.sede_id = s.id 
        WHERE p.user_id = ? AND p.data_prenotazione >= CURDATE()
        ORDER BY p.data_prenotazione ASC, p.ora_prenotazione ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $future = $stmt->fetchAll();

    if (count($future) > 0) {
        foreach ($future as $p) {
            $dataIt = date("d/m/Y", strtotime($p['data_prenotazione']));
            $oraIt = substr($p['ora_prenotazione'], 0, 5);
            $htmlFuture .= '<tr>
                <td>' . $dataIt . '</td>
                <td>' . $oraIt . '</td>
                <td>' . htmlspecialchars($p['tipo_donazione']) . '</td>
                <td>' . htmlspecialchars($p['nome_sede']) . '</td>
                <td><button type="button" class="link_azione">Annulla</button></td>
            </tr>';
        }
    } else {
        $htmlFuture = '<tr><td colspan="4" style="text-align:center;">Nessuna prenotazione in programma.</td></tr>';
    }
} catch (PDOException $e) {
    $htmlFuture = '<tr><td colspan="4" class="errore">Errore caricamento.</td></tr>';
}

// 2. QUERY PASSATE (Ultime 5)
$htmlPassate = "";
try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.data_prenotazione, p.ora_prenotazione, p.tipo_donazione , s.nome as nome_sede 
        FROM lista_prenotazioni p 
        JOIN sedi s ON p.sede_id = s.id 
        WHERE p.user_id = ? AND p.data_prenotazione < CURDATE()
        ORDER BY p.data_prenotazione DESC, p.ora_prenotazione DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $passate = $stmt->fetchAll();

    if (count($passate) > 0) {
        foreach ($passate as $p) {
            $dataIt = date("d/m/Y", strtotime($p['data_prenotazione']));
            $oraIt = substr($p['ora_prenotazione'], 0, 5);
            $htmlPassate .= '<tr>
                <td>' . $dataIt . '</td>
                <td>' . $oraIt . '</td>
                <td>' . htmlspecialchars($p['tipo_donazione']) . '</td>
                <td>' . htmlspecialchars($p['nome_sede']) . '</td>
                <td><span style="color: grey;">Completata</span></td>
            </tr>';
        }
    } else {
        $htmlPassate = '<tr><td colspan="4" style="text-align:center;">Nessuna donazione precedente.</td></tr>';
    }
} catch (PDOException $e) {
    $htmlPassate = '<tr><td colspan="4" class="errore">Errore caricamento.</td></tr>';
}


// --- COSTRUZIONE HTML E SOSTITUZIONE PLACEHOLDER ---
$nuovoContenutoTabelle = '
    <section>
        <h3 class="titolo_terziario">Prenotazioni Future</h3>
        <div class="contenitore_tabella">
            <table class="tabella_dati">
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Ora</th>
                        <th scope="col">Tipo Donazione</th>
                        <th scope="col">Sede</th>
                        <th scope="col">Azioni</th>
                    </tr>
                </thead>
                <tbody>' . $htmlFuture . '</tbody>
            </table>
        </div>

        <h3 class="titolo_terziario" style="margin-top: 40px;">Storico Donazioni (Ultime 5)</h3>
        <div class="contenitore_tabella">
            <table class="tabella_dati" style="opacity: 0.8;">
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Ora</th>
                        <th scope="col">Tipo Donazione</th>
                        <th scope="col">Sede</th>
                        <th scope="col">Stato</th>
                    </tr>
                </thead>
                <tbody>' . $htmlPassate . '</tbody>
            </table>
        </div>
    </section>
';

// ORA LA SOSTITUZIONE È SEMPLICE E PULITA:
$paginaHTML = str_replace('[tabellePrenotazioni]', $nuovoContenutoTabelle, $paginaHTML);


$nomeUtente = '<h1>' . htmlspecialchars(ucfirst($_SESSION['username'])) . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

// --- GESTIONE MESSAGGIO FLASH (Spostato all'inizio del Main) ---
if (isset($_SESSION['messaggio_flash'])) {
    $colore = (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) ? '#f8d7da' : '#d4edda';
    $testoColore = (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) ? '#721c24' : '#155724';
    $bordo = (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) ? '#f5c6cb' : '#c3e6cb';

    $msgHTML = '<div style="background-color: '.$colore.'; color: '.$testoColore.'; border: 1px solid '.$bordo.'; padding: 15px; margin: 20px auto; width: 90%; max-width: 800px; border-radius: 5px; text-align: center;">
                    ' . htmlspecialchars($_SESSION['messaggio_flash']) . '
                </div>';
    
    // Cerco il tag <main ...> e ci incollo subito dopo il messaggio
    // Nota: Assicurati che nel tuo file html/profilo.html il tag sia scritto così:
    $tagMain = '<main id="content" class="main_std">';
    
    // Se non dovesse trovarlo, prova a cercare solo "<main>" o verifica il tuo HTML
    if (strpos($paginaHTML, $tagMain) !== false) {
        $paginaHTML = str_replace($tagMain, $tagMain . $msgHTML, $paginaHTML);
    } else {
        // Fallback: se il tag è scritto diversamente, lo metto all'inizio del contenuto grezzo
        // (utile se magari hai classi diverse nel main)
        $paginaHTML = preg_replace('/<main[^>]*>/', '$0' . $msgHTML, $paginaHTML, 1);
    }
    
    unset($_SESSION['messaggio_flash']);
}

$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>Profilo</span></p>';
echo costruisciPagina($paginaHTML, $breadcrumb, "profilo.php");
?>