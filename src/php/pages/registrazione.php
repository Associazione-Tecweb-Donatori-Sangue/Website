<?php
require_once "../utility.php";
require_once "../db.php";

session_start();

// Template HTML
$paginaHTML = file_get_contents('../../html/registrazione.html');

// Variabile per la gestione degli errori e messaggi
$messaggio = "";

// 3. Gestione del Form di Registrazione (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = pulisciInput($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['password_confirm'];

    if ($password !== $confirm) {
        $messaggio = "<p class='errore' style='color:red; text-align:center;'>Le password non coincidono.</p>";
    } else {
        try {
            // 1. Controllo se l'utente esiste già
            $stmt = $pdo->prepare("SELECT id FROM utenti WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $messaggio = "<p class='errore' style='color:red; text-align:center;'>Username già esistente!</p>";
            } else {
                // 2. Inserimento con HASH della password (Sicurezza Top!)
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // Di default il ruolo è 'user'
                $stmt = $pdo->prepare("INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, 'user')");
                
                if ($stmt->execute([$username, $hash])) {
                    // Successo!
                    $_SESSION['messaggio_flash'] = "Registrazione completata! Ora puoi accedere.";
                    header("Location: login.php");
                    exit;
                } else {
                    $messaggio = "<p class='errore' style='color:red;'>Errore nel database.</p>";
                }
            }
        } catch (PDOException $e) {
            $messaggio = "<p class='errore' style='color:red;'>Errore tecnico: " . $e->getMessage() . "</p>";
        }
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