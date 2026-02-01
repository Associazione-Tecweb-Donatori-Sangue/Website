<?php
require_once '../utility.php';
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_GET['sede_id']) || !isset($_GET['data'])) {
    echo json_encode(['error' => 'Parametri mancanti']);
    exit();
}

$sede_id = $_GET['sede_id'];
$data = $_GET['data'];

// Validazioni
if (!validaInteroPositivo($sede_id)) {
    echo json_encode(['error' => 'Sede non valida']);
    exit();
}

if (!validaData($data)) {
    echo json_encode(['error' => 'Data non valida']);
    exit();
}

try {
    // Recupera il conteggio per ogni fascia oraria
    $stmt = $pdo->prepare(
        "SELECT ora_prenotazione, COUNT(*) as prenotazioni 
         FROM lista_prenotazioni 
         WHERE sede_id = ? AND data_prenotazione = ? 
         GROUP BY ora_prenotazione"
    );
    $stmt->execute([$sede_id, $data]);
    
    $orari_occupati = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['prenotazioni'] >= 2) {
            $orari_occupati[] = $row['ora_prenotazione'];
        }
    }
    
    echo json_encode(['orari_pieni' => $orari_occupati]);
    
} catch (PDOException $e) {
    logError("Errore get_orari_disponibili: " . $e->getMessage());
    echo json_encode(['error' => 'Errore durante il recupero degli orari']);
}
?>