<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "../utility.php";
require_once "../db.php";

session_start();

// 1. CONTROLLO SICUREZZA: L'utente è loggato ED è admin?
if (!isset($_SESSION['username']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
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
$nomeUtente = '<h1>' . htmlspecialchars($_SESSION['username']) . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

// 3. Gestione breadcrumb
$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>Profilo Admin</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, "profilo_admin.php");
?>