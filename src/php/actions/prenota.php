<?php
require_once '../utility.php';
require_once '../db.php';

// 1. Controllo Sicurezza
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

// 2. Controllo Metodo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recupero dati
    $sede_id = pulisciInput($_POST['luogo']);
    $data = $_POST['data'];
    $ora = pulisciInput($_POST['ora']);
    $tipo = pulisciInput($_POST['donazione']);
    $tipo = ucfirst(strtolower($tipo));
    
    // Se l'admin sta modificando, user_id viene dal form, altrimenti dalla sessione
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : $_SESSION['user_id'];
    
    // Controllo se è una modifica
    $modalitaModifica = isset($_POST['id_prenotazione']);
    $idPrenotazione = $modalitaModifica ? intval($_POST['id_prenotazione']) : null;
    
    // Determino la pagina di redirect per gli errori
    $isAdminModifica = isset($_POST['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    $redirectErrore = $isAdminModifica ? "../pages/modifica_prenotazione.php?id_prenotazione=" . $idPrenotazione : "../pages/dona_ora.php";

    // --- VALIDAZIONI ---
    $oggi = date("Y-m-d");

    // A. Data nel passato?
    if ($data < $oggi) {
        $_SESSION['messaggio_flash'] = "Errore: Non puoi prenotare in una data passata!";
        header("Location: " . $redirectErrore);
        exit();
    }

    // B. Data mancante o Sede mancante?
    if (empty($data) || empty($sede_id) || empty($ora)) {
        $_SESSION['messaggio_flash'] = "Errore: Compila tutti i campi obbligatori.";
        header("Location: " . $redirectErrore);
        exit();
    }

    try {
        // --- C. NUOVO: Controllo intervallo minimo tra donazioni ---
        // Recupera sesso e ultima prenotazione dell'utente
        $stmtUtente = $pdo->prepare(
            "SELECT d.sesso 
             FROM donatori d 
             WHERE d.user_id = ?"
        );
        $stmtUtente->execute([$user_id]);
        $donatore = $stmtUtente->fetch(PDO::FETCH_ASSOC);

        if (!$donatore) {
            $_SESSION['messaggio_flash'] = "Errore: Profilo donatore non trovato. Completa la registrazione.";
            header("Location: " . $redirectErrore);
            exit();
        }
        
        // Recupera l'ultima prenotazione
        $stmtUltima = $pdo->prepare(
            "SELECT MAX(data_prenotazione) as ultima_data 
             FROM lista_prenotazioni 
             WHERE user_id = ?"
        );
        $stmtUltima->execute([$user_id]);
        $risultato = $stmtUltima->fetch(PDO::FETCH_ASSOC);

        if ($risultato['ultima_data']) {
            $ultimaData = new DateTime($risultato['ultima_data']);
            $dataPrenotazione = new DateTime($data);
            
            // Calcola la data minima consentita
            $dataMinima = getDataProssimaDonazione($donatore['sesso'], $risultato['ultima_data']);
            $dataPrenotazione = new DateTime($data);

            // Verifica se la nuova data rispetta l'intervallo
            if ($dataPrenotazione < $dataMinima) {
                $dataFormattata = $dataMinima->format('d/m/Y');
                $mesi = ($donatore['sesso'] === 'Maschio') ? 3 : 6;
                $_SESSION['messaggio_flash'] = "ATTENZIONE! Devi attendere {$mesi} mesi. Prossima data disponibile: {$dataFormattata}";
                header("Location: " . $redirectErrore);
                exit();
            }
        }

        // --- D. Controllo Doppie Prenotazioni ---
        // Evita che l'utente prenoti due volte lo stesso giorno (esclusa la prenotazione corrente se in modifica)
        if ($modalitaModifica) {
            $stmtCheck = $pdo->prepare("SELECT id FROM lista_prenotazioni WHERE user_id = ? AND data_prenotazione = ? AND id != ?");
            $stmtCheck->execute([$user_id, $data, $idPrenotazione]);
        } else {
            $stmtCheck = $pdo->prepare("SELECT id FROM lista_prenotazioni WHERE user_id = ? AND data_prenotazione = ?");
            $stmtCheck->execute([$user_id, $data]);
        }
        
        if ($stmtCheck->rowCount() > 0) {
            $_SESSION['messaggio_flash'] = "Hai già una prenotazione per questa data!";
            header("Location: " . $redirectErrore);
            exit();
        }

        // --- E. Controllo disponibilità fascia oraria ---
        // Verifica quante prenotazioni ci sono già per quella sede, data e ora (escludendo la prenotazione corrente se in modifica)
        if ($modalitaModifica) {
            $stmtDisponibilita = $pdo->prepare(
                "SELECT COUNT(*) as totale 
                 FROM lista_prenotazioni 
                 WHERE sede_id = ? 
                 AND data_prenotazione = ? 
                 AND ora_prenotazione = ?
                 AND id != ?"
            );
            $stmtDisponibilita->execute([$sede_id, $data, $ora, $idPrenotazione]);
        } else {
            $stmtDisponibilita = $pdo->prepare(
                "SELECT COUNT(*) as totale 
                 FROM lista_prenotazioni 
                 WHERE sede_id = ? 
                 AND data_prenotazione = ? 
                 AND ora_prenotazione = ?"
            );
            $stmtDisponibilita->execute([$sede_id, $data, $ora]);
        }
        
        $risultato = $stmtDisponibilita->fetch(PDO::FETCH_ASSOC);
        
        // Se ci sono già 2 prenotazioni, la fascia è piena
        if ($risultato['totale'] >= 2) {
            $_SESSION['messaggio_flash'] = "Spiacenti, la fascia oraria selezionata è già completa. Scegli un altro orario.";
            header("Location: " . $redirectErrore);
            exit();
        }

        // --- INSERIMENTO O AGGIORNAMENTO NEL DB ---
        if ($modalitaModifica) {
            // UPDATE
            $sql = "UPDATE lista_prenotazioni 
                    SET sede_id = ?, data_prenotazione = ?, ora_prenotazione = ?, tipo_donazione = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sede_id, $data, $ora, $tipo, $idPrenotazione]);
            $_SESSION['messaggio_flash'] = "Prenotazione modificata con successo!";
            
            // Se è admin, torna al profilo admin
            if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
                header("Location: ../pages/profilo_admin.php");
            } else {
                header("Location: ../pages/dona_ora.php");
            }
        } else {
            // INSERT
            $sql = "INSERT INTO lista_prenotazioni (user_id, sede_id, data_prenotazione, ora_prenotazione, tipo_donazione) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $sede_id, $data, $ora, $tipo]);
            $_SESSION['messaggio_flash'] = "Prenotazione confermata con successo!";
            header("Location: ../pages/dona_ora.php");
        }

        exit();

    } catch (PDOException $e) {
        // Errore DB
        $_SESSION['messaggio_flash'] = "Errore durante la prenotazione: " . $e->getMessage();
        header("Location: " . $redirectErrore);
        exit();
    }

} else {
    // Se qualcuno prova ad aprire prenota.php direttamente senza passare dal form
    header("Location: ../pages/dona_ora.php");
    exit();
}
?>