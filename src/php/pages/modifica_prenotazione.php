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
    $msgHTML = '<div class="' . $classe . '">' . htmlspecialchars($_SESSION['messaggio_flash']) . '</div>';
    unset($_SESSION['messaggio_flash']);
}

$paginaHTML = caricaTemplate('dona_ora.html');
$paginaHTML = str_replace('<main id="content" class="main-standard">', '<main id="content" class="main-standard">' . $msgHTML, $paginaHTML);

// --- RECUPERO DATI DONATORE E LOGICA POPUP ---
$htmlDonatore = "";
$sessoDonatore = "Maschio"; 
$dataConfrontoPopup = "";

try {
    // 1. Recupero dati anagrafici e sesso del donatore
    $stmt = $pdo->prepare("SELECT d.*, u.username 
                           FROM donatori d 
                           JOIN utenti u ON d.user_id = u.id 
                           WHERE d.user_id = ?");
    $stmt->execute([$prenotazioneCorrente['user_id']]);
    $datiDonatore = $stmt->fetch();

    if ($datiDonatore) {
        $sessoDonatore = $datiDonatore['sesso'];
        $htmlDonatore = '
        <div class="admin-layout-container">
            <aside class="donor-info-sidebar" role="complementary" aria-labelledby="titolo-dati-donatore">
                <div class="profile-card">
                    <h2 id="titolo-dati-donatore" class="profile-card-title">Dati del Donatore</h2>
                    <dl class="data-list-compact">
                        <div>
                            <dt>Username:</dt>
                            <dd>' . htmlspecialchars($datiDonatore['username']) . '</dd>
                        </div>
                        <div>
                            <dt>Nome:</dt>
                            <dd>' . htmlspecialchars($datiDonatore['nome']) . '</dd>
                        </div>
                        <div>
                            <dt>Cognome:</dt>
                            <dd>' . htmlspecialchars($datiDonatore['cognome']) . '</dd>
                        </div>
                        <div>
                            <dt>Email:</dt>
                            <dd>' . htmlspecialchars($datiDonatore['email']) . '</dd>
                        </div>
                        <div>
                            <dt>Telefono:</dt>
                            <dd>' . htmlspecialchars($datiDonatore['telefono']) . '</dd>
                        </div>
                        <div>
                            <dt>Gruppo Sanguigno:</dt>
                            <dd><span class="blood-type-badge" aria-label="Gruppo sanguigno ' . htmlspecialchars($datiDonatore['gruppo_sanguigno']) . '">' . htmlspecialchars($datiDonatore['gruppo_sanguigno']) . '</span></dd>
                        </div>
                    </dl>
                </div>
            </aside>
            <div class="form-container-admin">';
    }

    // 2. Cerchiamo la data più vicina (precedente o successiva) per il popup smart
    $stmtVicini = $pdo->prepare("
        SELECT data_prenotazione 
        FROM lista_prenotazioni 
        WHERE user_id = ? AND id != ? 
        ORDER BY ABS(DATEDIFF(data_prenotazione, ?)) ASC 
        LIMIT 1");
    $stmtVicini->execute([
        $prenotazioneCorrente['user_id'], 
        $idPrenotazione, 
        $prenotazioneCorrente['data_prenotazione']
    ]);
    $dataConfrontoPopup = $stmtVicini->fetchColumn() ?: '';

} catch (PDOException $e) { /* silent fail */ }

// --- CONFIGURAZIONE FORM ---
// Iniettiamo data-is-admin="true" e la data di confronto per il JavaScript
$formModificato = '<form id="prenotaForm" data-ultima="'.$dataConfrontoPopup.'" data-sesso="'.$sessoDonatore.'" data-is-admin="true"';
$paginaHTML = str_replace('<form id="prenotaForm"', $htmlDonatore . $formModificato, $paginaHTML);

// Carico le sedi
$stmtSedi = $pdo->query("SELECT id, nome FROM sedi ORDER BY nome ASC");
$optionsSedi = "";
foreach ($stmtSedi->fetchAll() as $sede) {
    $selected = ($sede['id'] == $prenotazioneCorrente['sede_id']) ? 'selected' : '';
    $optionsSedi .= '<option value="' . $sede['id'] . '" ' . $selected . '>' . htmlspecialchars($sede['nome']) . '</option>';
}
$paginaHTML = str_replace('[listaNomiSedi]', $optionsSedi, $paginaHTML);

// Precompilo data, ora e tipo
$paginaHTML = str_replace('name="data" id="data"', 'name="data" id="data" value="' . htmlspecialchars($prenotazioneCorrente['data_prenotazione']) . '"', $paginaHTML);
$paginaHTML = str_replace('value="' . $prenotazioneCorrente['ora_prenotazione'] . '"', 'value="' . $prenotazioneCorrente['ora_prenotazione'] . '" selected', $paginaHTML);
$tipoDonazione = str_replace(' ', '-', $prenotazioneCorrente['tipo_donazione']);
$paginaHTML = str_replace('value="' . $tipoDonazione . '"', 'value="' . $tipoDonazione . '" checked', $paginaHTML);

// Cambio bottone e aggiungo campi hidden per processare la modifica
$paginaHTML = str_replace(
    '<button type="submit" class="btn-submit">Prenota</button>', 
    '<input type="hidden" name="id_prenotazione" value="' . $prenotazioneCorrente['id'] . '">
     <input type="hidden" name="user_id" value="' . $prenotazioneCorrente['user_id'] . '">
     <button type="submit" class="btn-submit">Salva modifiche</button>
     </div></div>', 
    $paginaHTML
);

// Aggiornamento titoli e Breadcrumb
$paginaHTML = str_replace(['<h1>Dona ora</h1>', '<h2>Prenota la tua donazione di sangue</h2>'], ['<h1>Modifica Prenotazione</h1>', '<h2>Modifica i dati della prenotazione</h2>'], $paginaHTML);
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <a href="profilo_admin.php">Profilo Admin</a> / <span>Modifica Prenotazione</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, 'modifica_prenotazione.php');
?>