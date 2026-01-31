<?php
require_once '../utility.php';
require_once '../db.php';

// Verifica che l'utente sia admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato');
}

$sede_filtro = isset($_GET['sede']) ? $_GET['sede'] : 'tutte';

try {
    // 1. QUERY PRENOTAZIONI FUTURE (>= OGGI)
    $sqlFuture = "SELECT p.id, u.username, p.data_prenotazione, p.ora_prenotazione, s.nome as nome_sede 
                  FROM lista_prenotazioni p 
                  JOIN utenti u ON p.user_id = u.id
                  JOIN sedi s ON p.sede_id = s.id
                  WHERE p.data_prenotazione >= CURDATE()";
    
    // 2. QUERY STORICO (< OGGI)
    $sqlStorico = "SELECT p.id, u.username, p.data_prenotazione, p.ora_prenotazione, s.nome as nome_sede 
                   FROM lista_prenotazioni p 
                   JOIN utenti u ON p.user_id = u.id
                   JOIN sedi s ON p.sede_id = s.id
                   WHERE p.data_prenotazione < CURDATE()";

    if ($sede_filtro !== 'tutte') {
        $sqlFuture .= " AND s.nome = :sede";
        $sqlStorico .= " AND s.nome = :sede";
    }

    $sqlFuture .= " ORDER BY p.data_prenotazione ASC, p.ora_prenotazione ASC";
    $sqlStorico .= " ORDER BY p.data_prenotazione DESC";

    $stmtF = $pdo->prepare($sqlFuture);
    if ($sede_filtro !== 'tutte') $stmtF->bindParam(':sede', $sede_filtro);
    $stmtF->execute();
    $future = $stmtF->fetchAll(PDO::FETCH_ASSOC);

    $stmtS = $pdo->prepare($sqlStorico);
    if ($sede_filtro !== 'tutte') $stmtS->bindParam(':sede', $sede_filtro);
    $stmtS->execute();
    $passate = $stmtS->fetchAll(PDO::FETCH_ASSOC);

   
    function generaTabellaAdmin($dati, $isStorico = false, $idDescrizione = '') {
        if (count($dati) === 0) {
            return '<p class="text-standard">Nessuna prenotazione trovata.</p>';
        }

        $ariaAttribute = !empty($idDescrizione) ? ' aria-describedby="' . $idDescrizione . '"' : '';

        $html = '<div class="table-container">';
        $html .= '<table class="data-table"' . $ariaAttribute . '>';
        $html .= '<thead><tr>
                    <th scope="col">Username donatore</th>
                    <th scope="col">Data</th>
                    <th scope="col">Ora</th>
                    <th scope="col">Sede</th>
                    <th scope="col">Azioni</th>
                  </tr></thead><tbody>';

        foreach ($dati as $p) {
            $dataIt = date("d/m/Y", strtotime($p['data_prenotazione']));
            $oraIt = substr($p['ora_prenotazione'], 0, 5);
            $user = htmlspecialchars($p['username']);

            $html .= '<tr>';
            $html .= '<th scope="row" data-label="Username">' . $user . '</th>';
            $html .= '<td data-label="Data">' . $dataIt . '</td>';
            $html .= '<td data-label="Ora">' . $oraIt . '</td>';
            $html .= '<td data-label="Sede">' . htmlspecialchars($p['nome_sede']) . '</td>';
            $html .= '<td data-label="Azioni">';
            
            if ($isStorico) {
                $html .= '<span class="status-completed">Completata</span>';
            } else {
                $html .= '<a href="modifica_prenotazione.php?id_prenotazione=' . $p['id'] . '" 
                            class="btn-table" 
                            aria-label="Modifica prenotazione di ' . $user . '">MODIFICA</a> ';

                $html .= '<button type="button" class="btn-table delete btn-elimina-prenotazione-admin" 
                            data-id-prenotazione="' . $p['id'] . '" 
                            data-username="' . $user . '" 
                            data-data="' . $dataIt . '" 
                            data-ora="' . $oraIt . '"
                            aria-label="Elimina prenotazione di ' . $user . '">ELIMINA</button>';
            }
            
            $html .= '</td></tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    }

    echo '<h3 id="titolo-future" class="tertiary-title">Prenotazioni in programma</h3>';
    echo generaTabellaAdmin($future, false, 'titolo-future');

     echo '<div class="spacer-admin" aria-hidden="true"></div>';

    echo '<h3 id="titolo-storico" class="tertiary-title">Storico donazioni passate (Tutti gli utenti)</h3>';
    echo generaTabellaAdmin($passate, true, 'titolo-storico');

} catch (PDOException $e) {
    echo '<p class="text-standard">Errore nel caricamento dei dati.</p>';
}
?>
