<?php
require_once "../utility.php";
require_once "../db.php";

// --- 1. GESTIONE MESSAGGI FLASH (Nuovo Blocco) ---
$msgHTML = "";
if (isset($_SESSION['messaggio_flash'])) {
    // Determino il colore in base al messaggio (verde per successo, rosso per errori)
    $classe = 'msg-flash msg-success'; // Verde default
    
    if (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) {
        $classe = 'msg-flash msg-error'; // Rosso errore
    }

    $msgHTML = '<div class="' . $classe . '">
                    ' . htmlspecialchars($_SESSION['messaggio_flash']) . '
                </div>';
    
    unset($_SESSION['messaggio_flash']);
}

$paginaHTML = caricaTemplate('dona_ora.html');

// --- 2. INIEZIONE MESSAGGIO NELLA PAGINA ---
// Lo inserisco subito dopo l'apertura del tag <main>
$paginaHTML = str_replace('<main id="content" class="main-standard">', '<main id="content" class="main-standard">' . $msgHTML, $paginaHTML);

// 1. Controllo se l'utente è loggato, se si mostro la pagina, altrimenti mostro tasti di login/registrazione
if (!isset($_SESSION['user_id'])) {
    // UTENTE NON LOGGATO
    $messaggioAvviso = '
    <div class="text-standard testo-centered">
        <h3 class="section-title">Accesso Richiesto</h3>
        <p>Per prenotare una donazione è necessario accedere alla propria area riservata.</p>
        <p>Il sangue è una cosa seria, e anche la sicurezza dei tuoi dati!</p>
        
        <div class="action-container">
            
            <form action="login.php" method="get" class="form-inline">
                <div class="btn-wrapper">
                    <input type="hidden" name="redirect" value="dona_ora.php">
                    <button type="submit" class="btn">Accedi</button>
                </div>
            </form>

            <p class="text-separator">oppure</p>

            <form action="registrazione.php" method="get" class="form-inline">
                <div class="btn-wrapper">
                    <button type="submit" class="btn">Registrati</button>
                </div>
            </form>
        </div>
    </div>
    ';

    // 3. SOSTITUZIONE DEL FORM CON IL MESSAGGIO
    // Cerco il form tramite il suo ID "prenotaForm" e lo rimpiazzo con il messaggio
    $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioAvviso, $paginaHTML);

} else {
    // UTENTE LOGGATO, controllo se è ADMIN
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        $messaggioNonDonatoreoAdmin = '
        <div class="text-standard testo-centered">
            <h3 class="section-title">Profilo Admin</h3>
            <p>Ciao <strong>' . htmlspecialchars($_SESSION['username']) . '</strong>!</p>
            <p>Per poter prenotare una donazione, devi utilizzare un account utente normale.</p>
            <p>Gli account amministratori non possono effettuare donazioni.</p>
            
            <div class="action-container-single">
                <form action="profilo.php" method="get" class="form-inline wide">
                    <div class="btn-wrapper">
                        <button type="submit" class="btn">Torna al Profilo</button>
                    </div>
                </form>
            </div>
        </div>
        ';

        // Sostituisco il form con il messaggio "Non puoi prenotare come Admin"
        $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioNonDonatoreoAdmin, $paginaHTML);
    } else { // UTENTE NORMALE
        try {
            // Controllo se esiste nella tabella donatori
            $stmtCheck = $pdo->prepare("SELECT user_id FROM donatori WHERE user_id = ?");
            $stmtCheck->execute([$_SESSION['user_id']]);
            $isDonatore = $stmtCheck->fetch();

            if (!$isDonatore) {
                
                // --- LIVELLO 2: UTENTE LOGGATO MA NON DONATORE ---
                $messaggioNonDonatoreoAdmin = '
                <div class="text-standard testo-centered">
                    <h3 class="section-title">Profilo Donatore Incompleto</h3>
                    <p>Ciao <strong>' . htmlspecialchars($_SESSION['username']) . '</strong>!</p>
                    <p>Per poter prenotare una donazione, abbiamo bisogno di raccogliere alcuni dati sanitari obbligatori.</p>
                    <p>La procedura richiede pochi minuti.</p>
                    
                    <div class="action-container-single">
                        <form action="registrazione_donatore.php" method="get" class="form-inline wide">
                            <div class="btn-wrapper">
                                <button type="submit" class="button-full-width">Diventa Donatore</button>
                            </div>
                        </form>
                    </div>
                </div>
                ';
                
                // Sostituisco il form con il messaggio "Diventa Donatore"
                $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioNonDonatoreoAdmin, $paginaHTML);

            } else {
                
                // --- LIVELLO 3: UTENTE LOGGATO E DONATORE (Mostro il form) ---
                
                // Carico le sedi dal DB per popolare la Select
                $stmt = $pdo->query("SELECT id, nome FROM sedi ORDER BY nome ASC");
                $sedi = $stmt->fetchAll();

                // Controllo se c'è una sede preselezionata via GET o da form preservato
                $sedePreselezionata = '';
                $oraPreselezionata = '';
                $tipoPreselezionato = '';
                
                if (isset($_SESSION['form_preservato'])) {
                    $sedePreselezionata = $_SESSION['form_preservato']['sede_id'];
                    $oraPreselezionata = $_SESSION['form_preservato']['ora'];
                    $tipoPreselezionato = $_SESSION['form_preservato']['tipo'];
                    unset($_SESSION['form_preservato']);
                } elseif (isset($_GET['sede_id'])) {
                    $sedePreselezionata = $_GET['sede_id'];
                }

                $optionsSedi = "";
                foreach ($sedi as $sede) {
                    $selected = ($sede['id'] == $sedePreselezionata) ? 'selected' : '';
                    $optionsSedi .= '<option value="' . $sede['id'] . '" ' . $selected . '>' . htmlspecialchars($sede['nome']) . '</option>';
                }

                // Sostituisco il segnaposto nel form
                $paginaHTML = str_replace('[listaNomiSedi]', $optionsSedi, $paginaHTML);
                
                // Pre-seleziono l'ora se disponibile
                if (!empty($oraPreselezionata)) {
                    $paginaHTML = str_replace(
                        'value="' . $oraPreselezionata . '">',
                        'value="' . $oraPreselezionata . '" selected>',
                        $paginaHTML
                    );
                }
                
                // Pre-seleziono il tipo di donazione (radio button) se disponibile
                if (!empty($tipoPreselezionato)) {
                    $paginaHTML = str_replace(
                        'name="donazione" value="' . $tipoPreselezionato . '"',
                        'name="donazione" value="' . $tipoPreselezionato . '" checked',
                        $paginaHTML
                    );
                }
            }

        } catch (PDOException $e) {
            // Fallback errore DB
            $errore = '<p class="errore">Si è verificato un errore nel caricamento dei dati. Riprova più tardi.</p>';
            $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $errore, $paginaHTML);
        }
    }
}


// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Dona ora</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'dona_ora.php');
?>