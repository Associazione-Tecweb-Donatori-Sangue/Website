<?php
require_once "php/utility.php";

// Carico il template HTML
$paginaHTML = file_get_contents('html/index.html');

// Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><span lang="en">Home</span></p>';

// Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'index.php');
?>
