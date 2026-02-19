<?php
require_once '../utility.php';
require_once '../db.php';

// Verifica che l'utente sia admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato');
}

try {
    // Query per ottenere tutti gli utenti con dati donatore (se esistono)
    $sql = "SELECT u.id, u.username, u.ruolo,
                   d.nome, d.cognome, d.email,
                   CASE WHEN d.user_id IS NOT NULL THEN 'Sì' ELSE 'No' END as e_donatore
            FROM utenti u
            LEFT JOIN donatori d ON u.id = d.user_id
            ORDER BY u.ruolo DESC, u.username ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    function generaTabellaUtenti($dati) {
        if (count($dati) === 0) {
            return '<p class="text-standard">Nessun utente trovato.</p>';
        }

        $html = '<div class="table-container">';
        $html .= '<table class="data-table" aria-describedby="descrizione-tabella-utenti">';
        $html .= '<caption id="descrizione-tabella-utenti" class="sr-only">Tabella di tutti gli utenti registrati nel sistema</caption>';
        $html .= '<thead><tr>
                    <th scope="col"><span lang="en">Username</span></th>
                    <th scope="col"><span lang="en">Email</span></th>
                    <th scope="col">Nome</th>
                    <th scope="col">Cognome</th>
                    <th scope="col">Iscritto come donatore</th>
                    <th scope="col">Ruolo</th>
                    <th scope="col">Azioni</th>
                  </tr></thead><tbody>';

        foreach ($dati as $u) {
            $username = htmlspecialchars($u['username']);
            $ruolo = htmlspecialchars($u['ruolo']);
            $isDonatore = $u['e_donatore'];
            
            // Email, nome e cognome vengono dalla tabella donatori (possono essere NULL)
            $email = !empty($u['email']) ? htmlspecialchars($u['email']) : '-';
            $nome = !empty($u['nome']) ? htmlspecialchars($u['nome']) : '-';
            $cognome = !empty($u['cognome']) ? htmlspecialchars($u['cognome']) : '-';

            // Mappatura ruoli per visualizzazione
            $ruoloDisplay = [
                'utente' => 'Utente',
                'donatore' => 'Donatore',
                'admin' => 'Admin'
            ];
            $ruoloMostrato = isset($ruoloDisplay[$ruolo]) ? $ruoloDisplay[$ruolo] : $ruolo;

            $html .= '<tr>';
            $html .= '<th scope="row" data-label="Username">' . $username . '</th>';
            $html .= '<td data-label="Email">' . $email . '</td>';
            $html .= '<td data-label="Nome">' . $nome . '</td>';
            $html .= '<td data-label="Cognome">' . $cognome . '</td>';
            $html .= '<td data-label="Iscritto come donatore" class="table-cell-centered">' . $isDonatore . '</td>';
            $html .= '<td data-label="Ruolo">' . $ruoloMostrato . '</td>';
            $html .= '<td data-label="Azioni">';
            
            $html .= '<button type="button" 
                        class="btn-table btn-modifica-ruolo" 
                        data-id-utente="' . $u['id'] . '" 
                        data-username="' . $username . '" 
                        data-ruolo="' . $ruolo . '"
                        aria-label="Modifica ruolo di ' . $username . '">MODIFICA RUOLO</button> ';

            $html .= '<button type="button" 
                        class="btn-table delete btn-elimina-utente" 
                        data-id-utente="' . $u['id'] . '" 
                        data-username="' . $username . '"
                        aria-label="Elimina utente ' . $username . '">ELIMINA</button>';
            
            $html .= '</td></tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    }

    echo generaTabellaUtenti($utenti);

} catch (PDOException $e) {
    logError("Errore get_utenti_admin: " . $e->getMessage());
    echo '<p class="text-standard msg-error">Errore durante il caricamento degli utenti. Riprova più tardi.</p>';
}
?>
