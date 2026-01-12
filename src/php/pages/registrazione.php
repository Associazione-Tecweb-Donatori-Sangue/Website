<?php
require_once "../utility.php";
session_start();

// 1. Carico il template HTML
$paginaHTML = file_get_contents('../../html/registrazione.html');

// 2. Variabili per la gestione degli errori e messaggi
$messaggio = "";

// 3. Gestione del Form di Registrazione (POST)
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password_confirm'])) {
    
    $username = pulisciInput($_POST['username']);
    $password = pulisciInput($_POST['password']);
    $confirm  = pulisciInput($_POST['password_confirm']);

    // Controllo base: le password coincidono?
    if ($password !== $confirm) {
        $messaggio = "<p class='errore' style='color:red; text-align:center; font-weight:bold;'>Le password non coincidono.</p>";
    } else {
        // --- TODO: INSERIMENTO NEL DB ---
        // DBAccess->registraUtente($username, $password)...
        
        // Simulazione successo: logghiamo l'utente direttamente
        $_SESSION['username'] = $username;
        header("Location: profilo.php");
        exit();
    }
}

// 4. Inserisco eventuali messaggi di errore prima del form
// Cerco il tag <form ...> e gli appendo prima il messaggio
$paginaHTML = str_replace('<form', $messaggio . '<form', $paginaHTML);

// 3. Definisco il Breadcrumb
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Registrazione</span></p>';

// 4. Costruisco la pagina
// Non passo "registrazione.php" come pagina attiva perché non è nel menu principale,
// quindi passo stringa vuota o nulla.
echo costruisciPagina($paginaHTML, $breadcrumb, "");
?>