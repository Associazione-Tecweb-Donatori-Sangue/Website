<?php
ob_start();
require_once "db.php";
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autenticato']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_profilo'])) {
    $file = $_FILES['foto_profilo'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Errore upload: ' . $file['error']]);
        exit();
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Formato non valido']);
        exit();
    }
    
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File troppo grande']);
        exit();
    }
    
    $uploadDir = '../images/profili/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('profile_' . $_SESSION['user_id'] . '_') . '.' . $extension;
    $uploadPath = $uploadDir . $fileName;
    
    try {
        $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $oldPhoto = $stmt->fetchColumn();
        
        if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
            unlink($uploadDir . $oldPhoto);
        }
    } catch (PDOException $e) {
        // Continua
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        try {
            $stmt = $pdo->prepare("UPDATE utenti SET foto_profilo = ? WHERE id = ?");
            $stmt->execute([$fileName, $_SESSION['user_id']]);
            
            echo json_encode(['success' => true, 'filename' => $fileName]);
        } catch (PDOException $e) {
            unlink($uploadPath);
            echo json_encode(['success' => false, 'message' => 'Errore database']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore spostamento file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nessun file']);
}