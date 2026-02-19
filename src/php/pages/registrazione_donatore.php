<?php
require_once "../utility.php";
require_once "../db.php";

// Sicurezza
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function validaCodiceFiscale($cf) {
    $cf = strtoupper(trim($cf));
    
    if (strlen($cf) != 16) {
        return false;
    }
    
    if (!preg_match('/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/', $cf)) {
        return false;
    }
    
    $valoriDispari = [
        '0' => 1, '1' => 0, '2' => 5, '3' => 7, '4' => 9, '5' => 13,
        '6' => 15, '7' => 17, '8' => 19, '9' => 21,
        'A' => 1, 'B' => 0, 'C' => 5, 'D' => 7, 'E' => 9, 'F' => 13,
        'G' => 15, 'H' => 17, 'I' => 19, 'J' => 21, 'K' => 2, 'L' => 4,
        'M' => 18, 'N' => 20, 'O' => 11, 'P' => 3, 'Q' => 6, 'R' => 8,
        'S' => 12, 'T' => 14, 'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25,
        'Y' => 24, 'Z' => 23
    ];
    
    $valoriPari = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5,
        '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5,
        'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11,
        'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17,
        'S' => 18, 'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
        'Y' => 24, 'Z' => 25
    ];
    
    $caratteriControllo = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $somma = 0;
    
    for ($i = 0; $i < 15; $i++) {
        $char = $cf[$i];
        if ($i % 2 == 0) {
            $somma += $valoriDispari[$char];
        } else {
            $somma += $valoriPari[$char];
        }
    }
    
    $resto = $somma % 26;
    $carattereAtteso = $caratteriControllo[$resto];
    
    return ($cf[15] === $carattereAtteso);
}

