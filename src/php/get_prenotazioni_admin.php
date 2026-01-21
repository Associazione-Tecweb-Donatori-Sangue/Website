<?php
require_once 'utility.php';
require_once 'db.php';

// Verifica che l'utente sia admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato');
}

$sede_filtro = isset($_GET['sede']) ? $_GET['sede'] : 'tutte';

try {
    // Query per prenotazioni future
    $sql = "SELECT p.id, u.username, p.data_prenotazione, p.ora_prenotazione, s.nome as nome_sede 
            FROM lista_prenotazioni p 
            JOIN utenti u ON p.user_id = u.id
            JOIN sedi s ON p.sede_id = s.id
            WHERE p.data_prenotazione >= CURDATE()";
    
    if ($sede_filtro !== 'tutte') {
        $sql .= " AND s.nome = :sede";
    }
    
    $sql .= " ORDER BY p.data_prenotazione ASC, p.ora_prenotazione ASC";
    
    $stmt = $pdo->prepare($sql);
    
    if ($sede_filtro !== 'tutte') {
        $stmt->bindParam(':sede', $sede_filtro);
    }
    
    $stmt->execute();
    $prenotazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($prenotazioni) > 0) {
        foreach ($prenotazioni as $prenotazione) {
            $dataIt = date("d/m/Y", strtotime($prenotazione['data_prenotazione']));
            $oraIt = substr($prenotazione['ora_prenotazione'], 0, 5);

            echo '<tr>';
            echo '<th scope="row">' . htmlspecialchars($prenotazione['username']) . '</th>';
            echo '<td>' . $dataIt . '</td>';
            echo '<td>' . $oraIt . '</td>';
            echo '<td>' . htmlspecialchars($prenotazione['nome_sede']) . '</td>';
            echo '<td class="celle_azioni">';
            // Tasto Modifica (rimasto invariato, per ora solo grafico)
            echo '<button type="button" class="btn_tabella btn_edit">Modifica</button>';
            
            // --- TASTO ELIMINA (Ora è un FORM funzionante) ---
            // Nota l'action "../cancellaPrenotazione.php": risale di un livello perché siamo dentro "pages/" virtualmente
            echo '<form action="../cancellaPrenotazione.php" method="POST" style="margin:0;" onsubmit="return confirm(\'Sei sicuro di voler eliminare questa prenotazione?\');">';
            echo '<input type="hidden" name="id_prenotazione" value="' . $prenotazione['id'] . '">';
            echo '<button type="submit" class="btn_tabella btn_delete" style="cursor: pointer;">Elimina</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5" style="text-align: center;">Nessuna prenotazione futura trovata</td></tr>';
    }
    
} catch (PDOException $e) {
    echo '<tr><td colspan="5" style="text-align: center;">Errore: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';}
?>