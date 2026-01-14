<?php
session_start();
require_once 'db.php';

// Verifica che l'utente sia loggato
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato');
}

$username = $_SESSION['username'];
$sede_filtro = isset($_GET['sede']) ? $_GET['sede'] : 'tutte';

try {
    // Query per prenotazioni future dell'utente specifico
    $sql = "SELECT id, data_prenotazione, ora_prenotazione, nome_sede 
            FROM lista_prenotazioni 
            WHERE username = :username 
            AND data_prenotazione >= CURDATE()";
    
    if ($sede_filtro !== 'tutte') {
        $sql .= " AND nome_sede = :sede";
    }
    
    $sql .= " ORDER BY data_prenotazione, ora_prenotazione";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username);
    
    if ($sede_filtro !== 'tutte') {
        $stmt->bindParam(':sede', $sede_filtro);
    }
    
    $stmt->execute();
    $prenotazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Genera HTML della tabella
    if (count($prenotazioni) > 0) {
        foreach ($prenotazioni as $prenotazione) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($prenotazione['data_prenotazione']) . '</td>';
            echo '<td>' . htmlspecialchars($prenotazione['ora_prenotazione']) . '</td>';
            echo '<td>' . htmlspecialchars($prenotazione['nome_sede']) . '</td>';
            echo '<td><button type="button" class="link_azione" data-id="' . $prenotazione['id'] . '">Annulla</button></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4" style="text-align: center;">Non hai prenotazioni future</td></tr>';
    }
    
} catch (PDOException $e) {
    echo '<tr><td colspan="4" style="text-align: center;">Errore: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';}
?>