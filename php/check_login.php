<?php
session_start();

if (isset($_SESSION['username'])) {
    header("Location: prenota.html");
    exit;
} else {
    header("Location: login.html");
    exit;
}
?>
