<?php
$titolo = "ATDS - Profilo Admin";
$descrizione = "Pagina per visualizzare il profilo dell'admin dell'Associazione Tecweb Donatori Sangue";
$keywords = "profilo, admin, associazione, sangue, ATDS";

include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "header.php";
echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "profilo_admin.html");
include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "footer.php";