<?php
require_once "../utility.php";
require_once "../db.php";

// Template HTML
$paginaHTML = caricaTemplate('registrazione.html');

// Variabile per la gestione degli errori e messaggi
$messaggio = "";
$usernamePreservato = "";

// Gestione del Form di Registrazione (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = pulisciInput($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['password_confirm'];
    $usernamePreservato = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    if ($password !== $confirm) {
        $messaggio = "<div class='msg-error'>Errore: Le password non coincidono.</div>";
    } else {
        try {
            // Controllo se l'utente esiste già
            $stmt = $pdo->prepare("SELECT id FROM utenti WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $messaggio = "<div class='msg-error'>Errore: Username già esistente!</div>";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, 'user')");
                
                if ($stmt->execute([$username, $hash])) {
                    $newUserId = $pdo->lastInsertId();
                    $_SESSION['user_id'] = $newUserId;
                    $_SESSION['username'] = $username;
                    $_SESSION['ruolo'] = 'user';

                    header("Location: profilo.php");
                    exit;
                } else {
                    $messaggio = "<div class='msg-error'>Errore durante la registrazione. Riprova.</div>";
                }
            }
        } catch (PDOException $e) {
            logError("Errore registrazione: " . $e->getMessage());
            $messaggio = "<div class='msg-error'>Errore durante la registrazione. Riprova più tardi.</div>";
        }
    }
}

// Sostituisco il placeholder del messaggio nel template
$paginaHTML = str_replace('[messaggioErrore]', $messaggio, $paginaHTML);

// Se c'è un errore, preservo l'username nel campo del form
if (!empty($usernamePreservato)) {
    $paginaHTML = str_replace(
        'name="username" id="username"',
        'name="username" id="username" value="' . $usernamePreservato . '"',
        $paginaHTML
    );
}

// Definisco il Breadcrumb
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Registrazione</span></p>';

// Costruisco la pagina
echo costruisciPagina($paginaHTML, $breadcrumb, "registrazione.php");
?>
