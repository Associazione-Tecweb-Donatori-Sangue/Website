<?php
require_once "../utility.php";
session_start();

$paginaHTML = file_get_contents('../../html/login.html');

// Variabile per eventuali messaggi di errore
$messaggioErrore = "";

// Controllo se il form è stato inviato
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = pulisciInput($_POST['username']);
    $password = pulisciInput($_POST['password']);

    // --- TODO: INSERIRE CONNESSIONE AL DB ---
    // Per ora, simuliamo che l'utente "user" con password "user" possa entrare e che "admin" con password "admin" sia l'amministratore
    // TODO: Sostituire con DBAccess->autenticaUtente($username, $password)
    
    if ($username == "user" && $password == "user") {
        // Login riuscito
        $_SESSION['username'] = $username;
        header("Location: profilo.php");
        exit();
    } elseif ($username == "admin" && $password == "admin") {
        // Login riuscito come admin
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = true;
        header("Location: profilo_admin.php");
        exit();
    } else {
        // Login fallito
        $messaggioErrore = "<p class='errore' style='color: red; text-align: center;'>Username o password errati.</p>";
    }
}

// 2. Inserisco il messaggio di errore nel corpo della pagina
$paginaHTML = str_replace('[messaggioErrore]', $messaggioErrore, $paginaHTML);

// 3. Definisco il Breadcrumb
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span lang="en">Login</span></p>';

// 4. Costruisco la pagina
// Passo "login.php" così se non sono loggato l'icona diventa non cliccabile
echo costruisciPagina($paginaHTML, $breadcrumb, "login.php");
?>