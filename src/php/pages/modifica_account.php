<?php
require_once "../utility.php";
require_once "../db.php";

// 1. Sicurezza: solo utenti loggati
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Sicurezza Admin: L'admin non può stare qui
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: profilo_admin.php");
    exit();
}

$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = pulisciInput($_POST['username']);
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $userId = $_SESSION['user_id'];

    try {
        // Recupero la password attuale dal DB
        $stmt = $pdo->prepare("SELECT password, username FROM utenti WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Controllo validità password attuale
        if (!$user || !password_verify($oldPassword, $user['password'])) {
            $messaggio = '<div class="msg-error-inline">Errore: La password attuale inserita non è corretta.</div>';
        } else {
            // PASSWORD OK -> Inizio controlli modifiche
            $erroreTrovato = false;
            $modificaEffettuata = false;

            // A. Cambio Username
            if ($newUsername !== $user['username']) {
                // Controllo se esiste già
                $check = $pdo->prepare("SELECT id FROM utenti WHERE username = ? AND id != ?");
                $check->execute([$newUsername, $userId]);
                
                if ($check->fetch()) {
                    $messaggio .= '<div class="msg-error-inline">Errore: Lo username scelto è già in uso.</div>';
                    $erroreTrovato = true;
                } else {
                    $updateUser = $pdo->prepare("UPDATE utenti SET username = ? WHERE id = ?");
                    $updateUser->execute([$newUsername, $userId]);
                    $_SESSION['username'] = $newUsername; // Aggiorno la sessione corrente
                    $modificaEffettuata = true;
                }
            }

            // B. Cambio Password (solo se compilata)
            if (!empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    $messaggio .= '<div class="msg-error-inline">Errore: Le nuove password non coincidono.</div>';
                    $erroreTrovato = true;
                } else {
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updatePass = $pdo->prepare("UPDATE utenti SET password = ? WHERE id = ?");
                    $updatePass->execute([$newHash, $userId]);
                    $modificaEffettuata = true;
                }
            }

            // C. REINDIRIZZAMENTO (Se non ci sono errori)
            if (!$erroreTrovato) {
                if ($modificaEffettuata) {
                    $_SESSION['messaggio_flash'] = "Account aggiornato con successo!";
                } else {
                    $_SESSION['messaggio_flash'] = "Nessuna modifica effettuata.";
                }
                
                // Vado al profilo
                header("Location: profilo.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $messaggio = '<div class="msg-error-inline">Errore Database: ' . $e->getMessage() . '</div>';
    }
}

// Visualizzazione (Questo codice viene eseguito SOLO se c'è un errore e non avviene il redirect)
$template = caricaTemplate('modifica_account.html');
$template = str_replace('[valore_username]', htmlspecialchars($_SESSION['username']), $template);
$template = str_replace('[MESSAGGI]', $messaggio, $template);

// Link indietro
$backLink = "profilo.php";
$template = str_replace('href="profilo.php"', 'href="'.$backLink.'"', $template);

$breadcrumb = '<p><a href="/index.php">Home</a> / <a href="'.$backLink.'">Profilo</a> / <span>Gestione utente</span></p>';

echo costruisciPagina($template, $breadcrumb, "modifica_account.php");
?>