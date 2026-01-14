<?php
session_start();
require_once "db.php";

// 1. Controllo sicurezza: se non è loggato, via al login
if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit();
}

// 2. Elimino il profilo donatore se esiste
try {
    $stmt = $pdo->prepare("DELETE FROM donatori WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Gestione errore (opzionale)
    echo "Errore nell'eliminazione del profilo donatore: " . $e->getMessage();
    exit();
}

// 3. Elimino l'account utente
try {
    $stmt = $pdo->prepare("DELETE FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Gestione errore (opzionale)
    echo "Errore nell'eliminazione dell'account utente: " . $e->getMessage();
    exit();
}

// 4. Distruggo la sessione
session_unset();
session_destroy();

// 5. Reindirizzo alla pagina di login
header("Location: pages/login.php");
exit();
?>