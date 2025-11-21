<?php
session_start();
require_once "db_connect.php"; 

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$sql = "SELECT * FROM utente WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['username'] = $user['username'];
    header("Location: ../html/profilo.html"); 
    exit;
} else {
    header("Location: ../html/login.html?error=1"); 
    exit;
}
?>
