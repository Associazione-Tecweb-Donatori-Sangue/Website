<?php
$titolo = "ATDS - Login";
$descrizione = "Pagina per effettuare il login come utente dell'Associazione Tecweb Donatori Sangue";
$keywords = "login, utente, associazione, sangue, ATDS";

include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "header.php";
echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "login.html");
include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "footer.php";