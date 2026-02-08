<?php
require_once "../utility.php";
require_once "../db.php";

// 1. Sicurezza: solo utenti loggati
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Sicurezza Admin
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
            $messaggio = '<div class="msg-error" role="alert">Errore: La password attuale inserita non è corretta.</div>';
        } else {
            // PASSWORD OK -> Inizio controlli modifiche
            $erroreTrovato = false;
            $modificaEffettuata = false;

            // A. Cambio Username
            if ($newUsername !== $user['username']) {
                $check = $pdo->prepare("SELECT id FROM utenti WHERE username = ? AND id != ?");
                $check->execute([$newUsername, $userId]);
                
                if ($check->fetch()) {
                    $messaggio .= '<div class="msg-error" role="alert">Errore: Lo username scelto è già in uso.</div>';
                    $erroreTrovato = true;
                } else {
                    $updateUser = $pdo->prepare("UPDATE utenti SET username = ? WHERE id = ?");
                    $updateUser->execute([$newUsername, $userId]);
                    $_SESSION['username'] = $newUsername;
                    $modificaEffettuata = true;
                }
            }

            // B. Cambio Password
            if (!empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    $messaggio .= '<div class="msg-error" role="alert">Errore: Le nuove password non coincidono.</div>';
                    $erroreTrovato = true;
                } else {
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updatePass = $pdo->prepare("UPDATE utenti SET password = ? WHERE id = ?");
                    $updatePass->execute([$newHash, $userId]);
                    $modificaEffettuata = true;
                }
            }

            if (!$erroreTrovato) {
                if ($modificaEffettuata) {
                    $_SESSION['messaggio_flash'] = "Account aggiornato con successo!";
                } else {
                    $_SESSION['messaggio_flash'] = "Nessuna modifica effettuata.";
                }
           
                header("Location: profilo.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        logError("Errore modifica account: " . $e->getMessage());
        $messaggio = '<div class="msg-error" role="alert">Errore durante l\'aggiornamento. Riprova più tardi.</div>';
    }
}

// Visualizzazione
$template = caricaTemplate('modifica_account.html');
$template = str_replace('[valore_username]', htmlspecialchars($_SESSION['username']), $template);
$template = str_replace('[MESSAGGI]', $messaggio, $template);

// Link indietro
$backLink = "profilo.php";
$template = str_replace('href="profilo.php"', 'href="'.$backLink.'"', $template);

$breadcrumb = '<p><a href="/ggiora/src/index.php" lang="en">Home</a> / <a href="'.$backLink.'">Profilo</a> / <span>Gestione utente</span></p>';

echo costruisciPagina($template, $breadcrumb, "modifica_account.php");
?>
