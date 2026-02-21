<?php
ob_start();
require_once __DIR__ . "/../db.php"; 
ob_clean();

header('Content-Type: application/json');

$response = [
    'donazioni_mese' => 0,
    'donazioni_totali' => 0,
    'sede_top' => 'N/D',
    'sede_top_count' => 0,
    'gruppi' => [],
    'orario_top' => 'N/D',
    'orario_top_count' => 0
];

try {
    // 1. Donazioni questo mese
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM lista_prenotazioni 
        WHERE MONTH(data_prenotazione) = MONTH(CURRENT_DATE()) 
        AND YEAR(data_prenotazione) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute();
    $response['donazioni_mese'] = (int)$stmt->fetchColumn();

    // 2. Donazioni totali di sempre
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM lista_prenotazioni
        WHERE data_prenotazione <= CURRENT_DATE()
    ");
    $stmt->execute();
    $response['donazioni_totali'] = (int)$stmt->fetchColumn();

    // 3. Sede più frequentata
    $stmt = $pdo->prepare("
        SELECT s.nome, COUNT(*) as cnt 
        FROM lista_prenotazioni lp
        JOIN sedi s ON lp.sede_id = s.id
        GROUP BY lp.sede_id, s.nome 
        ORDER BY cnt DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $response['sede_top'] = $row['nome'];
        $response['sede_top_count'] = (int)$row['cnt'];
    }

    // 4. Distribuzione Gruppi Sanguigni
    $stmt = $pdo->prepare("
        SELECT gruppo_sanguigno, COUNT(*) as cnt 
        FROM donatori 
        WHERE gruppo_sanguigno IS NOT NULL 
        GROUP BY gruppo_sanguigno 
        ORDER BY cnt DESC
    ");
    $stmt->execute();
    $gruppi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_users = array_sum(array_column($gruppi, 'cnt'));
    
    foreach ($gruppi as $gruppo) {
        $response['gruppi'][] = [
            'label'   => $gruppo['gruppo_sanguigno'],
            'count'   => (int)$gruppo['cnt'],
            'percent' => $total_users > 0 ? round(($gruppo['cnt'] / $total_users) * 100) : 0
        ];
    }

    // 5. Orario di maggiore affluenza
    $stmt = $pdo->prepare("
        SELECT ora_prenotazione, COUNT(*) as cnt
        FROM lista_prenotazioni
        GROUP BY ora_prenotazione
        ORDER BY cnt DESC
        LIMIT 1
    ");
    $stmt->execute();
    $orario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($orario) {
        $response['orario_top'] = $orario['ora_prenotazione'];
        $response['orario_top_count'] = (int)$orario['cnt'];
    }

    echo json_encode($response);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}
?>