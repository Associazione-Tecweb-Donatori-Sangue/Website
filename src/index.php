<?php
require_once "php/utility.php";

// 1. Carico il template HTML
$paginaHTML = file_get_contents('html/index.html');

// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><span lang="en">Home</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'index.php');
?>