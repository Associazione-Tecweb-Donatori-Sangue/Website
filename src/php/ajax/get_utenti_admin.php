<?php
require_once '../utility.php';
require_once '../db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato');
}

try {
    $filtro    = $_GET['filtro']    ?? 'tutti';
    $ordine    = $_GET['ordine']    ?? 'cognome';
    $direzione = strtoupper($_GET['direzione'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

    $ordineColonna = match($ordine) {
        'data_nascita' => 'd.data_nascita',
        'entrambi'     => 'd.cognome, d.data_nascita',
        default        => 'd.cognome',
    };

    $whereClause = "WHERE u.ruolo != 'admin'";
    $params = [];

    if ($filtro === 'donatori') {
        $whereClause .= " AND d.user_id IS NOT NULL";
    } elseif ($filtro === 'maschio') {
        $whereClause .= " AND d.sesso = :sesso";
        $params[':sesso'] = 'Maschio';
    } elseif ($filtro === 'femmina') {
        $whereClause .= " AND d.sesso = :sesso";
        $params[':sesso'] = 'Femmina';
    } elseif ($filtro !== 'tutti') {
        $whereClause .= " AND d.gruppo_sanguigno = :gruppo";
        $params[':gruppo'] = $filtro;
    }

    $sql = "SELECT u.id, u.username,
                   d.nome, d.cognome, d.email, d.gruppo_sanguigno, d.data_nascita, d.sesso,
                   CASE WHEN d.user_id IS NOT NULL THEN 'Sì' ELSE 'No' END as e_donatore
            FROM utenti u
            LEFT JOIN donatori d ON u.id = d.user_id
            {$whereClause}
            ORDER BY {$ordineColonna} {$direzione}, u.username ASC";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($utenti) === 0) {
        echo '<p class="text-standard">Nessun utente trovato.</p>';
        exit;
    }

    function thOrdinabile($label, $campo, $ordineAttivo, $direzioneAttiva) {
        $isAttivo       = ($ordineAttivo === $campo);
        $nuovaDirezione = ($isAttivo && $direzioneAttiva === 'ASC') ? 'DESC' : 'ASC';
        $icona          = $isAttivo ? ($direzioneAttiva === 'ASC' ? '▲' : '▼') : '▲▼';
        $ariaSort       = $isAttivo ? ($direzioneAttiva === 'ASC' ? 'ascending' : 'descending') : 'none';
        $ariaLabel      = 'Ordina per ' . $label . ($isAttivo ? ($direzioneAttiva === 'ASC' ? ', ordine crescente attivo' : ', ordine decrescente attivo') : ', nessun ordine attivo');

        return '<th scope="col" aria-sort="' . $ariaSort . '">
                    <button class="th-sort-btn"
                            data-ordine="' . $campo . '"
                            data-direzione="' . $nuovaDirezione . '"
                            aria-label="' . $ariaLabel . '">
                        ' . $label . ' <span aria-hidden="true">' . $icona . '</span>
                    </button>
                </th>';
    }

    $html  = '<div class="table-container">';
    $html .= '<table class="data-table" aria-describedby="descrizione-tabella-utenti">';
    $html .= '<caption id="descrizione-tabella-utenti" class="sr-only">Tabella di tutti gli utenti registrati nel sistema</caption>';
    $html .= '<thead><tr>';
    $html .= '<th scope="col"><span lang="en">Username</span></th>';
    $html .= '<th scope="col"><abbr title="Iscritto come donatore">Don.</abbr></th>';
    $html .= '<th scope="col">Nome</th>';
    $html .= thOrdinabile('Cognome', 'cognome', $ordine, $direzione);
    $html .= '<th scope="col"><span lang="en">Email</span></th>';
    $html .= '<th scope="col">Sesso</th>';
    $html .= '<th scope="col"><abbr title="Gruppo sanguigno">Gr. sang.</abbr></th>';
    $html .= thOrdinabile('Nascita', 'data_nascita', $ordine, $direzione);
    $html .= '<th scope="col">Azioni</th>';
    $html .= '</tr></thead><tbody>';

    foreach ($utenti as $u) {
        $username        = htmlspecialchars($u['username']);
        $isDonatore      = $u['e_donatore'];
        $nome            = !empty($u['nome'])             ? htmlspecialchars($u['nome'])             : '-';
        $cognome         = !empty($u['cognome'])          ? htmlspecialchars($u['cognome'])          : '-';
        $email           = !empty($u['email'])            ? htmlspecialchars($u['email'])            : '-';
        $sesso           = !empty($u['sesso'])            ? htmlspecialchars($u['sesso'])            : '-';
        $gruppoSanguigno = !empty($u['gruppo_sanguigno']) ? htmlspecialchars($u['gruppo_sanguigno']) : '-';
        $dataNascita     = !empty($u['data_nascita'])
            ? (new DateTime($u['data_nascita']))->format('d.m.Y')
            : '-';

        $html .= '<tr>';
        $html .= '<th scope="row" data-label="Username">'                           . $username        . '</th>';
        $html .= '<td data-label="Donatore" class="table-cell-centered">'           . $isDonatore      . '</td>';
        $html .= '<td data-label="Nome">'                                            . $nome            . '</td>';
        $html .= '<td data-label="Cognome">'                                         . $cognome         . '</td>';
        $html .= '<td data-label="Email">'                                           . $email           . '</td>';
        $html .= '<td data-label="Sesso">'                                           . $sesso           . '</td>';
        $html .= '<td data-label="Gruppo sanguigno">'                                . $gruppoSanguigno . '</td>';
        $html .= '<td data-label="Data di nascita">'                                 . $dataNascita     . '</td>';
        $html .= '<td data-label="Azioni">';
        $html .= '<button type="button"
                    class="btn-table delete btn-elimina-utente"
                    data-id-utente="' . $u['id'] . '"
                    data-username="'  . $username . '"
                    aria-label="Elimina utente ' . $username . '">ELIMINA</button>';
        $html .= '</td></tr>';
    }

    $html .= '</tbody></table></div>';
    echo $html;

} catch (PDOException $e) {
    logError("Errore get_utenti_admin: " . $e->getMessage());
    echo '<p class="text-standard msg-error">Errore durante il caricamento degli utenti. Riprova più tardi.</p>';
}
?>