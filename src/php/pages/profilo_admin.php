<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "../utility.php";
require_once "../db.php";

session_start();

// 1. CONTROLLO SICUREZZA
// A. Se l'utente non è proprio loggato -> vai al Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// B. Se è loggato, ma NON è admin -> vai al Profilo Utente
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: profilo.php");
    exit();
}

// 2. Carico il template HTML
$paginaHTML = file_get_contents('../../html/profilo_admin.html');

// --- LOGICA GESTIONE FOTO PROFILO (stessa dell'utente normale) ---
$fotoPath = "../../images/profilo.jpg"; 
$isDefaultClass = "is-default"; // Assumiamo sia default all'inizio

try {
    $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Se il campo non è vuoto, verifichiamo il file
    if ($user && !empty($user['foto_profilo'])) {
        $nomeFile = $user['foto_profilo'];
        $percorsoFisico = "../../images/profili/" . $nomeFile;
        
        if (file_exists($percorsoFisico)) {
            $fotoPath = $percorsoFisico;
            $isDefaultClass = ""; // C'è una foto valida: togliamo is-default per mostrare il tasto
        }
    }
} catch (PDOException $e) {
    // Errore DB: resta tutto default
}

// Sostituzione dei placeholder per la foto
$paginaHTML = str_replace('[FOTO_PROFILO]', htmlspecialchars($fotoPath), $paginaHTML);
$paginaHTML = str_replace('[CLASS_DEFAULT]', $isDefaultClass, $paginaHTML);

// --- GESTIONE NOME UTENTE ---
$nomeUtente = '<h1>' . htmlspecialchars(ucfirst($_SESSION['username'])) . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

// --- GESTIONE MESSAGGIO FLASH (Successo/Errore Cancellazione) ---
if (isset($_SESSION['messaggio_flash'])) {
    // 1. Decido i colori (Rosso per errori, Verde per successo)
    $colore = (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) ? '#f8d7da' : '#d4edda';
    $testoColore = (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) ? '#721c24' : '#155724';
    $bordo = (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) ? '#f5c6cb' : '#c3e6cb';

    // 2. Creo il box HTML
    $msgHTML = '<div style="background-color: '.$colore.'; color: '.$testoColore.'; border: 1px solid '.$bordo.'; padding: 15px; margin: 20px auto; width: 90%; max-width: 800px; border-radius: 5px; text-align: center;">
                    ' . htmlspecialchars($_SESSION['messaggio_flash']) . '
                </div>';
    
    // 3. Inserisco il messaggio subito dopo l'apertura del tag <main>
    // Cerco la stringa esatta del tuo template. Se cambia classi o ID, aggiorna questa riga.
    $tagMain = '<main id="content" class="main_std">';
    
    if (strpos($paginaHTML, $tagMain) !== false) {
        $paginaHTML = str_replace($tagMain, $tagMain . $msgHTML, $paginaHTML);
    } else {
        // Fallback intelligente: se il tag è scritto diversamente, lo trovo con una regex
        $paginaHTML = preg_replace('/<main[^>]*>/', '$0' . $msgHTML, $paginaHTML, 1);
    }
    
    // 4. Pulisco la sessione per non mostrare il messaggio all'infinito
    unset($_SESSION['messaggio_flash']);
}

// 3. Gestione breadcrumb
$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>Profilo Admin</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, "profilo_admin.php");
?>