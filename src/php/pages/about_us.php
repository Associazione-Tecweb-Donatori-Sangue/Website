<?php
require_once "../utility.php";

$paginaHTML = caricaTemplate('about_us.html');

$breadcrumb = '<p><a href="/ggiora/src/index.php" lang="en">Home</a> / <span>Chi siamo</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, 'about_us.php');
?>
