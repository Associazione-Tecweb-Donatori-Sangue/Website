<?php
require_once "../utility.php";
require_once "../db.php";

// Se arrivo con un parametro GET 'redirect' (es. da dona_ora), me lo segno
if (isset($_GET['redirect'])) {
    $_SESSION['redirect_post_login'] = $_GET['redirect'];
} 
// Se arrivo "pulito" (es. dal menu) e NON sto facendo POST, dimentico vecchi redirect
elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['redirect_post_login']);
}

$paginaHTML = caricaTemplate('login.html');

// Variabile per eventuali messaggi di errore
$messaggioErrore = "";

if (isset($_SESSION['messaggio_flash'])) {
    // Determino il colore in base al contenuto del messaggio
    if (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) {
        $classe = 'msg-error'; // Rosso errore
    } else {
        $classe = 'msg-success'; // Verde successo
    }
    
    $messaggioErrore = "<div class='" . $classe . "'>" . htmlspecialchars($_SESSION['messaggio_flash']) . "</div>";
    
    // Importante: cancelliamo il messaggio subito dopo averlo salvato nella variabile
    // così se ricarichi la pagina sparisce
    unset($_SESSION['messaggio_flash']);
}

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
            // Login OK
            $_SESSION['user_id'] = $user['id'];  // Fondamentale per le chiavi esterne
            $_SESSION['username'] = $user['username'];
            $_SESSION['ruolo']    = $user['ruolo'];
            
            // A. Se è admin, vince sempre il profilo admin
            if ($user['ruolo'] === 'admin') {
                $_SESSION['is_admin'] = true;
                header("Location: profilo_admin.php");
                exit();
            } 
            
            // B. Se c'è un redirect in sospeso (es. voleva donare), lo accontento
            if (isset($_SESSION['redirect_post_login'])) {
                $destinazione = $_SESSION['redirect_post_login'];
                unset($_SESSION['redirect_post_login']); // Pulisco subito dopo l'uso
                header("Location: " . $destinazione);
                exit();
            }

            // C. Default standard: Profilo utente
            header("Location: profilo.php");
            exit();
        } else {
            $messaggioErrore = "<div class='msg-error'>Errore: Credenziali non corrette.</div>";
        }
    } catch (PDOException $e) {
        $messaggioErrore = "<div class='msg-error'>Errore DB: " . $e->getMessage() . "</div>";
    }
}

// 2. Inserisco il messaggio di errore nel corpo della pagina
$paginaHTML = str_replace('[messaggioErrore]', $messaggioErrore, $paginaHTML);

// 3. Definisco il Breadcrumb
$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span lang="en">Login</span></p>';

// 4. Costruisco la pagina
// Passo "login.php" così se non sono loggato l'icona diventa non cliccabile
echo costruisciPagina($paginaHTML, $breadcrumb, "login.php");
?>