function validaCoerenzaCF($cf, $nome, $cognome, $dataNascita, $sesso) {
    $cf = strtoupper(trim($cf));
    $nome = strtoupper(trim($nome));
    $cognome = strtoupper(trim($cognome));
    
    // ========================================
    // CONTROLLO LUNGHEZZA
    // ========================================
    if (strlen($cf) != 16) {
        return ['valido' => false, 'errore' => 'il codice fiscale deve essere lungo esattamente 16 caratteri, attualmente ne hai inseriti ' . strlen($cf) . '.'];
    }

    if (!preg_match('/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/', $cf)) {
        return ['valido' => false, 'errore' => 'Il formato del codice fiscale non è corretto. Deve contenere 6 lettere, 2 numeri, 1 lettera, 2 numeri, 1 lettera, 3 numeri e 1 lettera finale.'];
    }
    
    function estraiConsonanti($stringa) {
        return preg_replace('/[AEIOU]/i', '', $stringa);
    }
    
    function estraiVocali($stringa) {
        return preg_replace('/[^AEIOU]/i', '', $stringa);
    }
    
    // ========================================
    // CONTROLLO COGNOME
    // ========================================
    $cognomeCF = substr($cf, 0, 3);
    $consonantiCognome = estraiConsonanti($cognome);
    $vocaliCognome = estraiVocali($cognome);
    $cognomeAtteso = substr($consonantiCognome . $vocaliCognome . 'XXX', 0, 3);
    
    if ($cognomeCF !== $cognomeAtteso) {
        return ['valido' => false, 'errore' => "le prime 3 lettere del codice fiscale (cognome) non corrispondono, hai inserito '$cognomeCF' (posizioni 1-3) ma dal cognome '$cognome' dovrebbero essere '$cognomeAtteso'."];
    }
    
    // ========================================
    // CONTROLLO NOME
    // ========================================
    $nomeCF = substr($cf, 3, 3);
    $consonantiNome = estraiConsonanti($nome);
    $vocaliNome = estraiVocali($nome);
    
    if (strlen($consonantiNome) >= 4) {
        $nomeAtteso = $consonantiNome[0] . $consonantiNome[2] . $consonantiNome[3];
    } else {
        $nomeAtteso = substr($consonantiNome . $vocaliNome . 'XXX', 0, 3);
    }
    
    if ($nomeCF !== $nomeAtteso) {
        return ['valido' => false, 'errore' => "i caratteri 4-6 del codice fiscale (nome) non corrispondono, hai inserito '$nomeCF' (posizioni 4-6) ma dal nome '$nome' dovrebbero essere '$nomeAtteso'."];
    }
    
    // ========================================
    // CONTROLLO ANNO
    // ========================================
    $annoCF = substr($cf, 6, 2);
    $annoNascita = date('y', strtotime($dataNascita));
    
    if ($annoCF != $annoNascita) {
        return ['valido' => false, 'errore' => "l'anno di nascita del codice fiscale non corrisponde, il codice fiscale riporta '$annoCF' (posizioni 7-8) ma la tua data di nascita indica '$annoNascita'."];
    }
    
    // ========================================
    // CONTROLLO MESE
    // ========================================
    $meseCF = substr($cf, 8, 1);
    $mesiCF = [
        'A' => '01', 'B' => '02', 'C' => '03', 'D' => '04', 'E' => '05', 'H' => '06',
        'L' => '07', 'M' => '08', 'P' => '09', 'R' => '10', 'S' => '11', 'T' => '12'
    ];
    
    $meseNascita = date('m', strtotime($dataNascita));
    $meseAtteso = array_search($meseNascita, $mesiCF);
    
    if (!isset($mesiCF[$meseCF]) || $mesiCF[$meseCF] != $meseNascita) {
        $nomiMesi = [
            '01' => 'gennaio', '02' => 'febbraio', '03' => 'marzo', '04' => 'aprile',
            '05' => 'maggio', '06' => 'giugno', '07' => 'luglio', '08' => 'agosto',
            '09' => 'settembre', '10' => 'ottobre', '11' => 'novembre', '12' => 'dicembre'
        ];

        $natoNata = ($sesso == 'Femmina') ? 'nata' : 'nato';
        
        return ['valido' => false, 'errore' => "il mese di nascita del codice fiscale non corrisponde, hai inserito '$meseCF' (posizione 9) ma sei $natoNata a " . $nomiMesi[$meseNascita] . ", quindi dovrebbe essere '$meseAtteso'."];
    }
    
    // ========================================
    // CONTROLLO GIORNO
    // ========================================
    $giornoCF = intval(substr($cf, 9, 2));
    $giornoNascita = intval(date('d', strtotime($dataNascita)));
    
    if ($sesso == 'Femmina') {
        $giornoAtteso = $giornoNascita + 40;
    } else {
        $giornoAtteso = $giornoNascita;
    }
    
    if ($giornoCF != $giornoAtteso) {
        $giornoAttesoStr = str_pad($giornoAtteso, 2, '0', STR_PAD_LEFT);
        $giornoCFStr = str_pad($giornoCF, 2, '0', STR_PAD_LEFT);
        
        if ($sesso == 'Femmina') {
            return ['valido' => false, 'errore' => "il giorno di nascita del codice fiscale non corrisponde, hai inserito '$giornoCFStr' (posizioni 10-11) ma essendo donna e nata il giorno $giornoNascita, dovrebbe essere '$giornoAttesoStr' (giorno + 40)."];
        } else {
            return ['valido' => false, 'errore' => "il giorno di nascita del codice fiscale non corrisponde, hai inserito '$giornoCFStr' (posizioni 10-11) ma essendo uomo e nato il giorno $giornoNascita, dovrebbe essere '$giornoAttesoStr'."];
        }
    }
    
    // ========================================
    // CONTROLLO CHECKSUM
    // ========================================
    if (!validaCodiceFiscale($cf)) {
        return ['valido' => false, 'errore' => 'il carattere di controllo del codice fiscale non è valido, tutti gli altri dati (nome, cognome, data) sono corretti ma probabilmente hai sbagliato a digitare l\'ultima lettera.'];
    }
    
    return ['valido' => true, 'errore' => ''];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $dataNascita = new DateTime($_POST['data_nascita']);
    $oggi = new DateTime();
    $eta = $oggi->diff($dataNascita)->y;

    if ($eta < 18 || $eta > 60) {
        $_SESSION['messaggio_flash'] = "Errore: Devi avere almeno 18 anni e non più di 60 anni per registrarti come donatore.";
        $_SESSION['dati_inseriti'] = $_POST; // <--- SALVO I DATI
        header("Location: registrazione_donatore.php");
        exit();
    }


    $peso = floatval($_POST['peso_corporeo_in_kg']);
    if ($peso < 50) {
        $_SESSION['messaggio_flash'] = "Errore: Il peso minimo per donare è 50 Kg.";
        $_SESSION['dati_inseriti'] = $_POST; // <--- SALVO I DATI
        header("Location: registrazione_donatore.php");
        exit();
    }

    // VALIDAZIONE TELEFONO
    $risultatoTelefono = validaTelefono($_POST['telefono']);
    if (!$risultatoTelefono['valido']) {
        $_SESSION['messaggio_flash'] = "Errore: " . $risultatoTelefono['errore'] . ".";
        $_SESSION['dati_inseriti'] = $_POST;
        header("Location: registrazione_donatore.php");
        exit();
    }

    // VALIDAZIONE CODICE FISCALE
    $risultatoValidazione = validaCoerenzaCF(
        $_POST['codice_fiscale'],
        $_POST['nome'],
        $_POST['cognome'],
        $_POST['data_nascita'],
        $_POST['sesso']
    );
    
    if (!$risultatoValidazione['valido']) {
        $_SESSION['messaggio_flash'] = "Errore: " . $risultatoValidazione['errore'];
        $_SESSION['dati_inseriti'] = $_POST;
        header("Location: registrazione_donatore.php");
        exit();
    }

    try {
        $checkStmt = $pdo->prepare("SELECT user_id FROM donatori WHERE user_id = ?");
        $checkStmt->execute([$_SESSION['user_id']]);
        $esiste = $checkStmt->fetch();

        if ($esiste) {
            $sql = "UPDATE donatori SET nome=?, cognome=?, data_nascita=?, luogo_nascita=?, codice_fiscale=?, indirizzo=?, telefono=?, email=?, gruppo_sanguigno=?, sesso=?, peso=? WHERE user_id=?";
            $params = [
                pulisciInput($_POST['nome']),
                pulisciInput($_POST['cognome']),
                $_POST['data_nascita'],
                pulisciInput($_POST['luogo_nascita']),
                pulisciInput($_POST['codice_fiscale']),
                pulisciInput($_POST['residenza']),
                pulisciInput($_POST['telefono']),
                pulisciInput($_POST['email']),
                $_POST['gruppo_sanguigno'],
                $_POST['sesso'],
                $_POST['peso_corporeo_in_kg'],
                $_SESSION['user_id']
            ];
        } else {
            $sql = "INSERT INTO donatori (nome, cognome, data_nascita, luogo_nascita, codice_fiscale, indirizzo, telefono, email, gruppo_sanguigno, sesso, peso, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                pulisciInput($_POST['nome']),
                pulisciInput($_POST['cognome']),
                $_POST['data_nascita'],
                pulisciInput($_POST['luogo_nascita']),
                pulisciInput($_POST['codice_fiscale']),
                pulisciInput($_POST['residenza']),
                pulisciInput($_POST['telefono']),
                pulisciInput($_POST['email']),
                $_POST['gruppo_sanguigno'],
                $_POST['sesso'],
                $_POST['peso_corporeo_in_kg'],
                $_SESSION['user_id']
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['messaggio_flash'] = "Dati salvati correttamente!";
        header("Location: profilo.php");
        exit();

    } catch (PDOException $e) {
        logError("Errore salvataggio donatore: " . $e->getMessage());
      
        if ($e->getCode() == 23000) {
            $_SESSION['messaggio_flash'] = "Errore: Il codice fiscale è già registrato.";
        } else {
            $_SESSION['messaggio_flash'] = "Errore durante il salvataggio. Riprova più tardi.";
        }
        
        $_SESSION['dati_inseriti'] = $_POST;
        header("Location: registrazione_donatore.php");
        exit();
    }
}

// PREPARAZIONE DELLA PAGINA
$template = caricaTemplate('registrazione_donatore.html');

$template = str_replace('<form method="post"', '<form method="post" autocomplete="new-password"', $template);

$template = preg_replace(
    '/<input\s+(type="text"|type="email"|type="tel"|type="date"|type="number")/i',
    '<input autocomplete="new-password" $1',
    $template
);

$template = preg_replace(
    '/(<input[^>]*name="codice_fiscale"[^>]*)((minlength|pattern|title)="[^"]*"\s*)+/', 
    '$1', 
    $template
);

$template = str_replace(
    'name="codice_fiscale"',
    'name="codice_fiscale" maxlength="16" autocomplete="nope"',
    $template
);

$template = str_replace(
    'name="luogo_nascita"',
    'name="luogo_nascita" autocomplete="nope" readonly onfocus="this.removeAttribute(\'readonly\');"',
    $template
);

$messaggioErrore = getMessaggioFlashHTML();

// Sostituisco il placeholder del messaggio
$template = str_replace('[messaggioErrore]', $messaggioErrore, $template);

// Inizializzo variabili vuote
$dati = [
    'nome' => '', 
    'cognome' => '', 
    'data_nascita' => '', 
    'luogo_nascita' => '', 
    'codice_fiscale' => '', 
    'indirizzo' => '', 
    'telefono' => '', 
    'email' => '', 
    'gruppo_sanguigno' => '', 
    'sesso' => '', 
    'peso' => ''
];

$titoloPagina = "Registrazione donatore";
$sottotitoloPagina = "Diventa un eroe, entra nella nostra rete di donatori";
$testoSubmit = "Invia Registrazione";

$metaTitle = "Registrazione donatore - ATDS";
$metaDescription = "Pagina per registrarsi come nuovo donatore presso l'Associazione Tecweb Donatori Sangue";
$metaKeywords = "registrazione, donatore, sangue, volontariato, ATDS";

$stmt = $pdo->prepare("SELECT * FROM donatori WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userDB = $stmt->fetch();

if ($userDB) {
    $dati = $userDB;

    $titoloPagina = "Modifica dati donatore";
    $sottotitoloPagina = "Modifica le tue informazioni di donatore";
    $testoSubmit = "Salva modifiche";

    $metaTitle = "Modifica profilo donatore - ATDS";
    $metaDescription = "Pagina per modificare i dati del profilo donatore ATDS";
    $metaKeywords = "modifica, profilo, donatore, aggiornamento, dati, ATDS";
}

if (isset($_SESSION['dati_inseriti'])) {
    $temp = $_SESSION['dati_inseriti'];
    $dati['nome'] = $temp['nome'];
    $dati['cognome'] = $temp['cognome'];
    $dati['data_nascita'] = $temp['data_nascita'];
    $dati['luogo_nascita'] = $temp['luogo_nascita'];
    $dati['codice_fiscale'] = $temp['codice_fiscale'];
    $dati['indirizzo'] = $temp['residenza'];
    $dati['telefono'] = $temp['telefono'];
    $dati['email'] = $temp['email'];
    $dati['gruppo_sanguigno'] = $temp['gruppo_sanguigno'];
    $dati['sesso'] = isset($temp['sesso']) ? $temp['sesso'] : '';
    $dati['peso'] = $temp['peso_corporeo_in_kg'];

    unset($_SESSION['dati_inseriti']);
}

// SOSTITUZIONE DEI SEGNAPOSTI
$template = str_replace('[valore_nome]', $dati['nome'], $template);
$template = str_replace('[valore_cognome]', $dati['cognome'], $template);
$template = str_replace('[valore_data_nascita]', $dati['data_nascita'], $template);
$template = str_replace('[valore_luogo_nascita]', $dati['luogo_nascita'], $template);
$template = str_replace('[valore_codice_fiscale]', $dati['codice_fiscale'], $template);
$template = str_replace('[valore_residenza]', $dati['indirizzo'], $template); 
$template = str_replace('[valore_telefono]', $dati['telefono'], $template);
$template = str_replace('[valore_email]', $dati['email'], $template);
$template = str_replace('[valore_peso]', $dati['peso'], $template);

// Testi Dinamici
$template = str_replace('[titoloPagina]', $titoloPagina,  $template);
$template = str_replace('[sottotitoloPagina]', $sottotitoloPagina , $template);
$template = str_replace('[testoSubmit]', $testoSubmit , $template);

// SEO Dinamico
$template = str_replace('[metaTitolo]', $metaTitle, $template);
$template = str_replace('[metaDescrizione]', $metaDescription, $template);
$template = str_replace('[metaKeywords]', $metaKeywords, $template);

// GESTIONE SELEZIONI
if ($dati['gruppo_sanguigno'] != "") {
    $find = 'value="'.$dati['gruppo_sanguigno'].'"'; 
    $replace = 'value="'.$dati['gruppo_sanguigno'].'" selected';
    $template = str_replace($find, $replace, $template);
}

if ($dati['sesso'] == 'Maschio') {
    $template = str_replace('value="Maschio"', 'value="Maschio" checked', $template);
} elseif ($dati['sesso'] == 'Femmina') {
    $template = str_replace('value="Femmina"', 'value="Femmina" checked', $template);
}

$dataMassima = date('Y-m-d', strtotime('-18 years'));
$template = str_replace('id="data_nascita"', 'id="data_nascita" max="'.$dataMassima.'"', $template);

// STAMPA FINALE
$breadcrumb = '<p><a href="/ggiora/src/index.php" lang="en">Home</a> / <a href="/ggiora/src/php/pages/profilo.php">Profilo</a> / <span>'.$titoloPagina.'</span></p>';
echo costruisciPagina($template, $breadcrumb, "registrazione_donatore.php");
?>
