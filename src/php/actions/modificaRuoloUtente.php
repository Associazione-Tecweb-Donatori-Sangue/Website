<?php
require_once '../utility.php';
require_once '../db.php';

// Sicurezza: solo admin può modificare ruoli
requireAdmin();

// Controllo se ricevo i dati via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_utente']) && isset($_POST['nuovo_ruolo'])) {
    
    $idUtente = intval($_POST['id_utente']);
    $nuovoRuolo = trim($_POST['nuovo_ruolo']);
    
    // Validazione ID
    if (!validaInteroPositivo($idUtente)) {
        $_SESSION['messaggio_flash'] = "Errore: Utente non valido.";
        header("Location: ../pages/profilo_admin.php");
        exit();
    }
    
    // Validazione ruolo (deve essere uno dei tre consentiti)
    $ruoliConsentiti = ['utente', 'donatore', 'admin'];
    if (!in_array($nuovoRuolo, $ruoliConsentiti)) {
        $_SESSION['messaggio_flash'] = "Errore: Ruolo non valido.";
        header("Location: ../pages/profilo_admin.php");
        exit();
    }
    
    // Prevenzione: un admin non può modificare il proprio ruolo
    if ($idUtente == $_SESSION['user_id']) {
        $_SESSION['messaggio_flash'] = "Errore: Non puoi modificare il tuo stesso ruolo.";
        header("Location: ../pages/profilo_admin.php");
        exit();
    }

    try {
        // Aggiorno il ruolo dell'utente
        $stmt = $pdo->prepare("UPDATE utenti SET ruolo = ? WHERE id = ?");
        $stmt->execute([$nuovoRuolo, $idUtente]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['messaggio_flash'] = "Ruolo utente modificato con successo.";
        } else {
            $_SESSION['messaggio_flash'] = "Nessuna modifica effettuata. Il ruolo potrebbe essere già quello selezionato.";
        }

    } catch (PDOException $e) {
        logError("Errore modifica ruolo utente: " . $e->getMessage());
        $_SESSION['messaggio_flash'] = "Errore durante la modifica. Riprova più tardi.";
    }
}

// Redirect alla dashboard admin mantenendo la tab Gestione utenti aperta
header("Location: ../pages/profilo_admin.php?tab=utenti");
exit();
?>
