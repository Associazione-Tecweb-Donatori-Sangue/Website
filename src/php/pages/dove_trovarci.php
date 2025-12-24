<?php
$titolo = "ATDS - Dove trovarci";
$descrizione = "Pagina con la mappa che visualizza dove sono le sedi dell'associazione in cui è possibile donare il sangue";
$keywords = "sangue, mappa, donare, sedi, associazione, ATDS";

include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "header.php";
echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "dove_trovarci.html");
include __DIR__ . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "footer.php";