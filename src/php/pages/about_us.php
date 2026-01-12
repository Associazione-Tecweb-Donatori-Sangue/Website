<?php
require_once "../utility.php";
session_start();

$paginaHTML = file_get_contents('../../html/about_us.html');

// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Chi siamo</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'about_us.php');
?>