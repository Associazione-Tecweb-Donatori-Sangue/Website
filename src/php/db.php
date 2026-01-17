<?php
$host = 'db';
$db   = 'miodb';
$user = 'studente';
$pass = 'pass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   
    PDO::ATTR_EMULATE_PREPARES   => false,               
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $GLOBALS['pdo'] = $pdo; // AGGIUNGI QUESTA RIGA
} catch (\PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}

return $pdo; // AGGIUNGI QUESTA RIGA
?>