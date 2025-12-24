<?php
$titolo = "ATDS - Profilo";
$descrizione = "Pagina per visualizzare il profilo utente dell'Associazione Tecweb Donatori Sangue";
$keywords = "profilo, utente, associazione, sangue, ATDS";

include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "header.php";
echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "profilo.html");
include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "footer.php";