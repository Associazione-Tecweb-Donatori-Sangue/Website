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
 * Logga un errore in modo sicuro
 */
function logError($messaggio, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $messaggio";
    if ($contextStr) {
        $logMessage .= " - Context: $contextStr";
    }
    error_log($logMessage);
}

/**
 * Redirect a pagina di errore 500 con messaggio
 */
function redirectToError500($messaggioUtente = "Si è verificato un errore. Riprova più tardi.") {
    $_SESSION['errore_500'] = $messaggioUtente;
    header("Location: /500.php");
    exit();
}

/**
 * Valida che un valore sia un intero positivo
 */
function validaInteroPositivo($valore, $nomecampo = 'valore') {
    // Verifica che sia numerico, che sia uguale alla sua conversione intera, e che sia positivo
    if (!is_numeric($valore) || $valore != intval($valore) || intval($valore) <= 0) {
        return false;
    }
    return true;
}

/**
 * Valida formato data (Y-m-d)
 */
function validaData($data) {
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}

/**
 * Valida formato orario (HH:MM)
 */
function validaOrario($orario) {
    return (bool) preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $orario);
}

/**
 * Valida username per la registrazione
 * Controlla che: 
 * - Non sia vuoto o composto solo da spazi
 * - Lunghezza tra 4 e 50 caratteri
 * - Inizi con lettera o numero
 * - Finisca con lettera o numero
 * - Contenga almeno 2 caratteri alfanumerici
 * - Contenga solo caratteri alfanumerici, underscore, trattino e punto
 */
function validaUsername($username) {
    // Rimuove spazi all'inizio e alla fine
    $username = trim($username);
    
    // Verifica che non sia vuoto
    if (empty($username)) {
        return ['valido' => false, 'errore' => 'L\'username non può essere vuoto'];
    }
    
    // Verifica lunghezza (4-50 caratteri)
    if (strlen($username) < 4 || strlen($username) > 50) {
        return ['valido' => false, 'errore' => 'L\'username deve essere tra 4 e 50 caratteri'];
    }
    
    // Verifica che inizi con una lettera o numero
    if (!preg_match('/^[a-zA-Z0-9]/', $username)) {
        return ['valido' => false, 'errore' => 'L\'username deve iniziare con una lettera o un numero'];
    }
    
    // Verifica che finisca con una lettera o numero
    if (!preg_match('/[a-zA-Z0-9]$/', $username)) {
        return ['valido' => false, 'errore' => 'L\'username deve finire con una lettera o un numero'];
    }
    
    // Verifica che contenga almeno 2 caratteri alfanumerici
    if (preg_match_all('/[a-zA-Z0-9]/', $username) < 2) {
        return ['valido' => false, 'errore' => 'L\'username deve contenere almeno 2 caratteri alfanumerici'];
    }
    
    // Verifica che contenga solo caratteri validi (alfanumerici, underscore, trattino, punto)
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
        return ['valido' => false, 'errore' => 'L\'username può contenere solo lettere, numeri, underscore, trattino e punto'];
    }
    
    return ['valido' => true, 'errore' => null];
}

/**
 * Valida numero di telefono italiano
 * Accetta formati comuni: 3331234567, 333 123 4567, +39 333 1234567, 041 2345678, etc.
 * 
 * @param string $telefono Il numero di telefono da validare
 * @return array ['valido' => bool, 'errore' => string|null]
 */
