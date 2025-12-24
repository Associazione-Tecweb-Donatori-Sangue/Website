<?php
$titolo = "ATDS - Prenotazione";
$descrizione = "Pagina per prenotare una donazione di sangue presso l'Associazione Tecweb Donatori Sangue";
$keywords = "prenotazione, donazione, sangue, associazione, volontario, ATDS";

include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "header.php";
echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "dona_ora.html");
include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "footer.php";