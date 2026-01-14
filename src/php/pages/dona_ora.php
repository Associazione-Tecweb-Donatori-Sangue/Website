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
    // Recupero le sedi dal database
    try {
        // Prendo ID e Nome delle sedi
        $stmt = $pdo->query("SELECT id, nome FROM sedi ORDER BY nome ASC");
        $sedi = $stmt->fetchAll();

        $optionsSedi = "";
        foreach ($sedi as $sede) {
            // Value = ID (per il database), Testo = Nome (per l'utente)
            // Esempio generato: <option value="1">ATDS Piovego</option>
            $optionsSedi .= '<option value="' . $sede['id'] . '">' . htmlspecialchars($sede['nome']) . '</option>';
        }

        // Sostituisco il segnaposto nel form
        $paginaHTML = str_replace('[listaNomiSedi]', $optionsSedi, $paginaHTML);

    } catch (PDOException $e) {
        // Se qualcosa va storto, mostro un'opzione di errore
        $errorOption = '<option value="">Errore caricamento sedi</option>';
        $paginaHTML = str_replace('[listaNomiSedi]', $errorOption, $paginaHTML);
    }
}


// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Dona ora</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'dona_ora.php');
?>