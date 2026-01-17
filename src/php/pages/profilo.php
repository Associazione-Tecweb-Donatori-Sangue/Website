<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "../utility.php";
require_once "../db.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$paginaHTML = file_get_contents('../../html/profilo.html');

// --- LOGICA GESTIONE FOTO PROFILO ---
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

// Sostituzione dei placeholder
$paginaHTML = str_replace('[FOTO_PROFILO]', htmlspecialchars($fotoPath), $paginaHTML);
$paginaHTML = str_replace('[CLASS_DEFAULT]', $isDefaultClass, $paginaHTML);

// --- LOGICA DONATORE (Invariata) ---
$htmlDonatore = "";
try {
    $stmt = $pdo->prepare("SELECT * FROM donatori WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $datiDonatore = $stmt->fetch();

    if (!$datiDonatore) {
        $htmlDonatore = '<div class="button_std"><a href="/php/pages/registrazione_donatore.php" class="button">Completa la registrazione come donatore</a></div>';
    } else {
        $htmlDonatore = '
        <section class="dati_donatore_box">
            <h3 class="titolo-dashboard">Il tuo profilo Donatore</h3>
            <dl class="lista_dati">
                <dt>Nome e Cognome:</dt><dd>' . htmlspecialchars($datiDonatore['nome']) . ' ' . htmlspecialchars($datiDonatore['cognome']) . '</dd>
                <dt>Gruppo Sanguigno:</dt><dd class="evidenziato">' . htmlspecialchars($datiDonatore['gruppo_sanguigno']) . '</dd>
                <dt>Email:</dt><dd>' . htmlspecialchars($datiDonatore['email']) . '</dd>
                <dt>Telefono:</dt><dd>' . htmlspecialchars($datiDonatore['telefono']) . '</dd>
            </dl>
            <div class="button_std" style="margin-top: 20px;"><a href="/php/pages/registrazione_donatore.php" class="button">Modifica i tuoi dati</a></div>
        </section>';
        $_SESSION['dati_donatore'] = $datiDonatore;
    }
} catch (PDOException $e) {
    $htmlDonatore = "<p>Errore nel recupero dati.</p>";
}

$paginaHTML = str_replace('[sezioneDonatore]', $htmlDonatore, $paginaHTML);
$nomeUtente = '<h1>' . htmlspecialchars($_SESSION['username']) . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>Profilo</span></p>';
echo costruisciPagina($paginaHTML, $breadcrumb, "profilo.php");
?>