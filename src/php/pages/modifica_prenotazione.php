<?php
require_once "../utility.php";
require_once "../db.php";

// Controllo sicurezza: solo admin
requireAdmin();

// Controllo che ci sia un ID prenotazione
if (!isset($_GET['id_prenotazione'])) {
    header("Location: profilo_admin.php");
    exit();
}

$idPrenotazione = intval($_GET['id_prenotazione']);
$prenotazioneCorrente = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM lista_prenotazioni WHERE id = ?");
    $stmt->execute([$idPrenotazione]);
    $prenotazioneCorrente = $stmt->fetch();
    
    if (!$prenotazioneCorrente) {
        $_SESSION['messaggio_flash'] = "Prenotazione non trovata.";
        header("Location: profilo_admin.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['messaggio_flash'] = "Errore nel caricamento della prenotazione.";
    header("Location: profilo_admin.php");
    exit();
}

// --- GESTIONE MESSAGGI FLASH ---
$msgHTML = "";
if (isset($_SESSION['messaggio_flash'])) {
    $classe = 'msg-flash msg-success';
    
    if (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) {
        $classe = 'msg-flash msg-error';
    }

    $msgHTML = '<div class="' . $classe . '">
                    ' . htmlspecialchars($_SESSION['messaggio_flash']) . '
                </div>';
    
    unset($_SESSION['messaggio_flash']);
}

$paginaHTML = caricaTemplate('dona_ora.html');

// Iniezione messaggio
$paginaHTML = str_replace('<main id="content" class="main-standard">', '<main id="content" class="main-standard">' . $msgHTML, $paginaHTML);

// --- RECUPERO DATI DONATORE ---
$htmlDonatore = "";
try {
    $stmt = $pdo->prepare("SELECT d.*, u.username 
                           FROM donatori d 
                           JOIN utenti u ON d.user_id = u.id 
                           WHERE d.user_id = ?");
    $stmt->execute([$prenotazioneCorrente['user_id']]);
    $datiDonatore = $stmt->fetch();

    if ($datiDonatore) {
        $htmlDonatore = '
        <section>
            <h3 class="dashboard-title">Dati del Donatore</h3>
            <dl class="data-list">
                <dt>Username:</dt>
                <dd>' . htmlspecialchars($datiDonatore['username']) . '</dd>
            
                <dt>Nome e Cognome:</dt>
                <dd>' . htmlspecialchars($datiDonatore['nome']) . ' ' . htmlspecialchars($datiDonatore['cognome']) . '</dd>

                <dt>Email:</dt>
                <dd>' . htmlspecialchars($datiDonatore['email']) . '</dd>
                
                <dt>Telefono:</dt>
                <dd>' . htmlspecialchars($datiDonatore['telefono']) . '</dd>

                <dt>Gruppo Sanguigno:</dt>
                <dd class="evidenziato">' . htmlspecialchars($datiDonatore['gruppo_sanguigno']) . '</dd>
            </dl>
        </section>';
    }
} catch (PDOException $e) {
    // Errore nel recupero dati donatore
}

// Carico le sedi
$stmt = $pdo->query("SELECT id, nome FROM sedi ORDER BY nome ASC");
$sedi = $stmt->fetchAll();

$sedePreselezionata = $prenotazioneCorrente['sede_id'];

$optionsSedi = "";
foreach ($sedi as $sede) {
    $selected = ($sede['id'] == $sedePreselezionata) ? 'selected' : '';
    $optionsSedi .= '<option value="' . $sede['id'] . '" ' . $selected . '>' . htmlspecialchars($sede['nome']) . '</option>';
}

$paginaHTML = str_replace('[listaNomiSedi]', $optionsSedi, $paginaHTML);

// Precompilo data
$paginaHTML = str_replace('name="data" id="data"', 'name="data" id="data" value="' . htmlspecialchars($prenotazioneCorrente['data_prenotazione']) . '"', $paginaHTML);

// Precompilo ora
$oraSelezionata = $prenotazioneCorrente['ora_prenotazione'];
$paginaHTML = str_replace('value="' . $oraSelezionata . '"', 'value="' . $oraSelezionata . '" selected', $paginaHTML);

// Precompilo tipo donazione
$tipoDonazione = str_replace(' ', '-', $prenotazioneCorrente['tipo_donazione']);
$paginaHTML = str_replace('value="' . $tipoDonazione . '"', 'value="' . $tipoDonazione . '" checked', $paginaHTML);

// Cambio il testo del bottone e aggiungo campi hidden
$paginaHTML = str_replace(
    '<button type="submit" class="btn-submit">Prenota</button>', 
    '<input type="hidden" name="id_prenotazione" value="' . $prenotazioneCorrente['id'] . '">
    <input type="hidden" name="user_id" value="' . $prenotazioneCorrente['user_id'] . '">
    <button type="submit" class="btn-submit">Salva modifiche</button>', 
    $paginaHTML
);

// Cambio titoli della pagina
$paginaHTML = str_replace('<h1>Dona ora</h1>', '<h1>Modifica Prenotazione</h1>', $paginaHTML);
$paginaHTML = str_replace('<h2>Prenota la tua donazione di sangue</h2>', '<h2>Modifica i dati della prenotazione</h2>', $paginaHTML);

// Inserisco i dati del donatore prima del form
$paginaHTML = str_replace('<form id="prenotaForm"', $htmlDonatore . '<form id="prenotaForm"', $paginaHTML);

$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <a href="profilo_admin.php">Profilo Admin</a> / <span>Modifica Prenotazione</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, 'modifica_prenotazione.php');
?>
