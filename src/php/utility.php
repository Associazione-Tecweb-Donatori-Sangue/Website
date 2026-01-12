<?php

/* * Funzione per costruire la pagina finale
 * $contentHTML: il codice HTML specifico della pagina (caricato con file_get_contents)
 * $breadcrumb: il testo del breadcrumb per quella pagina
 * $paginaAttiva: il nome del file (es. 'dona_ora.php') per evidenziare il menu
 */
function costruisciPagina($contentHTML, $breadcrumb, $paginaAttiva = "") {
    
    // 1. Carico i template comuni
    $pathTemplates = __DIR__ . '/../html/templates/';
    $header = file_get_contents($pathTemplates . 'header.html');
    $footer = file_get_contents($pathTemplates . 'footer.html');

    // 2. GESTIONE link profilo
    // Default: L'utente non è loggato -> Link a Login
    $linkDestinazione = "/php/pages/login.php";
    $altText = "Accedi";
    $tagHTML = "a";

    // Se l'utente è loggato -> Link a profilo.php (o profilo_admin.php se admin)
    if (isset($_SESSION['username'])) {
        $linkDestinazione = "/php/pages/profilo.php";
        $altText = "Profilo di " . $_SESSION['username'];
        
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            $linkDestinazione = "/php/pages/profilo_admin.php";
            $altText = "Profilo Admin";
        }
    }

    // Se sono sulla pagina login.php e NON sono loggato -> Non cliccabile
    if ($paginaAttiva == "login.php" && !isset($_SESSION['username'])) {
        $tagHTML = "span"; 
    }

    // Se sono sulla pagina di registrazione.php e NON sono loggato -> Non cliccabile
    if ($paginaAttiva == "registrazione.php" && !isset($_SESSION['username'])) {
        $tagHTML = "span";
    }

    // Se sono sulla pagina profilo.php (o profilo_admin.php) e SONO loggato -> Non cliccabile
    if (($paginaAttiva == "profilo.php" || $paginaAttiva == "profilo_admin.php") && isset($_SESSION['username'])) {
        $tagHTML = "span";
    }

    // Costruisco l'HTML del profilo in base alle decisioni sopra
    if ($tagHTML == "a") {
        $linkProfilo = '<a id="linkProfilo" href="'.$linkDestinazione.'"><img src="/images/profilo.jpg" alt="'.$altText.'" id="imgProfilo"></a>';
    } else {
        // È uno span (non cliccabile)
        $linkProfilo = '<span id="linkProfilo"><img src="/images/profilo.jpg" alt="'.$altText.'" id="imgProfilo"></span>';
    }

    // Sostituisco il segnaposto DENTRO l'header
    $header = str_replace('[linkProfilo]', $linkProfilo, $header);

    // 3. Gestione Breadcrumb
    $header = str_replace('[BREADCRUMB]', $breadcrumb, $header);

    // 4. Gestione "currentLink" nel menu
    // Cerca la stringa href="Nome paginaAttiva" e la sostituisce con id="currentLink", per index il link attivo è href="../../index.php"
    if ($paginaAttiva != "") {
        $pathDaCercare = "/php/pages/" . $paginaAttiva;
        if($paginaAttiva == "index.php") $pathDaCercare = "/index.php";

        $find = 'href="'.$pathDaCercare.'"';
        $replace = 'id="currentLink" aria-current="page"';
        $header = str_replace($find, $replace, $header);
    }

    // 5. Unisco tutto
    $paginaFinale = str_replace('[HEADER]', $header, $contentHTML);
    $paginaFinale = str_replace('[FOOTER]', $footer, $paginaFinale);

    return $paginaFinale;
}

/* Funzione per pulire l'input dell'utente e prevenire XSS
*/
function pulisciInput($value){
    $value = trim($value);
    $value = strip_tags($value);
    $value = htmlentities($value);
    return $value;
}


?>