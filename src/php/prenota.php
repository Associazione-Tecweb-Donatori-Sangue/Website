<?php
require_once "db.php";
require_once "utility.php";

session_start();

// 1. Controllo Sicurezza
if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit();
}

// 2. Controllo Metodo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recupero dati
    $sede_id = pulisciInput($_POST['luogo']);
    $data = $_POST['data'];
    $ora = pulisciInput($_POST['ora']);
    $tipo = pulisciInput($_POST['donazione']);
    $user_id = $_SESSION['user_id'];

    // --- VALIDAZIONI ---
    $oggi = date("Y-m-d");

    // A. Data nel passato?
    if ($data < $oggi) {
        $_SESSION['messaggio_flash'] = "Errore: Non puoi prenotare in una data passata!";
        // Torno a dona_ora (nota il percorso: entro in pages/)
        header("Location: pages/dona_ora.php");
        exit();
    }

    // B. Data mancante o Sede mancante?
    if (empty($data) || empty($sede_id) || empty($ora)) {
        $_SESSION['messaggio_flash'] = "Errore: Compila tutti i campi obbligatori.";
        header("Location: pages/dona_ora.php");
        exit();
    }

    try {
        // --- C. Controllo Doppie Prenotazioni ---
        // Evita che l'utente prenoti due volte lo stesso giorno
        $stmtCheck = $pdo->prepare("SELECT id FROM lista_prenotazioni WHERE user_id = ? AND data_prenotazione = ?");
        $stmtCheck->execute([$user_id, $data]);
        if ($stmtCheck->rowCount() > 0) {
            $_SESSION['messaggio_flash'] = "Hai già una prenotazione per questa data!";
            header("Location: pages/dona_ora.php");
            exit();
        }

        // --- INSERIMENTO NEL DB ---
        $sql = "INSERT INTO lista_prenotazioni (user_id, sede_id, data_prenotazione, ora_prenotazione, tipo_donazione) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $sede_id, $data, $ora, $tipo]);

        // Successo
        $_SESSION['messaggio_flash'] = "Prenotazione confermata con successo!";
        header("Location: pages/dona_ora.php");
        exit();

    } catch (PDOException $e) {
        // Errore DB
        $_SESSION['messaggio_flash'] = "Errore durante la prenotazione: " . $e->getMessage();
        header("Location: pages/dona_ora.php");
        exit();
    }

} else {
    // Se qualcuno prova ad aprire prenota.php direttamente senza passare dal form
    header("Location: pages/dona_ora.php");
    exit();
}
?>