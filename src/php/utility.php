<?php

function logDebug($message) {
    file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
}

function costruisciPagina($contentHTML, $breadcrumb, $paginaAttiva = "") {
    
    logDebug("=== INIZIO costruisciPagina ===");
    logDebug("Pagina attiva: " . $paginaAttiva);
    
    // Includiamo db.php e catturiamo il return
    $pdo = require_once __DIR__ . "/db.php";
    
    // Se require_once ritorna true (file già incluso), usa il globale
    if ($pdo === true || $pdo === 1) {
        $pdo = $GLOBALS['pdo'] ?? null;
        logDebug("PDO da GLOBALS (file già incluso)");
    } else {
        logDebug("PDO da return di db.php");
    }
    
    logDebug("PDO finale: " . ($pdo !== null && $pdo instanceof PDO ? 'DISPONIBILE' : 'NULL'));

    // 1. Carico i template comuni
    $pathTemplates = __DIR__ . '/../html/templates/';
    $header = file_get_contents($pathTemplates . 'header.html');
    $footer = file_get_contents($pathTemplates . 'footer.html');

    // 2. GESTIONE link profilo
    $linkDestinazione = "/php/pages/login.php";
    $altText = "Accedi";
    $tagHTML = "a";
    
    $fotoNavbar = "/images/profilo.jpg"; // Default base
    logDebug("Foto navbar iniziale (default): " . $fotoNavbar);

    // Se l'utente è loggato
    if (isset($_SESSION['username'])) {
        logDebug("Utente loggato: " . $_SESSION['username']);
        logDebug("User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NON SETTATO'));
        
        $linkDestinazione = "/php/pages/profilo.php";
        $altText = "Profilo di " . $_SESSION['username'];
        
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            $linkDestinazione = "/php/pages/profilo_admin.php";
            $altText = "Profilo Admin";
            logDebug("Utente è admin");
        }

        // Recupero foto profilo dal database
        if ($pdo !== null && $pdo instanceof PDO) {
            logDebug("PDO disponibile");
            try {
                $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $userFoto = $stmt->fetchColumn();
                
                logDebug("Foto recuperata dal DB: " . ($userFoto ? $userFoto : 'NESSUNA'));
                
                if ($userFoto) {
                    $version = time();
                    $fotoNavbar = "/images/profili/" . $userFoto . "?v=" . $version;
                    logDebug("Foto navbar aggiornata: " . $fotoNavbar);
                } else {
                    logDebug("userFoto è vuoto, resto con default");
                }
            } catch (PDOException $e) {
                logDebug("ERRORE DB: " . $e->getMessage());
            }
        } else {
            logDebug("PDO è NULL o non è un oggetto PDO!");
        }
    } else {
        logDebug("Utente NON loggato (SESSION username non settato)");
    }

    logDebug("Foto navbar finale: " . $fotoNavbar);

    // Controlli per tag span (non cliccabile)
    if ($paginaAttiva == "login.php" && !isset($_SESSION['username'])) {
        $tagHTML = "span";
    }
    if ($paginaAttiva == "registrazione.php" && !isset($_SESSION['username'])) {
        $tagHTML = "span";
    }
    if (($paginaAttiva == "profilo.php" || $paginaAttiva == "profilo_admin.php") && isset($_SESSION['username'])) {
        $tagHTML = "span";
    }

    // Costruisco l'HTML del profilo
    if ($tagHTML == "a") {
        $linkProfilo = '<a id="linkProfilo" href="'.$linkDestinazione.'"><img src="'.$fotoNavbar.'" alt="'.$altText.'" id="imgProfilo"></a>';
    } else {
        $linkProfilo = '<span id="linkProfilo"><img src="'.$fotoNavbar.'" alt="'.$altText.'" id="imgProfilo"></span>';
    }

    logDebug("HTML link profilo generato");

    // Sostituisco il segnaposto DENTRO l'header
    $header = str_replace('[linkProfilo]', $linkProfilo, $header);

    // 3. Gestione Breadcrumb
    $header = str_replace('[BREADCRUMB]', $breadcrumb, $header);

    // 4. Gestione "currentLink" nel menu
    if ($paginaAttiva != "") {
        $pathDaCercare = "/php/pages/" . $paginaAttiva;
        if ($paginaAttiva == "index.php") {
            $pathDaCercare = "/index.php";
        }

        $find = 'href="'.$pathDaCercare.'"';
        $replace = 'id="currentLink" aria-current="page"';
        $header = str_replace($find, $replace, $header);
    }

    // 5. Unisco tutto
    $paginaFinale = str_replace('[HEADER]', $header, $contentHTML);
    $paginaFinale = str_replace('[FOOTER]', $footer, $paginaFinale);

    logDebug("=== FINE costruisciPagina ===\n");

    return $paginaFinale;
}

function pulisciInput($value){
    $value = trim($value);
    $value = strip_tags($value);
    $value = htmlentities($value);
    return $value;
}
?>