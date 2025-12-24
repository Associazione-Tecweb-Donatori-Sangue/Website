<?php
$titolo = "ATDS - Home";
$descrizione = "Pagina principale del sito dell'Associazione Tecweb Donatori Sangue";
$keywords = "sangue, donazione, associazione, volontario, solidarietà, ATDS";

include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "header.php";
echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "index.html");
include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "footer.php";