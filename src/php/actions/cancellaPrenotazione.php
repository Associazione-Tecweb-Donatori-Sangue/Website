<?php
require_once '../utility.php';
require_once '../db.php';

// 1. Sicurezza: Utente deve essere loggato
requireLogin();

// 2. Controllo se ricevo i dati via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_prenotazione'])) {
    
    $idPrenotazione = $_POST['id_prenotazione'];
    $userId = $_SESSION['user_id'];
    
    // Verifico se è admin
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

    try {
        if ($isAdmin) {
            // A. LOGICA ADMIN: Cancella SENZA controllare di chi è la prenotazione
            $stmt = $pdo->prepare("DELETE FROM lista_prenotazioni WHERE id = ?");
            $stmt->execute([$idPrenotazione]);
        } else {
            // B. LOGICA UTENTE: Cancella SOLO se la prenotazione è sua (AND user_id = ?)
            $stmt = $pdo->prepare("DELETE FROM lista_prenotazioni WHERE id = ? AND user_id = ?");
            $stmt->execute([$idPrenotazione, $userId]);
        }

        if ($stmt->rowCount() > 0) {
            $_SESSION['messaggio_flash'] = "Prenotazione eliminata con successo.";
        } else {
            $_SESSION['messaggio_flash'] = "Errore: Impossibile trovare la prenotazione.";
        }

    } catch (PDOException $e) {
        $_SESSION['messaggio_flash'] = "Errore Database: " . $e->getMessage();
    }
}

// 4. Redirect Intelligente
// Se sono admin torno alla dashboard admin, altrimenti al profilo utente
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: pages/profilo_admin.php");
} else {
    header("Location: pages/profilo.php");
}
exit();
?>