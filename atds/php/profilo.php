<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit;
}
$username = $_SESSION['username'];
if (isset($_POST['elimina_profilo'])) {
    $stmt = $conn->prepare("DELETE FROM utente WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    session_destroy();
    header("Location: ../html/login.html");
    exit;
}
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../html/login.html");
    exit;
}
if (isset($_POST['modifica_profilo'])) {
    header("Location: ../html/modifica_profilo.html");
    exit;
}
$conn->close();
?>
