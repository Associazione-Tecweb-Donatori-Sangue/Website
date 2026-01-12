<?php
require_once "../utility.php";
session_start();

// 1. CONTROLLO SICUREZZA: L'utente è loggato ED è admin?
// Se non è admin, lo rimando al login
if (!isset($_SESSION['username']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

// 2. Carico il template HTML
$paginaHTML = file_get_contents('../../html/profilo_admin.html');

// 3. Gestione breadcrumb
$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Profilo Admin</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, "profilo_admin.php");
?>