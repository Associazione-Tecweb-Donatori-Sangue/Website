<?php
require_once "../utility.php";
require_once "../db.php";

// 1. Cntrollo sicurezza: solo admin
requireAdmin();

// 2. Carico il template HTML
$paginaHTML = caricaTemplate('profilo_admin.html');

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
            $fotoPath = $percorsoFisico . "?v=" . time();
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

// 3. Gestione messaggi flash
$msgHTML = getMessaggioFlashHTML();
// Inserimento nel main (come facevi prima)
if (!empty($msgHTML)) {
    $paginaHTML = str_replace('<main id="content" class="main_std">', '<main id="content" class="main_std">' . $msgHTML, $paginaHTML);
}

// 3. Gestione breadcrumb
$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>Profilo Admin</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, "profilo_admin.php");
?>