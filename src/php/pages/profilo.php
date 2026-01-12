<?php
require_once "../utility.php";
session_start();

// 1. Controllo sicurezza: se non è loggato, via al login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. Carico il template HTML
$paginaHTML = file_get_contents('../../html/profilo.html');

// 3. Gestione contenuto specifico della pagina
$nomeUtente = '<h1> Profilo di ' . $_SESSION['username'] . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Profilo</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, "profilo.php");
?>