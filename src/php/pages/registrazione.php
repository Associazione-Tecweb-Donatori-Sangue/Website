<?php
$titolo = "ATDS - Registrazione";
$descrizione = "Pagina per registrarsi come nuovo utente dell'Associazione Tecweb Donatori Sangue";
$keywords = "registrazione, utente, associazione, sangue, ATDS";

include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "header.php";
echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "registrazione.html");
include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "footer.php";