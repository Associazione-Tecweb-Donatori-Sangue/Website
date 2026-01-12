<?php
require_once "../utility.php";
session_start();

$paginaHTML = file_get_contents('../../html/dove_trovarci.html');

// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Dove trovarci</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'dove_trovarci.php');
?>

