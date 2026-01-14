<?php
require_once "../utility.php";
require_once "../db.php";
session_start();

$paginaHTML = file_get_contents('../../html/dona_ora.html');

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
    // UTENTE LOGGATO

    try {
        // Controllo se esiste nella tabella donatori
        $stmtCheck = $pdo->prepare("SELECT user_id FROM donatori WHERE user_id = ?");
        $stmtCheck->execute([$_SESSION['user_id']]);
        $isDonatore = $stmtCheck->fetch();

        if (!$isDonatore) {
            
            // --- LIVELLO 2: UTENTE LOGGATO MA NON DONATORE ---
            $messaggioNonDonatore = '
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
            $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioNonDonatore, $paginaHTML);

        } else {
            
            // --- LIVELLO 3: UTENTE LOGGATO E DONATORE (Mostro il form) ---
            
            // Carico le sedi dal DB per popolare la Select
            $stmt = $pdo->query("SELECT id, nome FROM sedi ORDER BY nome ASC");
            $sedi = $stmt->fetchAll();

            $optionsSedi = "";
            foreach ($sedi as $sede) {
                $optionsSedi .= '<option value="' . $sede['id'] . '">' . htmlspecialchars($sede['nome']) . '</option>';
            }

            // Sostituisco il segnaposto nel form
            $paginaHTML = str_replace('[OPZIONI_SEDI]', $optionsSedi, $paginaHTML);
        }

    } catch (PDOException $e) {
        // Fallback errore DB
        $errore = '<p class="errore">Si è verificato un errore nel caricamento dei dati. Riprova più tardi.</p>';
        $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $errore, $paginaHTML);
    }
}


// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Dona ora</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'dona_ora.php');
?>