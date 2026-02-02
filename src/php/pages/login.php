<?php
require_once "../utility.php";
require_once "../db.php";
if (isset($_GET['redirect'])) {
    $_SESSION['redirect_post_login'] = $_GET['redirect'];
} 

elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['redirect_post_login']);
}

$paginaHTML = caricaTemplate('login.html');

// Variabile per eventuali messaggi di errore
$messaggioErrore = getMessaggioFlashHTML();

// Controllo se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = pulisciInput($_POST['username']);
    $password = $_POST['password'];

    try {
        // Cerchiamo l'utente nel DB
        $stmt = $pdo->prepare("SELECT * FROM utenti WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // Verifica Password Hash
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['ruolo']    = $user['ruolo'];
            
            if ($user['ruolo'] === 'admin') {
                $_SESSION['is_admin'] = true;
                header("Location: profilo_admin.php");
                exit();
            } 

            if (isset($_SESSION['redirect_post_login'])) {
                $destinazione = $_SESSION['redirect_post_login'];
                unset($_SESSION['redirect_post_login']);
                header("Location: " . $destinazione);
                exit();
            }

            header("Location: profilo.php");
            exit();
        } else {
            $messaggioErrore = "<div class='msg-error' role='alert'>Errore: Credenziali non corrette.</div>";
        }
    } catch (PDOException $e) {
        logError("Errore login: " . $e->getMessage());
        $messaggioErrore = "<div class='msg-error' role='alert'>Errore durante l'autenticazione. Riprova più tardi.</div>";
    }
}
$paginaHTML = str_replace('[messaggioErrore]', $messaggioErrore, $paginaHTML);
$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span lang="en">Login</span></p>';

// 4. Costruisco la pagina
// Passo "login.php" così se non sono loggato l'icona diventa non cliccabile
echo costruisciPagina($paginaHTML, $breadcrumb, "login.php");
?>
