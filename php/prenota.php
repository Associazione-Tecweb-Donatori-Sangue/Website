<?php
session_start();
require_once "db_connect.php"; 

if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html"); 
    exit;
}
$user = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $luogo = trim($_POST['luogo']);
    $data = trim($_POST['data']);
    $ora = trim($_POST['ora']);
    $donazione = trim($_POST['donazione']);

    if (empty($luogo) || empty($data) || empty($ora) || empty($donazione)) {
        echo "Tutti i campi sono obbligatori.";
        exit;
    }
    $sql = "INSERT INTO prenotazioni (username, luogo, data_donazione, ora_donazione, tipo_donazione)
             VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $user, $luogo, $data, $ora, $donazione);

    if ($stmt->execute()) {
        header("Location: conferma.php");
        exit;
    } else {
        echo "Si è verificato un errore durante la prenotazione... riprova più tardi.";
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../html/index.html");
    exit;
}
?>