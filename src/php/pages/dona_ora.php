<?php
require_once "../utility.php";
require_once "../db.php";

$paginaHTML = caricaTemplate('dona_ora.html');

$msgHTML = getMessaggioFlashHTML();
if (!empty($msgHTML)) {
    $paginaHTML = str_replace('<main id="content" class="main-standard">', '<main id="content" class="main-standard">' . $msgHTML, $paginaHTML);
}

if (!isset($_SESSION['user_id'])) {
    $messaggioAvviso = '
    <div class="text-standard">
        <h3 class="section-title">Accesso Richiesto</h3>
        <p>Per prenotare una donazione è necessario accedere alla propria area riservata.</p>
        <div class="action-container">
            <form action="login.php" method="get" class="form-inline">
                <div class="btn-wrapper">
                    <input type="hidden" name="redirect" value="dona_ora.php">
                    <button type="submit" class="btn-std">Accedi</button>
                </div>
            </form>
            <p class="text-separator">oppure</p>
            <form action="registrazione.php" method="get" class="form-inline">
                <div class="btn-wrapper"><button type="submit" class="btn-std">Registrati</button></div>
            </form>
        </div>
    </div>';
    $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioAvviso, $paginaHTML);

} else {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        $messaggioAdmin = '
        <div class="text-standard">
            <h3 class="section-title">Profilo Admin</h3>
            <p>Ciao ' . htmlspecialchars($_SESSION['username']) . '! Gli account amministratori non possono effettuare donazioni.</p>
            <div class="action-container-single">
                <form action="profilo.php" method="get" class="form-inline">
                    <div class="btn-wrapper"><button type="submit" class="btn-std">Torna al Profilo</button></div>
                </form>
            </div>
        </div>';
        $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioAdmin, $paginaHTML);
    } else {
        try {
            $stmtDonatore = $pdo->prepare("SELECT sesso FROM donatori WHERE user_id = ?");
            $stmtDonatore->execute([$_SESSION['user_id']]);
            $datiDonatore = $stmtDonatore->fetch();

            if (!$datiDonatore) {
                $messaggioIncompleto = '
                <div class="text-standard">
                    <h3 class="section-title">Profilo donatore incompleto</h3>
                    <p>Ciao ' . htmlspecialchars($_SESSION['username']) . '! Devi completare la registrazione dei dati sanitari prima di poter prenotare una donazione.</p>
                    <div class="action-container-single">
                        <form action="registrazione_donatore.php" method="get" class="form-inline">
                            <div class="btn-wrapper"><button type="submit" class="btn-std">Diventa Donatore</button></div>
                        </form>
                    </div>
                </div>';
                $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', $messaggioIncompleto, $paginaHTML);
            } else {

                // Widget prossima donazione disponibile
                $mesiAttesa = ($datiDonatore['sesso'] === 'Maschio') ? 3 : 6;

                $stmtTutte = $pdo->prepare("
                    SELECT data_prenotazione
                    FROM lista_prenotazioni
                    WHERE user_id = ?
                    ORDER BY data_prenotazione ASC
                ");
                $stmtTutte->execute([$_SESSION['user_id']]);
                $tutteLeDate = $stmtTutte->fetchAll(PDO::FETCH_COLUMN);

                $widgetHTML = '';

                if (!empty($tutteLeDate)) {
                    $oggi        = new DateTime();
                    $oggiISO     = $oggi->format('Y-m-d');

                    $prenotazioniFuture = array_values(array_filter($tutteLeDate, fn($d) => $d >= $oggiISO));
                    $ultimaInAssoluto   = new DateTime(end($tutteLeDate));
                    $dateOccupate       = array_map(fn($d) => new DateTime($d), $tutteLeDate);

                    $slotLiberi = [];
                    for ($i = 0; $i < count($dateOccupate) - 1; $i++) {
                        $fineBlocco             = (clone $dateOccupate[$i])->modify("+{$mesiAttesa} months");
                        $inizioBloccoSuccessivo = (clone $dateOccupate[$i + 1])->modify("-{$mesiAttesa} months");

                        if ($fineBlocco < $dateOccupate[$i + 1] && $fineBlocco <= $inizioBloccoSuccessivo) {
                            $slotDa = clone $fineBlocco;
                            $slotA  = clone $inizioBloccoSuccessivo;
                            if ($slotA >= $oggi) {
                                $slotLiberi[] = [
                                    'da_raw' => $slotDa,
                                    'a_raw'  => $slotA,
                                    'da'     => $slotDa->format('d.m.Y'),
                                    'a'      => $slotA->format('d.m.Y'),
                                ];
                            }
                        }
                    }

                    $slotFuturi          = array_values(array_filter($slotLiberi, fn($s) => $s['a_raw'] >= $oggi));
                    $dopoultimaData      = (clone $ultimaInAssoluto)->modify("+{$mesiAttesa} months");
                    $primaDisponibile    = $dopoultimaData->format('d.m.Y');
                    $primaDisponibileISO = $dopoultimaData->format('Y-m-d');

                    $widgetHTML = '<aside class="dona-sidebar" role="complementary">';
                    $widgetHTML .= '<div class="profile-card">';

                    if (!empty($prenotazioniFuture)) {
                        $widgetHTML .= '<h2 class="profile-card-title">Prenotazioni attive</h2>';
                        $widgetHTML .= '<dl class="data-list-compact">';
                        foreach ($prenotazioniFuture as $data) {
                            $widgetHTML .= '<div><dt>Data</dt><dd>' . (new DateTime($data))->format('d.m.Y') . '</dd></div>';
                        }
                        $widgetHTML .= '</dl>';

                        if (!empty($slotFuturi)) {
                            $widgetHTML .= '<h2 class="profile-card-title">Slot disponibili</h2>';
                            $widgetHTML .= '<dl class="data-list-compact">';
                            foreach ($slotFuturi as $slot) {
                                $da = $slot['da_raw'] < $oggi ? $oggi->format('d.m.Y') : $slot['da'];
                                $widgetHTML .= '<div><dt>Dal</dt><dd>' . $da . '</dd></div>';
                                $widgetHTML .= '<div><dt>Al</dt><dd>' . $slot['a'] . '</dd></div>';
                            }
                            $widgetHTML .= '</dl>';
                        }

                        $widgetHTML .= '<h2 class="profile-card-title">Prossima disponibilità</h2>';
                        $widgetHTML .= '<dl class="data-list-compact">';
                        $widgetHTML .= '<div><dt>Dal</dt><dd>' . $primaDisponibile . '</dd></div>';
                        $widgetHTML .= '</dl>';
                    } else {
                        if ($primaDisponibileISO <= $oggiISO) {
                            $widgetHTML .= '<h2 class="profile-card-title">Disponibile</h2>';
                            $widgetHTML .= '<dl class="data-list-compact">';
                            $widgetHTML .= '<div><dt>Stato</dt><dd>Puoi prenotare subito</dd></div>';
                            $widgetHTML .= '</dl>';
                        } else {
                            $widgetHTML .= '<h2 class="profile-card-title">Prossima disponibilità</h2>';
                            $widgetHTML .= '<dl class="data-list-compact">';
                            $widgetHTML .= '<div><dt>Dal</dt><dd>' . $primaDisponibile . '</dd></div>';
                            $widgetHTML .= '<div><dt>Attesa</dt><dd>' . $mesiAttesa . ' mesi dall\'ultima donazione del ' . $ultimaInAssoluto->format('d.m.Y') . '</dd></div>';
                            $widgetHTML .= '</dl>';
                        }
                    }

                 $widgetHTML .= '</div></aside>';

                $paginaHTML = str_replace(
                    '<form id="prenotaForm"',
                            '<div class="admin-layout-container">' . 
                            '<aside class="donor-info-sidebar">' . $widgetHTML . '</aside>' . 
                            '<div class="form-container-admin"><form id="prenotaForm"',
                            $paginaHTML
                );

                $paginaHTML = str_replace(
                    '</form>',
                    '</form></div></div>',
                    $paginaHTML
                );
                }
                // fine widget

                $stmtUltima = $pdo->prepare("SELECT MAX(data_prenotazione) FROM lista_prenotazioni WHERE user_id = ?");
                $stmtUltima->execute([$_SESSION['user_id']]);
                $ultimaData = $stmtUltima->fetchColumn() ?: '';

                $formConDati = '<form id="prenotaForm" data-ultima="' . $ultimaData . '" data-sesso="' . $datiDonatore['sesso'] . '" data-is-admin="false"';
                $paginaHTML  = str_replace('<form id="prenotaForm"', $formConDati, $paginaHTML);

                $stmtSedi = $pdo->query("SELECT id, nome FROM sedi ORDER BY nome ASC");
                $sedi     = $stmtSedi->fetchAll();

                $sedePre     = $_GET['sede_id'] ?? ($_SESSION['form_preservato']['sede_id'] ?? '');
                $optionsSedi = "";
                foreach ($sedi as $s) {
                    $sel          = ($s['id'] == $sedePre) ? 'selected' : '';
                    $optionsSedi .= '<option value="' . $s['id'] . '" ' . $sel . '>' . htmlspecialchars($s['nome']) . '</option>';
                }
                $paginaHTML = str_replace('[listaNomiSedi]', $optionsSedi, $paginaHTML);

                if (isset($_SESSION['form_preservato'])) {
                    $oraP       = $_SESSION['form_preservato']['ora'];
                    $tipoP      = $_SESSION['form_preservato']['tipo'];
                    $paginaHTML = str_replace('value="' . $oraP . '">', 'value="' . $oraP . '" selected>', $paginaHTML);
                    $paginaHTML = str_replace('name="donazione" value="' . $tipoP . '"', 'name="donazione" value="' . $tipoP . '" checked', $paginaHTML);
                    unset($_SESSION['form_preservato']);
                }
            }
        } catch (PDOException $e) {
            $paginaHTML = preg_replace('/<form id="prenotaForm".*?<\/form>/s', '<p class="text-standard">Errore caricamento dati.</p>', $paginaHTML);
        }
    }
}

$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Dona ora</span></p>';
echo costruisciPagina($paginaHTML, $breadcrumb, 'dona_ora.php');
?>