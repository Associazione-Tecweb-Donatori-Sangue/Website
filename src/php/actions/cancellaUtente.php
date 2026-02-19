<?php
require_once '../utility.php';
require_once '../db.php';

// Sicurezza: solo admin può eliminare utenti
requireAdmin();

// Controllo se ricevo i dati via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_utente'])) {
    
    $idUtente = intval($_POST['id_utente']);
    
    // Validazione ID
    if (!validaInteroPositivo($idUtente)) {
        $_SESSION['messaggio_flash'] = "Errore: Utente non valido.";
        header("Location: ../pages/profilo_admin.php");
        exit();
    }
    
    // Prevenzione: un admin non può eliminare se stesso
    if ($idUtente == $_SESSION['user_id']) {
        $_SESSION['messaggio_flash'] = "Errore: Non puoi eliminare il tuo stesso account.";
        header("Location: ../pages/profilo_admin.php");
        exit();
    }

    try {
        // Elimino il profilo donatore (se esiste)
        $stmt = $pdo->prepare("DELETE FROM donatori WHERE user_id = ?");
        $stmt->execute([$idUtente]);
        
        // Elimino l'account utente (le prenotazioni vengono eliminate automaticamente via CASCADE)
        $stmt = $pdo->prepare("DELETE FROM utenti WHERE id = ?");
        $stmt->execute([$idUtente]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['messaggio_flash'] = "Utente eliminato con successo.";
        } else {
            $_SESSION['messaggio_flash'] = "Errore: Impossibile trovare l'utente.";
        }

    } catch (PDOException $e) {
        logError("Errore eliminazione utente: " . $e->getMessage());
        $_SESSION['messaggio_flash'] = "Errore durante l'eliminazione. Riprova più tardi.";
    }
}

// Redirect alla dashboard admin mantenendo la tab Gestione utenti aperta
header("Location: ../pages/profilo_admin.php?tab=utenti");
exit();
?>
