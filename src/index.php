<?php
// Credenziali definite nel compose.yaml
$host = 'db'; // Il nome del servizio nel file yaml
$user = 'studente';
$pass = 'pass';
$db   = 'miodb';

try {
    // Tentativo di connessione con PDO (più moderno e sicuro di mysqli)
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    
    // Imposta la modalità di errore
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>✅ Tutto funziona!</h1>";
    echo "<p>PHP 8.4 è attivo e connesso al database MariaDB 11.</p>";
    echo "<p>Versione Server: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    
} catch (PDOException $e) {
    echo "<h1>❌ C'è un problema...</h1>";
    echo "<p>Errore di connessione: " . $e->getMessage() . "</p>";
}
?>