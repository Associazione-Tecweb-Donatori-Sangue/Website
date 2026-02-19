<?php
require_once "php/utility.php";

http_response_code(404);

// Carico il template HTML
$paginaHTML = file_get_contents('html/404.html');

// Definisco il breadcrumb per questa pagina
$breadcrumb = "";

// Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, '');
?>