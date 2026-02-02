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
    $sede_id = pulisciInput($_POST['luogo']);
    $data = $_POST['data'];
    $ora = pulisciInput($_POST['ora']);
    $tipo = pulisciInput($_POST['donazione']);
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : $_SESSION['user_id'];
    $modalitaModifica = isset($_POST['id_prenotazione']);
    $idPrenotazione = $modalitaModifica ? intval($_POST['id_prenotazione']) : null;
    $isAdminModifica = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    $redirectErrore = ($modalitaModifica && $isAdminModifica) 
        ? "../pages/modifica_prenotazione.php?id_prenotazione=" . $idPrenotazione 
        : "../pages/dona_ora.php";

    // VALIDAZIONI INPUT
    
    // Valida sede_id
    if (!validaInteroPositivo($sede_id)) {
        $_SESSION['messaggio_flash'] = "Errore: sede non valida.";
        header("Location: " . $redirectErrore);
        exit();
    }
    
    // Valida data
    if (!validaData($data)) {
        $_SESSION['messaggio_flash'] = "Errore: formato data non valido.";
        header("Location: " . $redirectErrore);
        exit();
    }
    
    // Valida orario
    if (!validaOrario($ora)) {
        $_SESSION['messaggio_flash'] = "Errore: formato orario non valido.";
        header("Location: " . $redirectErrore);
        exit();
    }
    
    // Valida user_id
    if (!validaInteroPositivo($user_id)) {
        $_SESSION['messaggio_flash'] = "Errore: utente non valido.";
        header("Location: " . $redirectErrore);
        exit();
    }

    // VALIDAZIONI BUSINESS
    $oggi = date("Y-m-d");

    // A. Data nel passato
    if ($data < $oggi) {
        if ($isAdminModifica) {
            $_SESSION['messaggio_flash'] = "Errore: non puoi spostare una prenotazione in una data passata!";
        } else {
            $_SESSION['messaggio_flash'] = "Errore: non puoi prenotare in una data passata!";
        }
        header("Location: " . $redirectErrore);
        exit();
    }

    // B. Campi obbligatori
    if (empty($data) || empty($sede_id) || empty($ora)) {
        $_SESSION['messaggio_flash'] = "Errore: compila tutti i campi obbligatori.";
        header("Location: " . $redirectErrore);
        exit();
    }

    try {
        // C. Controllo intervallo minimo
        $stmtUtente = $pdo->prepare("SELECT sesso FROM donatori WHERE user_id = ?");
        $stmtUtente->execute([$user_id]);
        $donatore = $stmtUtente->fetch(PDO::FETCH_ASSOC);
        $sogliaMesi = ($donatore && $donatore['sesso'] === 'Femmina') ? 6 : 3;

        // 1. Cerchiamo la prenotazione precedente più vicina
        $stmtPrec = $pdo->prepare("SELECT MAX(data_prenotazione) FROM lista_prenotazioni WHERE user_id = ? AND data_prenotazione < ? AND id != ?");
        $stmtPrec->execute([$user_id, $data, $idPrenotazione ?? 0]);
        $dataPrecedente = $stmtPrec->fetchColumn();

        // 2. Cerchiamo la prenotazione successiva più vicina
        $stmtSucc = $pdo->prepare("SELECT MIN(data_prenotazione) FROM lista_prenotazioni WHERE user_id = ? AND data_prenotazione > ? AND id != ?");
        $stmtSucc->execute([$user_id, $data, $idPrenotazione ?? 0]);
        $dataSuccessiva = $stmtSucc->fetchColumn();

        $intervalloViolato = false;
        $dataConflitto = null;
        
        // Oggetti DateTime
        $dataSceltaObj = new DateTime($data);
        $dataSceltaObj->setTime(0, 0, 0);

        // A. Controllo con la precedente
        if ($dataPrecedente) {
            $dataPrecObj = new DateTime($dataPrecedente);
            $dataPrecObj->setTime(0, 0, 0);
            
            $limiteSicuro = clone $dataPrecObj;
            $limiteSicuro->modify("+$sogliaMesi months");
            
            if ($dataSceltaObj < $limiteSicuro) {
                $intervalloViolato = true;
                $dataConflitto = $dataPrecedente;
            }
        }

        // B. Controllo con la successiva
        if (!$intervalloViolato && $dataSuccessiva) {
            $dataSuccObj = new DateTime($dataSuccessiva);
            $dataSuccObj->setTime(0, 0, 0);
            
            $limiteSicuro = clone $dataSceltaObj;
            $limiteSicuro->modify("+$sogliaMesi months");

            if ($dataSuccObj < $limiteSicuro) {
                $intervalloViolato = true;
                $dataConflitto = $dataSuccessiva;
            }
        }

        // Violazione
        if ($intervalloViolato) {
            $dataSceltaFormatted = $dataSceltaObj->format('d/m/Y');
            $dataConflictFormatted = (new DateTime($dataConflitto))->format('d/m/Y');

            $msg = "Errore: non è possibile prenotare per il {$dataSceltaFormatted}, questa data risulta troppo vicina alla prenotazione del {$dataConflictFormatted}. ";
            $msg .= "È necessario attendere almeno {$sogliaMesi} mesi tra una donazione e l'altra.";

            $_SESSION['messaggio_flash'] = $msg;
            
            header("Location: " . $redirectErrore);
            exit();
        }

        // D. Controllo doppie prenotazioni
        $stmtCheck = $pdo->prepare("SELECT id FROM lista_prenotazioni WHERE user_id = ? AND data_prenotazione = ? AND id != ?");
        $stmtCheck->execute([$user_id, $data, $idPrenotazione ?? 0]);
        
        if ($stmtCheck->rowCount() > 0) {
            $_SESSION['messaggio_flash'] = "Errore: esiste già una prenotazione nella stessa data selezionata!";
            header("Location: " . $redirectErrore);
            exit();
        }

        // E. Controllo disponibilità fascia oraria
        $stmtDisp = $pdo->prepare(
            "SELECT COUNT(*) FROM lista_prenotazioni 
             WHERE sede_id = ? AND data_prenotazione = ? AND ora_prenotazione = ? AND id != ?"
        );
        $stmtDisp->execute([$sede_id, $data, $ora, $idPrenotazione ?? 0]);
        
        if ($stmtDisp->fetchColumn() >= 2) {
            $_SESSION['messaggio_flash'] = "Errore: la fascia oraria selezionata è già completa.";
            header("Location: " . $redirectErrore);
            exit();
        }

        // Salva
        if ($modalitaModifica) {
            $sql = "UPDATE lista_prenotazioni SET sede_id = ?, data_prenotazione = ?, ora_prenotazione = ?, tipo_donazione = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$sede_id, $data, $ora, $tipo, $idPrenotazione]);
            $_SESSION['messaggio_flash'] = "Prenotazione modificata con successo!";
        } else {
            $sql = "INSERT INTO lista_prenotazioni (user_id, sede_id, data_prenotazione, ora_prenotazione, tipo_donazione) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$user_id, $sede_id, $data, $ora, $tipo]);
            $_SESSION['messaggio_flash'] = "Prenotazione confermata!";
        }

        // Redirect finale
        header("Location: " . ($isAdminModifica ? "../pages/profilo_admin.php" : "../pages/dona_ora.php"));
        exit();

    } catch (PDOException $e) {
        logError("Errore prenotazione: " . $e->getMessage());
        $_SESSION['messaggio_flash'] = "Errore durante l'operazione. Riprova più tardi.";
        header("Location: " . $redirectErrore);
        exit();
    }
}
