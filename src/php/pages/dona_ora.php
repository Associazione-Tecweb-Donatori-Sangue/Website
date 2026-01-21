<?php
require_once "../utility.php";
require_once "../db.php";

// --- 1. GESTIONE MESSAGGI FLASH (Nuovo Blocco) ---
$msgHTML = "";
if (isset($_SESSION['messaggio_flash'])) {
    // Determino il colore in base al messaggio (verde per successo, rosso/giallo per errori)
    // Se il messaggio contiene "Errore" o "già", uso uno stile diverso, altrimenti verde successo.
    $style = "background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;"; // Verde default
    
    if (strpos($_SESSION['messaggio_flash'], 'Errore') !== false || strpos($_SESSION['messaggio_flash'], 'già') !== false) {
        $style = "background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;"; // Rosso errore
    }

    $msgHTML = '<div style="' . $style . ' padding: 15px; margin: 20px auto; width: 100%; max-width: 600px; border-radius: 5px; text-align: center;">
                    ' . htmlspecialchars($_SESSION['messaggio_flash']) . '
                </div>';
    
    unset($_SESSION['messaggio_flash']);
}

$paginaHTML = caricaTemplate('dona_ora.html');

// --- 2. INIEZIONE MESSAGGIO NELLA PAGINA ---
// Lo inserisco subito dopo l'apertura del tag <main>
$paginaHTML = str_replace('<main id="content" class="main_std">', '<main id="content" class="main_std">' . $msgHTML, $paginaHTML);

// 1. Controllo se l'utente è loggato, se si mostro la pagina, altrimenti mostro tasti di login/registrazione
if (!isset($_SESSION['user_id'])) {
    // UTENTE NON LOGGATO
    $messaggioAvviso = '
    <div class="testo_std" style="text-align: center;">
        <h3 style="margin-bottom: 1em;">Accesso Richiesto</h3>
        <p>Per prenotare una donazione è necessario accedere alla propria area riservata.</p>
        <p>Il sangue è una cosa seria, e anche la sicurezza dei tuoi dati!</p>
        
        <div style="margin-top: 2em; display: flex; flex-direction: column; align-items: center; gap: 10px;">
            
            <form action="login.php" method="get" style="box-shadow:none; background:transparent; margin:0; width:100%; max-width:300px;">
                <div class="button_std" style="margin:0;">
                    <input type="hidden" name="redirect" value="dona_ora.php">
                    <button type="submit" style="margin:0; width:100%;">Accedi</button>
                </div>
            </form>

            <p style="margin: 0.5em 0;">oppure</p>

            <form action="registrazione.php" method="get" style="box-shadow:none; background:transparent; margin:0; width:100%; max-width:300px;">
                <div class="button_std" style="margin:0;">
                    <button type="submit" style="margin:0; width:100%;">Registrati</button>
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
        <div class="testo_std" style="text-align: center;">
            <h3 style="margin-bottom: 1em;">Profilo Admin</h3>
            <p>Ciao <strong>' . htmlspecialchars($_SESSION['username']) . '</strong>!</p>
            <p>Per poter prenotare una donazione, devi utilizzare un account utente normale.</p>
            <p>Gli account amministratori non possono effettuare donazioni.</p>
            
            <div style="margin-top: 2em; display: flex; justify-content: center;">
                <form action="profilo.php" method="get" style="box-shadow:none; background:transparent; margin:0; width:100%; max-width:350px;">
                    <div class="button_std" style="margin:0;">
                        <button type="submit" style="margin:0; width:100%;">Torna al Profilo</button>
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
                <div class="testo_std" style="text-align: center;">
                    <h3 style="margin-bottom: 1em;">Profilo Donatore Incompleto</h3>
                    <p>Ciao <strong>' . htmlspecialchars($_SESSION['username']) . '</strong>!</p>
                    <p>Per poter prenotare una donazione, abbiamo bisogno di raccogliere alcuni dati sanitari obbligatori.</p>
                    <p>La procedura richiede pochi minuti.</p>
                    
                    <div style="margin-top: 2em; display: flex; justify-content: center;">
                        <form action="registrazione_donatore.php" method="get" style="box-shadow:none; background:transparent; margin:0; width:100%; max-width:350px;">
                            <div class="button_std" style="margin:0;">
                                <button type="submit" style="margin:0; width:100%;">Diventa Donatore</button>
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

                // Controllo se c'è una sede preselezionata via GET
                $sedePreselezionata = isset($_GET['sede_id']) ? $_GET['sede_id'] : '';

                $optionsSedi = "";
                foreach ($sedi as $sede) {
                    $selected = ($sede['id'] == $sedePreselezionata) ? 'selected' : '';
                    $optionsSedi .= '<option value="' . $sede['id'] . '" ' . $selected . '>' . htmlspecialchars($sede['nome']) . '</option>';
                }

                // Sostituisco il segnaposto nel form
                $paginaHTML = str_replace('[listaNomiSedi]', $optionsSedi, $paginaHTML);
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