<?php
require_once '../utility.php';
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_GET['sede_id'])) {
    echo json_encode(['error' => 'Sede non specificata']);
    exit();
}

$sede_id = $_GET['sede_id'];
$oggi = date('Y-m-d');

try {
    // Trova tutti i giorni futuri con prenotazioni per questa sede
    $stmt = $pdo->prepare(
        "SELECT data_prenotazione, COUNT(*) as totale_prenotazioni
         FROM lista_prenotazioni
         WHERE sede_id = ? 
         AND data_prenotazione >= ?
         GROUP BY data_prenotazione"
    );
    $stmt->execute([$sede_id, $oggi]);
    
    $giorniPieni = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ci sono 16 fasce orarie disponibili (07:30 - 11:30 e 14:00 - 18:00, ogni 30 min)
        // Se ci sono 32 prenotazioni (16 * 2), il giorno è pieno (si conta che ogni mezz'ora possono prenotare 2 persone massimo)
        if ($row['totale_prenotazioni'] >= 32) {
            $giorniPieni[] = $row['data_prenotazione'];
        }
    }
    
    echo json_encode(['giorni_pieni' => $giorniPieni]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>