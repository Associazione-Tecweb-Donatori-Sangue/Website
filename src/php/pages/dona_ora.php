<?php
require_once "../utility.php";
session_start();

$paginaHTML = file_get_contents('../../html/dona_ora.html');

// 2. Definisco il breadcrumb per questa pagina
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Dona ora</span></p>';

// 3. Costruisco e stampo la pagina finale
echo costruisciPagina($paginaHTML, $breadcrumb, 'dona_ora.php');
?>