function validaTelefono($telefono) {
    // Rimuove spazi all'inizio e alla fine
    $telefono = trim($telefono);
    
    // Verifica che non sia vuoto
    if (empty($telefono)) {
        return ['valido' => false, 'errore' => 'Il numero di telefono non può essere vuoto'];
    }
    
    // Verifica lunghezza massima (con prefisso internazionale)
    if (strlen($telefono) > 20) {
        return ['valido' => false, 'errore' => 'Il numero di telefono è troppo lungo'];
    }
    
    // Verifica che contenga solo caratteri validi (numeri, spazi, +, -, parentesi)
    if (!preg_match('/^[\d\s+\-()]+$/', $telefono)) {
        return ['valido' => false, 'errore' => 'Il numero di telefono può contenere solo numeri, spazi, +, - e parentesi'];
    }
    
    // Estrae solo le cifre per contarle
    $soloCifre = preg_replace('/\D/', '', $telefono);
    
    // Verifica numero minimo di cifre (almeno 9 cifre per numeri italiani)
    if (strlen($soloCifre) < 9) {
        return ['valido' => false, 'errore' => 'Il numero di telefono deve contenere almeno 9 cifre'];
    }
    
    // Verifica numero massimo di cifre (max 13 cifre con prefisso internazionale)
    if (strlen($soloCifre) > 13) {
        return ['valido' => false, 'errore' => 'Il numero di telefono contiene troppe cifre'];
    }
    
    // Se inizia con +39, verifica che abbia 10 cifre dopo il prefisso
    if (preg_match('/^\+39/', $telefono)) {
        $cifreSenzaPrefisso = preg_replace('/^\+39\D*/', '', $telefono);
        $cifreSenzaPrefisso = preg_replace('/\D/', '', $cifreSenzaPrefisso);
        
        if (strlen($cifreSenzaPrefisso) != 10) {
            return ['valido' => false, 'errore' => 'I numeri italiani con +39 devono avere 10 cifre'];
        }
    }
    
    return ['valido' => true, 'errore' => null];
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
        $role = $isError ? ' role="alert"' : '';

        $html = '<div class="' . $classe . '"' . $role . '>
                    ' . htmlspecialchars($msg) . '
                 </div>';
        
        unset($_SESSION['messaggio_flash']);
        return $html;
    }
    return '';
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
        $contenuto = @file_get_contents($path);
        if ($contenuto === false) {
            logError("Impossibile leggere il template: $nomeFile");
            redirectToError500("Errore nel caricamento della pagina");
        }
        $cache[$nomeFile] = $contenuto;
        return $cache[$nomeFile];
    }
    logError("Template non trovato: $nomeFile");
    redirectToError500("Pagina non disponibile");
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
    $favicon = '<link rel="icon" type="image/png" href="/ggiora/images/favicons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/ggiora/images/favicons/favicon.svg" />
    <link rel="shortcut icon" href="/ggiora/images/favicons/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/ggiora/images/favicons/apple-touch-icon.png" />
    <link rel="manifest" href="/ggiora/images/favicons/site.webmanifest" />';

    // 3. GESTIONE link profilo
    $linkDestinazione = "/ggiora/src/php/pages/login.php";
    $altText = "Accedi";
    $tagHTML = "a";
    
    $fotoNavbar = "/ggiora/images/profilo.png";

    // Se l'utente è loggato
    if (isset($_SESSION['username'])) {
        
        $linkDestinazione = "/ggiora/src/php/pages/profilo.php";
        $altText = "Profilo di " . $_SESSION['username'];
        
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            $linkDestinazione = "/ggiora/src/php/pages/profilo_admin.php";
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
                    $fotoNavbar = "/ggiora/images/profili/" . $userFoto . "?v=" . $version;
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
        $linkProfilo = '<span id="linkProfiloActive"><img src="'.$fotoNavbar.'" alt="'.$altText.'" id="imgProfilo"></span>';
    }

    $header = str_replace('[linkProfilo]', $linkProfilo, $header);

    // 4. Gestione Breadcrumb
    $header = str_replace('[BREADCRUMB]', $breadcrumb, $header);

    // 5. Gestione "currentLink" nel menu
    if ($paginaAttiva != "") {
        $pathDaCercare = "/ggiora/src/php/pages/" . $paginaAttiva;
        if ($paginaAttiva == "index.php") {
            $pathDaCercare = "/ggiora/src/index.php";
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
?>
