<?php
require_once '../utility.php';
require_once '../db.php';

// Verifica che l'utente sia admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Se non è admin, blocco tutto
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
        echo '<div class="table-container">';
        echo '<table class="data-table" aria-describedby="tabella-donazioni-desc">';
        echo '<caption>Tabella delle donazioni prenotate</caption>';
        echo '<thead><tr>';
        echo '<th scope="col">Nome Donatore</th>';
        echo '<th scope="col">Data</th>';
        echo '<th scope="col">Ora</th>';
        echo '<th scope="col">Sede</th>';
        echo '<th scope="col">Azioni</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        foreach ($prenotazioni as $prenotazione) {
            $dataIt = date("d/m/Y", strtotime($prenotazione['data_prenotazione']));
            $oraIt = substr($prenotazione['ora_prenotazione'], 0, 5);

            echo '<tr>';
            echo '<th scope="row">' . htmlspecialchars($prenotazione['username']) . '</th>';
            echo '<td>' . $dataIt . '</td>';
            echo '<td>' . $oraIt . '</td>';
            echo '<td>' . htmlspecialchars($prenotazione['nome_sede']) . '</td>';
            echo '<td class="celle_azioni">';
            echo '<a href="modifica_prenotazione.php?id=' . $prenotazione['id'] . '" class="btn_tabella btn_edit btn-table-link">Modifica</a>';
            echo '<form action="../actions/cancellaPrenotazione.php" method="POST" class="form-inline-table" onsubmit="return confirm(\'Sei sicuro di voler eliminare questa prenotazione?\');">';
            echo '<input type="hidden" name="id_prenotazione" value="' . $prenotazione['id'] . '">';
            echo '<button type="submit" class="btn_tabella btn_delete btn-cursor-pointer">Elimina</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p class="text-standard testo-centered-message">Nessuna prenotazione in programma.</p>';
    }
    
} catch (PDOException $e) {
    echo '<p class="text-standard testo-centered-message">Errore nel caricamento dei dati.</p>';
}
?>