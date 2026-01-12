<?php
require_once "../utility.php";
require_once "../db.php";

session_start();

$paginaHTML = file_get_contents('../../html/login.html');

// Variabile per eventuali messaggi di errore
$messaggioErrore = "";

if (isset($_SESSION['messaggio_flash'])) {
    
    $messaggioErrore = "<p style='color:green; text-align:center; font-weight:bold;'>" . $_SESSION['messaggio_flash'] . "</p>";
    
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
            
            // Redirect in base al ruolo
            if ($user['ruolo'] === 'admin') {
                $_SESSION['is_admin'] = true; // Per compatibilità con utility.php
                header("Location: profilo_admin.php");
            } else {
                header("Location: profilo.php");
            }
            exit();
        } else {
            $messaggioErrore = "<p class='errore' style='color: red; text-align: center;'>Username o password errati.</p>";
        }
    } catch (PDOException $e) {
        $messaggioErrore = "<p class='errore' style='color: red;'>Errore DB: " . $e->getMessage() . "</p>";
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