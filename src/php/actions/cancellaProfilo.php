<?php
require_once '../utility.php';
require_once '../db.php';

// 1. Controllo sicurezza: se non è loggato, via al login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

try {
    // 2. Elimino il profilo donatore se esiste
    $stmt = $pdo->prepare("DELETE FROM donatori WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // 3. Elimino l'account utente
    $stmt = $pdo->prepare("DELETE FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // 4. Distruggo la sessione
    session_unset();
    session_destroy();
    
    // 5. Imposto messaggio di successo per la prossima sessione
    session_start();
    $_SESSION['messaggio_flash'] = "Account eliminato con successo.";
    
    // 6. Reindirizzo alla pagina di login
    header("Location: ../pages/login.php");
    exit();
    
} catch (PDOException $e) {
    logError("Errore cancellazione profilo: " . $e->getMessage());
    $_SESSION['messaggio_flash'] = "Errore durante l'eliminazione dell'account. Riprova più tardi.";
    header("Location: ../pages/profilo.php");
    exit();
}
?>