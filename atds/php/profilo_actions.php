<?php
session_start();
require_once "db_connect.php"; 
function complete_session_logout() {
    session_unset();
    session_destroy(); 

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}
if (!isset($_SESSION['username']) && !isset($_POST['logout'])) {
    header("Location: ../html/login.html");
    exit;
}
$username = $_SESSION['username'] ?? null;

if (isset($_POST['elimina_profilo']) && $username) {
    
    $stmt = $conn->prepare("DELETE FROM utente WHERE username = ?"); 
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    complete_session_logout();
    header("Location: ../html/index.html"); 
    exit;
}

if (isset($_POST['logout'])) {
    complete_session_logout();
    header("Location: ../html/index.html"); 
    exit;
}
if (isset($_POST['modifica_profilo'])) {
    header("Location: ../html/modifica.html");
    exit;
}
$conn->close();
?>