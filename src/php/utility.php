<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Pulisce l'input utente per sicurezza
 */
function pulisciInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Verifica se l'utente è loggato. Se no, reindirizza.
 */
function requireLogin($redirect = '../pages/login.php') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $redirect);
        exit();
    }
}

/**
 * Verifica se l'utente è Admin. Se no, reindirizza al profilo o login.
 */
function requireAdmin() {
    requireLogin(); // Deve essere prima loggato
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header("Location: ../pages/profilo.php");
        exit();
    }
}

/**
 * Genera l'HTML per il messaggio flash se presente in sessione
 */
function getMessaggioFlashHTML() {
    if (isset($_SESSION['messaggio_flash'])) {
        $msg = $_SESSION['messaggio_flash'];
        
        // Logica colori
        $isError = (strpos($msg, 'Errore') !== false || strpos($msg, 'già') !== false || strpos($msg, 'Attenzione') !== false);
        
        $classe = $isError ? 'msg-error' : 'msg-success';

        $html = '<div class="' . $classe . '">
                    ' . htmlspecialchars($msg) . '
                 </div>';
        
        unset($_SESSION['messaggio_flash']);
        return $html;
    }
    return '';
}

/**
 * Recupera la foto profilo dell'utente dal database
 */
function getFotoProfilo($pdo, $userId) {
    static $stmt = null;
    
    if ($stmt === null) {
        $stmt = $pdo->prepare("SELECT foto_profilo FROM utenti WHERE id = ?");
    }
    
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Carica un template HTML dalla cartella corretta
 */
function caricaTemplate($nomeFile) {
    static $cache = [];
    
    if (isset($cache[$nomeFile])) {
        return $cache[$nomeFile];
    }

    $path = __DIR__ . '/../html/' . $nomeFile; 
    if (file_exists($path)) {
        $cache[$nomeFile] = file_get_contents($path);
        return $cache[$nomeFile];
    }
    return "Errore: Template $nomeFile non trovato.";
}


/**
 * Costruisce la pagina finale unendo header, footer e contenuto
 */
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

    // 2. GESTIONE FAVICON
    $favicon = '<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />';

    // 3. GESTIONE link profilo
    $linkDestinazione = "/php/pages/login.php";
    $altText = "Accedi";
    $tagHTML = "a";
    
    $fotoNavbar = "/images/profilo.png"; // Default base

    // Se l'utente è loggato
    if (isset($_SESSION['username'])) {
        
        $linkDestinazione = "/php/pages/profilo.php";
        $altText = "Profilo di " . $_SESSION['username'];
        
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            $linkDestinazione = "/php/pages/profilo_admin.php";
            $altText = "Profilo admin";
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

   
    if ($paginaAttiva == "login.php" && !isset($_SESSION['username'])) {
        $tagHTML = "span";
    }
    if ($paginaAttiva == "registrazione.php" && !isset($_SESSION['username'])) {
        $tagHTML = "span";
    }
    if (($paginaAttiva == "profilo.php" || $paginaAttiva == "profilo_admin.php") && isset($_SESSION['username'])) {
        $tagHTML = "span";
    }

   
    if ($tagHTML == "a") {
        $linkProfilo = '<a id="linkProfilo" href="'.$linkDestinazione.'"><img src="'.$fotoNavbar.'" alt="'.$altText.'" id="imgProfilo"></a>';
    } else {
        // $fotoNavbar = "/images/profilo_dark.png";
        $linkProfilo = '<span id="linkProfiloActive"><img src="'.$fotoNavbar.'" alt="'.$altText.'" id="imgProfilo"></span>';
    }

    $header = str_replace('[linkProfilo]', $linkProfilo, $header);

    // 4. Gestione Breadcrumb
    $header = str_replace('[BREADCRUMB]', $breadcrumb, $header);

    // 5. Gestione "currentLink" nel menu
    if ($paginaAttiva != "") {
        $pathDaCercare = "/php/pages/" . $paginaAttiva;
        if ($paginaAttiva == "index.php") {
            $pathDaCercare = "/index.php";
        }

        $find = 'href="'.$pathDaCercare.'"';
        $replace = 'id="currentLink" aria-current="page"';
        $header = str_replace($find, $replace, $header);
    }

    // 6. Unisco tutto
    $paginaFinale = str_replace('[HEADER]', $header, $contentHTML);
    $paginaFinale = str_replace('[FOOTER]', $footer, $paginaFinale);
    $paginaFinale = str_replace('[FAVICON]', $favicon, $paginaFinale);

    return $paginaFinale;
}

/**
 * Calcola la data minima per la prossima donazione in base al sesso.
 * @param string $sesso 'Maschio' o 'Femmina'
 * @param string $ultimaData Data ultima donazione (Y-m-d)
 * @return DateTime Data minima calcolata
 */
function getDataProssimaDonazione($sesso, $ultimaData) {
    $mesiIntervallo = ($sesso === 'Maschio') ? 3 : 6;
    $dataMinima = new DateTime($ultimaData);
    $dataMinima->modify("+{$mesiIntervallo} months");
    return $dataMinima;
}
?>