<?php
require_once "../utility.php";
require_once "../db.php";

// Cntrollo sicurezza: admin
requireAdmin();

// Carico il template HTML
$paginaHTML = caricaTemplate('profilo_admin.html');

// LOGICA GESTIONE FOTO PROFILO
$fotoPath = "../../images/profilo.jpg"; 
$isDefaultClass = "is-default";

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
            $isDefaultClass = "";
        }
    }
} catch (PDOException $e) {
    // Errore DB: resta tutto default
}

// Sostituzione dei placeholder per la foto
$paginaHTML = str_replace('[FOTO_PROFILO]', htmlspecialchars($fotoPath), $paginaHTML);
$paginaHTML = str_replace('[CLASS_DEFAULT]', $isDefaultClass, $paginaHTML);

// GESTIONE NOME UTENTE
$nomeUtente = '<h1>' . htmlspecialchars(ucfirst($_SESSION['username'])) . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

// Gestione messaggi flash
$msgHTML = getMessaggioFlashHTML();
if (!empty($msgHTML)) {
    $paginaHTML = str_replace('<main id="content" class="main-standard">', '<main id="content" class="main-standard">' . $msgHTML, $paginaHTML);
}

// Gestione breadcrumb
$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>Profilo Admin</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, "profilo_admin.php");
?>
