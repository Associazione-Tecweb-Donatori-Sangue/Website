<?php
require_once '../utility.php';
require_once '../db.php';

// Verifica che l'utente sia loggato
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato');
}

$userId = $_SESSION['user_id'];
$sede_filtro = isset($_GET['sede']) ? $_GET['sede'] : 'tutte';

try {
    // Query per prenotazioni future dell'utente specifico
    $sql = "SELECT p.id, p.data_prenotazione, p.ora_prenotazione, s.nome as nome_sede 
            FROM lista_prenotazioni p 
            JOIN sedi s ON p.sede_id = s.id 
            WHERE p.user_id = :user_id 
            AND p.data_prenotazione >= CURDATE()";

    if ($sede_filtro !== 'tutte') {
        $sql .= " AND s.nome = :sede";
    }
    
    $sql .= " ORDER BY p.data_prenotazione ASC, p.ora_prenotazione ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    
    if ($sede_filtro !== 'tutte') {
        $stmt->bindParam(':sede', $sede_filtro);
    }
    
    $stmt->execute();
    $prenotazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Genera HTML della tabella
    if (count($prenotazioni) > 0) {
        foreach ($prenotazioni as $prenotazione) {
            $dataIt = date("d/m/Y", strtotime($prenotazione['data_prenotazione']));
            $oraIt = substr($prenotazione['ora_prenotazione'], 0, 5);

            echo '<tr>';
            echo '<td>' . $dataIt . '</td>';
            echo '<td>' . $oraIt . '</td>';
            echo '<td>' . htmlspecialchars($prenotazione['nome_sede']) . '</td>';
            echo '<td><button type="button" class="link_azione" onclick="alert(\'Funzionalità annulla da implementare\')">Annulla</button></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4" style="text-align: center;">Non hai prenotazioni future</td></tr>';
    }
    
} catch (PDOException $e) {
    echo '<tr><td colspan="4" style="text-align: center;">Errore: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';}
?>