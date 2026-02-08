<?php
require_once "../utility.php";
require_once "../db.php";

$paginaHTML = caricaTemplate('dona_ora.html');

// Gestione messaggi flash unificata
$msgHTML = getMessaggioFlashHTML();
if (!empty($msgHTML)) {
    $paginaHTML = str_replace('<main id="content" class="main-standard">', '<main id="content" class="main-standard">' . $msgHTML, $paginaHTML);
}

// 1. Controllo Logica Accesso
if (!isset($_SESSION['user_id'])) {
    $messaggioAvviso = '
    <div class="text-standard">
        <h3 class="section-title">Accesso Richiesto</h3>
        <p>Per prenotare una donazione è necessario accedere alla propria area riservata.</p>
        <div class="action-container">
            <form action="login.php" method="get" class="form-inline">
                <div class="btn-wrapper">
                    <input type="hidden" name="redirect" value="dona_ora.php">
                    <button type="submit" class="btn-std">Accedi</button>
                </div>
            </form>
            <p class="text-separator">oppure</p>
            <form action="registrazione.php" method="get" class="form-inline">
                <div class="btn-wrapper"><button type="submit" class="btn-std">Registrati</button></div>
            </form>
        </div>
    </div>';
    $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioAvviso, $paginaHTML);

} else {
    // Utente loggato -> controllo se è admin
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        $messaggioAdmin = '
        <div class="text-standard">
            <h3 class="section-title">Profilo Admin</h3>
            <p>Ciao ' . htmlspecialchars($_SESSION['username']) . '! Gli account amministratori non possono effettuare donazioni.</p>
            <div class="action-container-single">
                <form action="profilo.php" method="get" class="form-inline">
                    <div class="btn-wrapper"><button type="submit" class="btn-std">Torna al Profilo</button></div>
                </form>
            </div>
        </div>';
        $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioAdmin, $paginaHTML);
    } else { 
        // Utente normale 
        try {
            // Recupero dati donatore
            $stmtDonatore = $pdo->prepare("SELECT sesso FROM donatori WHERE user_id = ?");
            $stmtDonatore->execute([$_SESSION['user_id']]);
            $datiDonatore = $stmtDonatore->fetch();

            if (!$datiDonatore) {
                // Utente loggato ma non donatore -> Messaggio di completamento profilo donatore
                $messaggioIncompleto = '
                <div class="text-standard">
                    <h3 class="section-title">Profilo donatore incompleto</h3>
                    <p>Ciao ' . htmlspecialchars($_SESSION['username']) . '! Devi completare la registrazione dei dati sanitari prima di poter prenotare una donazione.</p>
                    <div class="action-container-single">
                        <form action="registrazione_donatore.php" method="get" class="form-inline">
                            <div class="btn-wrapper"><button type="submit" class="btn-std">Diventa Donatore</button></div>
                        </form>
                    </div>
                </div>';
                $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioIncompleto, $paginaHTML);
            } else {
                // Utente donatore -> Configurazione form prenotazione
                
                // Recupero ultima data prenotazione per JavaScript
                $stmtUltima = $pdo->prepare("SELECT MAX(data_prenotazione) FROM lista_prenotazioni WHERE user_id = ?");
                $stmtUltima->execute([$_SESSION['user_id']]);
                $ultimaData = $stmtUltima->fetchColumn() ?: '';

                // Iniettiamo i metadati nel tag form per farli leggere a script.js
                $formConDati = '<form id="prenotaForm" data-ultima="' . $ultimaData . '" data-sesso="' . $datiDonatore['sesso'] . '" data-is-admin="false"';
                $paginaHTML = str_replace('<form id="prenotaForm"', $formConDati, $paginaHTML);

                // Popolamento Sedi
                $stmtSedi = $pdo->query("SELECT id, nome FROM sedi ORDER BY nome ASC");
                $sedi = $stmtSedi->fetchAll();

                $sedePre = $_GET['sede_id'] ?? ($_SESSION['form_preservato']['sede_id'] ?? '');
                $optionsSedi = "";
                foreach ($sedi as $s) {
                    $sel = ($s['id'] == $sedePre) ? 'selected' : '';
                    $optionsSedi .= '<option value="' . $s['id'] . '" ' . $sel . '>' . htmlspecialchars($s['nome']) . '</option>';
                }
                $paginaHTML = str_replace('[listaNomiSedi]', $optionsSedi, $paginaHTML);

                // Ripristino dati form se presenti
                if (isset($_SESSION['form_preservato'])) {
                    $oraP = $_SESSION['form_preservato']['ora'];
                    $tipoP = $_SESSION['form_preservato']['tipo'];
                    $paginaHTML = str_replace('value="' . $oraP . '">', 'value="' . $oraP . '" selected>', $paginaHTML);
                    $paginaHTML = str_replace('name="donazione" value="' . $tipoP . '"', 'name="donazione" value="' . $tipoP . '" checked', $paginaHTML);
                    unset($_SESSION['form_preservato']);
                }
            }
        } catch (PDOException $e) {
            $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', '<p class="text-standard">Errore caricamento dati.</p>', $paginaHTML);
        }
    }
}

$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Dona ora</span></p>';
echo costruisciPagina($paginaHTML, $breadcrumb, 'dona_ora.php');
?>
