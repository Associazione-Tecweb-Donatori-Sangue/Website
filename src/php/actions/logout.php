<?php
require_once '../utility.php';

// 1. Cancella tutte le variabili di sessione
$_SESSION = array();

// 2. Distruggi la sessione sul server
session_destroy();

// 3. Reindirizza l'utente alla pagina di login (o alla home)
header("Location: ../pages/login.php");
exit();
?>