<?php
$host = 'db';
$db   = 'ggiora';
$user = 'ggiora';
$pass = 'Eith6isheixei3ko';

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   
    PDO::ATTR_EMULATE_PREPARES   => false,               
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $GLOBALS['pdo'] = $pdo;
} catch (\PDOException $e) {
    // Log dettagliato per il debug
    error_log("Errore connessione DB: " . $e->getMessage() . " - File: " . $e->getFile() . " - Line: " . $e->getLine());
    
    // Avvia sessione se non già avviata
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Messaggio generico per l'utente
    $_SESSION['errore_500'] = "Impossibile connettersi al database. Riprova più tardi.";
    
    // Redirect a pagina di errore 500
    header("Location: /500.php");
    exit();
}

return $pdo;
?>