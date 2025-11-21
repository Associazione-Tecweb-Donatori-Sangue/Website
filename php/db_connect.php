<?php
$host = "localhost";    
$user = "root";       
$pass = "";
$dbname = "donatori_db";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>
