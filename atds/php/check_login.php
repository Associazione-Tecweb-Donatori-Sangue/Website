<?php
session_start();

$isFetch = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isFetch) {
header('Content-Type: application/json');
if (isset($_SESSION['username'])) {
echo json_encode(["logged_in" => true, "username" => $_SESSION['username']]);
} else {
echo json_encode(["logged_in" => false]);
}
     exit; 
} else {
if (!isset($_SESSION['username'])) { 
header("Location: ../html/login.html"); 
exit;
}
}