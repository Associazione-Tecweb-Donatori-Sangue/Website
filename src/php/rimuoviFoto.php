<?php
require_once "db.php";
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$user_id = $_SESSION['user_id'];
$uploadDir = '../images/profili/';

try {
    // Recupera il nome del file attuale per cancellarlo
    $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
    $stmt->execute([$user_id]);
    $oldPhoto = $stmt->fetchColumn();

    if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
        unlink($uploadDir . $oldPhoto); // Elimina il file fisico
    }

    // Aggiorna il database mettendo NULL o una stringa vuota
    $stmt = $pdo->prepare("UPDATE utenti SET foto_profilo = NULL WHERE id = ?");
    $stmt->execute([$user_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}