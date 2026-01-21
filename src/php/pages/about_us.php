<?php
require_once "../utility.php";

$paginaHTML = caricaTemplate('about_us.html');

// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Chi siamo</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'about_us.php');
?>