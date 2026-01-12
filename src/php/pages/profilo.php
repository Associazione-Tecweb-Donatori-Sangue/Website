<?php
require_once "../utility.php";
require_once "../db.php";

session_start();

// 1. Controllo sicurezza: se non è loggato, via al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Carico il template HTML
$paginaHTML = file_get_contents('../../html/profilo.html');

// Logica donatore
$htmlDonatore = "";

try {
    $stmt = $pdo->prepare("SELECT * FROM donatori WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $datiDonatore = $stmt->fetch();

    if (!$datiDonatore) {
        // CASO A: Non è ancora donatore
        $htmlDonatore = '
        <div class="button_std">
            <a href="/php/pages/registrazione_donatore.php" class="button"><span lang="en">Completa la registrazione come donatore</span></a>
        </div>';
    } else {
        // CASO B: È già donatore -> Mostro i dati dal DB
        $htmlDonatore = '
        <section class="dati_donatore_box">
            <h3 class="titolo_terziario">Il tuo profilo Donatore</h3>
            <dl class="lista_dati">
                <dt>Nome e Cognome:</dt>
                <dd>' . htmlspecialchars($datiDonatore['nome']) . ' ' . htmlspecialchars($datiDonatore['cognome']) . '</dd>
                
                <dt>Gruppo Sanguigno:</dt>
                <dd class="evidenziato">' . htmlspecialchars($datiDonatore['gruppo_sanguigno']) . '</dd>
                
                <dt>Email:</dt>
                <dd>' . htmlspecialchars($datiDonatore['email']) . '</dd>
                
                <dt>Telefono:</dt>
                <dd>' . htmlspecialchars($datiDonatore['telefono']) . '</dd>
            </dl>
            
            <div class="button_std" style="margin-top: 20px;">
                <a href="/php/pages/registrazione_donatore.php" class="button">Modifica i tuoi dati</a>
            </div>
        </section>';
        
        // Salviamo i dati in sessione per pre-compilare il form di modifica
        $_SESSION['dati_donatore'] = $datiDonatore;
    }
} catch (PDOException $e) {
    $htmlDonatore = "<p>Errore nel recupero dati: " . $e->getMessage() . "</p>";
}

// Inserisco il blocco donatore nella pagina
$paginaHTML = str_replace('[sezioneDonatore]', $htmlDonatore, $paginaHTML);

// 3. Gestione contenuto specifico della pagina
$nomeUtente = '<h1> Profilo di ' . htmlspecialchars($_SESSION['username']) . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>Profilo</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, "profilo.php");
?>