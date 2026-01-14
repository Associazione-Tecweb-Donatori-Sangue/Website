<?php
session_start();
require_once 'db.php';

// Verifica che l'utente sia admin
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato');
}

$sede_filtro = isset($_GET['sede']) ? $_GET['sede'] : 'tutte';

try {
    // Query per prenotazioni future
    $sql = "SELECT id, user_id, username, data_prenotazione, ora_prenotazione, nome_sede 
            FROM lista_prenotazioni 
            WHERE data_prenotazione >= CURDATE()";
    
    if ($sede_filtro !== 'tutte') {
        $sql .= " AND nome_sede = :sede";
    }
    
    $sql .= " ORDER BY data_prenotazione, ora_prenotazione";
    
    $stmt = $pdo->prepare($sql);
    
    if ($sede_filtro !== 'tutte') {
        $stmt->bindParam(':sede', $sede_filtro);
    }
    
    $stmt->execute();
    $prenotazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($prenotazioni) > 0) {
        foreach ($prenotazioni as $prenotazione) {
            echo '<tr>';
            echo '<th scope="row">' . htmlspecialchars($prenotazione['username']) . '</th>';
            echo '<td>' . htmlspecialchars($prenotazione['data_prenotazione']) . '</td>';
            echo '<td>' . htmlspecialchars($prenotazione['ora_prenotazione']) . '</td>';
            echo '<td>' . htmlspecialchars($prenotazione['nome_sede']) . '</td>';
            echo '<td class="celle_azioni">';
            echo '<button type="button" class="btn_tabella btn_edit" data-id="' . $prenotazione['id'] . '" aria-label="Modifica prenotazione di ' . htmlspecialchars($prenotazione['username']) . '">Modifica</button>';
            echo '<button type="button" class="btn_tabella btn_delete" data-id="' . $prenotazione['id'] . '" aria-label="Elimina prenotazione di ' . htmlspecialchars($prenotazione['username']) . '">Elimina</button>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5" style="text-align: center;">Nessuna prenotazione futura trovata</td></tr>';
    }
    
} catch (PDOException $e) {
    echo '<tr><td colspan="5" style="text-align: center;">Errore: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';}
?>