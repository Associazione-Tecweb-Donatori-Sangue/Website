<?php
require_once "../utility.php";
require_once "../db.php";

// Template HTML
$paginaHTML = caricaTemplate('registrazione.html');

// Variabile per la gestione degli errori e messaggi
$messaggio = "";

// 3. Gestione del Form di Registrazione (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = pulisciInput($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['password_confirm'];

    if ($password !== $confirm) {
        $messaggio = "<p class='errore msg-error-text'>Le password non coincidono.</p>";
    } else {
        try {
            // 1. Controllo se l'utente esiste già
            $stmt = $pdo->prepare("SELECT id FROM utenti WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $messaggio = "<p class='errore msg-error-text'>Username già esistente!</p>";
            } else {
                // 2. Inserimento con HASH della password
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // Di default il ruolo è 'user'
                $stmt = $pdo->prepare("INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, 'user')");
                
                if ($stmt->execute([$username, $hash])) {
                    // Successo! Imposto la sessione e reindirizzo
                    $newUserId = $pdo->lastInsertId();

                    $_SESSION['user_id'] = $newUserId;
                    $_SESSION['username'] = $username;
                    $_SESSION['ruolo'] = 'user';

                    header("Location: profilo.php");
                    exit;
                } else {
                    $messaggio = "<p class='errore msg-error-simple'>Errore nel database.</p>";
                }
            }
        } catch (PDOException $e) {
            $messaggio = "<p class='errore msg-error-simple'>Errore tecnico: " . $e->getMessage() . "</p>";
        }
    }
}

// 4. Inserisco eventuali messaggi di errore prima del form
// Cerco il tag <form ...> e gli appendo prima il messaggio
$paginaHTML = str_replace('<form', $messaggio . '<form', $paginaHTML);

// 3. Definisco il Breadcrumb
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Registrazione</span></p>';

// 4. Costruisco la pagina
echo costruisciPagina($paginaHTML, $breadcrumb, "registrazione.php");
?>