<?php
require_once "../utility.php";
require_once "../db.php";

requireLogin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Errore sconosciuto'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'] ?? '';
    
    // --- PERCORSO IMMAGINI (Corretto per la tua struttura Docker) ---
    // Actions è in /var/www/html/php/actions
    // Images è in /var/www/html/images
    // Quindi devo salire di 2 livelli: ../../
    $uploadDir = '../../images/profili/';

    // --- LOGICA UPLOAD ---
    if ($azione === 'upload' && isset($_FILES['foto_profilo'])) {
        $file = $_FILES['foto_profilo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response = ['success' => false, 'message' => 'Errore upload codice: ' . $file['error']];
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $response = ['success' => false, 'message' => 'File troppo grande (Max 5MB)'];
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                $response = ['success' => false, 'message' => 'Formato non valido (solo JPG/PNG)'];
            } else {
                $user_id = $_SESSION['user_id'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'profile_' . $user_id . '_' . uniqid() . '.' . $extension;
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                    try {
                        // Rimuovi vecchia foto
                        $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $oldPhoto = $stmt->fetchColumn();
                        
                        if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
                            if (!@unlink($uploadDir . $oldPhoto)) {
                                logError("Impossibile eliminare vecchia foto: $oldPhoto");
                            }
                        }

                        // Aggiorna DB
                        $stmt = $pdo->prepare("UPDATE utenti SET foto_profilo = ? WHERE id = ?");
                        $stmt->execute([$fileName, $user_id]);

                        $response = ['success' => true, 'fileName' => $fileName];
                    } catch (PDOException $e) {
                        logError("Errore upload foto DB: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Errore durante il salvataggio. Riprova.'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Errore spostamento file. Permessi cartella?'];
                }
            }
        }
    } 
    // --- LOGICA RIMOZIONE ---
    elseif ($azione === 'rimuovi') {
        $user_id = $_SESSION['user_id'];

        try {
            $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
            $stmt->execute([$user_id]);
            $oldPhoto = $stmt->fetchColumn();

            if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
                if (!@unlink($uploadDir . $oldPhoto)) {
                    logError("Impossibile eliminare foto: $oldPhoto");
                }
            }

            $stmt = $pdo->prepare("UPDATE utenti SET foto_profilo = NULL WHERE id = ?");
            $stmt->execute([$user_id]);

            $response = ['success' => true];
        } catch (PDOException $e) {
            logError("Errore rimozione foto DB: " . $e->getMessage());
            $response = ['success' => false, 'message' => 'Errore durante la rimozione. Riprova.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Azione non valida'];
    }
}

echo json_encode($response);
exit();
?>