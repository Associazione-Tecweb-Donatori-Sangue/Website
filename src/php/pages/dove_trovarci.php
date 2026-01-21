<?php
require_once "../utility.php";
require_once "../db.php";

$paginaHTML = caricaTemplate('dove_trovarci.html');

try {
    $stmt = $pdo->query("SELECT * FROM sedi ORDER BY nome ASC");
    $sedi = $stmt->fetchAll();

    $cardsHTML = "";

    foreach ($sedi as $sede) {
        // Preparo i dati per l'HTML
        
        // A. Gestione Nome con ABBR
        // Se nel DB c'è scritto "ATDS Piovego", lo trasformo in "<abbr...>ATDS</abbr> Piovego"
        $nomeSede = htmlspecialchars($sede['nome']);
        $nomeSedeFormattato = str_replace(
            "ATDS", 
            '<abbr title="Associazione Tecweb Donatori Sangue">ATDS</abbr>', 
            $nomeSede
        );

        // B. Gestione Percorso Immagine
        // Nel DB è salvato come "images/nomefile.jpg", ma noi siamo in php/pages/
        // quindi dobbiamo aggiungere "../../" davanti.
        $percorsoImmagine = "../../" . htmlspecialchars($sede['immagine']);
        
        // C. Gestione Telefono (pulizia per il link tel:)
        // Rimuovo spazi e caratteri non numerici per l'href
        $telefonoVisualizzato = htmlspecialchars($sede['telefono']);
        $telefonoLink = preg_replace('/[^0-9+]/', '', $telefonoVisualizzato);

        // D. Costruzione della CARD
        $cardsHTML .= '
        <div class="sede">
            <h3>' . $nomeSedeFormattato . '</h3>
            <img src="' . $percorsoImmagine . '" alt="Sede ' . $nomeSede . '">
            <p>' . htmlspecialchars($sede['descrizione']) . '</p>
            <p> 
                Indirizzo: ' . htmlspecialchars($sede['indirizzo']) . '
                <a href="' . htmlspecialchars($sede['link_maps']) . '" target="_blank">Visualizza su Google Maps</a>
            </p>
            <p>Telefono: <a href="tel:' . $telefonoLink . '" aria-label="Chiama ' . $telefonoVisualizzato . '">' . $telefonoVisualizzato . '</a></p>
            <a href="../../php/pages/dona_ora.php?sede_id=' . $sede['id'] . '" class="btn_prenota">Dona qui</a>
        </div>';
    }

    // Se non ci sono sedi, mostro un messaggio
    if (empty($cardsHTML)) {
        $cardsHTML = '<p class="testo_std">Nessuna sede trovata nel database.</p>';
    }

    // 2. Sostituisco il segnaposto
    $paginaHTML = str_replace('[cardsSedi]', $cardsHTML, $paginaHTML);

} catch (PDOException $e) {
    // Gestione errore DB
    $errore = '<p class="errore">Impossibile caricare le sedi al momento. Riprova più tardi.</p>';
    $paginaHTML = str_replace('[cardsSedi]', $errore, $paginaHTML);
}

// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Dove trovarci</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'dove_trovarci.php');
?>

