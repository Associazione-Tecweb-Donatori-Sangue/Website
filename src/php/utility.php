<?php



function costruisciPagina($contentHTML, $breadcrumb, $paginaAttiva = "") {
    
    // Includiamo db.php e catturiamo il return
    $pdo = require_once __DIR__ . "/db.php";
    
    // Se require_once ritorna true (file già incluso), usa il globale
    if ($pdo === true || $pdo === 1) {
        $pdo = $GLOBALS['pdo'] ?? null;
    }

    // 1. Carico i template comuni
    $pathTemplates = __DIR__ . '/../html/templates/';
    $header = file_get_contents($pathTemplates . 'header.html');
    $footer = file_get_contents($pathTemplates . 'footer.html');

    // 2. GESTIONE link profilo
    $linkDestinazione = "/php/pages/login.php";
    $altText = "Accedi";
    $tagHTML = "a";
    
    $fotoNavbar = "/images/profilo.jpg"; // Default base

    // Se l'utente è loggato
    if (isset($_SESSION['username'])) {
        
        $linkDestinazione = "/php/pages/profilo.php";
        $altText = "Profilo di " . $_SESSION['username'];
        
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            $linkDestinazione = "/php/pages/profilo_admin.php";
            $altText = "Profilo Admin";
        }

        // Recupero foto profilo dal database
        if ($pdo !== null && $pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $userFoto = $stmt->fetchColumn();
                
                if ($userFoto) {
                    $version = time();
                    $fotoNavbar = "/images/profili/" . $userFoto . "?v=" . $version;
                }
            } catch (PDOException $e) {
            }
        } 
    }

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

    return $paginaFinale;
}

function pulisciInput($value){
    $value = trim($value);
    $value = strip_tags($value);
    $value = htmlentities($value);
    return $value;
}
?>