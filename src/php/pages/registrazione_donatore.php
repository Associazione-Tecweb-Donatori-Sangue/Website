<?php
require_once "../utility.php";
require_once "../db.php";

// Sicurezza
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Controllo Età
    $dataNascita = new DateTime($_POST['data_nascita']);
    $oggi = new DateTime();
    $eta = $oggi->diff($dataNascita)->y;

    if ($eta < 18 || $eta > 60) {
        $_SESSION['messaggio_flash'] = "Errore: Devi avere almeno 18 anni e non più di 60 anni per registrarti come donatore.";
        $_SESSION['dati_inseriti'] = $_POST; // <--- SALVO I DATI
        header("Location: registrazione_donatore.php");
        exit();
    }

    // 2. Controllo Peso (Standard 50kg)
    $peso = floatval($_POST['peso_corporeo_in_kg']);
    if ($peso < 50) {
        $_SESSION['messaggio_flash'] = "Errore: Il peso minimo per donare è 50 Kg.";
        $_SESSION['dati_inseriti'] = $_POST; // <--- SALVO I DATI
        header("Location: registrazione_donatore.php");
        exit();
    }

    try {
        // Controllo se sto facendo INSERT (nuovo) o UPDATE (modifica)
        // Verifico se esiste già un record per questo user_id
        $checkStmt = $pdo->prepare("SELECT user_id FROM donatori WHERE user_id = ?");
        $checkStmt->execute([$_SESSION['user_id']]);
        $esiste = $checkStmt->fetch();

        if ($esiste) {
            // UPDATE
            $sql = "UPDATE donatori SET nome=?, cognome=?, data_nascita=?, luogo_nascita=?, codice_fiscale=?, indirizzo=?, telefono=?, email=?, gruppo_sanguigno=?, sesso=?, peso=? WHERE user_id=?";
            // Aggiungo user_id alla fine per il WHERE
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
            // INSERT
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
        die("Errore salvataggio donatore: " . $e->getMessage());
    }
}

// 2. PREPARAZIONE DELLA PAGINA (Visualizzazione)
$template = caricaTemplate('registrazione_donatore.html');

if (isset($_SESSION['messaggio_flash'])) {
    $classe = (strpos($_SESSION['messaggio_flash'], 'Errore') !== false) ? 'msg-error' : 'msg-success';

    $msgHTML = '<div class="' . $classe . '">
                    ' . htmlspecialchars($_SESSION['messaggio_flash']) . '
                </div>';
    
    // Inserisco il messaggio prima del form
    $template = str_replace('<form', $msgHTML . '<form', $template);
    
    unset($_SESSION['messaggio_flash']);
}

// Inizializzo variabili vuote (caso "Nuova Registrazione")
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
// Variabili per il corpo della pagina
$titoloPagina = "Registrazione donatore";
$sottotitoloPagina = "Diventa un eroe, entra nella nostra rete di donatori";
$testoSubmit = "Invia Registrazione";

// Variabili per il SEO (Head)
$metaTitle = "Registrazione donatore - ATDS";
$metaDescription = "Pagina per registrarsi come nuovo donatore presso l'Associazione Tecweb Donatori Sangue";
$metaKeywords = "registrazione, donatore, sangue, volontariato, ATDS";

// Controllo se l'utente ha già i dati nel DB
$stmt = $pdo->prepare("SELECT * FROM donatori WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userDB = $stmt->fetch();

// Se l'utente HA già i dati, li carico (caso "Modifica")
if ($userDB) {
    $dati = $userDB;

    // Aggiorno testi pagina
    $titoloPagina = "Modifica dati donatore";
    $sottotitoloPagina = "Modifica le tue informazioni di donatore";
    $testoSubmit = "Salva modifiche";
    
    // Aggiorno testi SEO
    $metaTitle = "Modifica profilo donatore - ATDS";
    $metaDescription = "Pagina per modificare i dati del profilo donatore ATDS";
    $metaKeywords = "modifica, profilo, donatore, aggiornamento, dati, ATDS";
}

// Se ci sono dati inseriti precedentemente (errore di validazione), li uso per precompilare il form
if (isset($_SESSION['dati_inseriti'])) {
    $temp = $_SESSION['dati_inseriti'];
    
    // Mappo i campi del FORM ($temp) sui campi attesi dall'array $dati (DB)
    // Nota: alcuni nomi nel form sono diversi da quelli nel DB/Array interno
    $dati['nome'] = $temp['nome'];
    $dati['cognome'] = $temp['cognome'];
    $dati['data_nascita'] = $temp['data_nascita'];
    $dati['luogo_nascita'] = $temp['luogo_nascita'];
    $dati['codice_fiscale'] = $temp['codice_fiscale'];
    $dati['indirizzo'] = $temp['residenza']; // Nel form si chiama 'residenza', nell'array 'indirizzo'
    $dati['telefono'] = $temp['telefono'];
    $dati['email'] = $temp['email'];
    $dati['gruppo_sanguigno'] = $temp['gruppo_sanguigno'];
    $dati['sesso'] = isset($temp['sesso']) ? $temp['sesso'] : '';
    $dati['peso'] = $temp['peso_corporeo_in_kg']; // Nel form è 'peso_corporeo_in_kg', nell'array 'peso'

    // Pulisco la sessione per non rivedere questi dati se ricarico la pagina domani
    unset($_SESSION['dati_inseriti']);
}

// 3. SOSTITUZIONE DEI SEGNAPOSTI (Input di testo)
$template = str_replace('[valore_nome]', $dati['nome'], $template);
$template = str_replace('[valore_cognome]', $dati['cognome'], $template);
$template = str_replace('[valore_data_nascita]', $dati['data_nascita'], $template);
$template = str_replace('[valore_luogo_nascita]', $dati['luogo_nascita'], $template);
$template = str_replace('[valore_codice_fiscale]', $dati['codice_fiscale'], $template);
$template = str_replace('[valore_residenza]', $dati['indirizzo'], $template); 
$template = str_replace('[valore_telefono]', $dati['telefono'], $template);
$template = str_replace('[valore_email]', $dati['email'], $template);
$template = str_replace('[valore_peso]', $dati['peso'], $template);

// B. Testi Dinamici (H1, H2, Button)
$template = str_replace('[titoloPagina]', $titoloPagina,  $template);
$template = str_replace('[sottotitoloPagina]', $sottotitoloPagina , $template);
$template = str_replace('[testoSubmit]', $testoSubmit , $template);

// C. SEO Dinamico (Head)
$template = str_replace('[metaTitolo]', $metaTitle, $template);
$template = str_replace('[metaDescrizione]', $metaDescription, $template);
$template = str_replace('[metaKeywords]', $metaKeywords, $template);

// 4. GESTIONE SELEZIONI (Select e Radio)
// Trucco: cerchiamo il valore nell'HTML e aggiungiamo l'attributo "selected" o "checked"
if ($dati['gruppo_sanguigno'] != "") {
    $find = 'value="'.$dati['gruppo_sanguigno'].'"'; 
    $replace = 'value="'.$dati['gruppo_sanguigno'].'" selected';
    $template = str_replace($find, $replace, $template);
}

// Pulisco prima i segnaposti
$template = str_replace('[checked_maschio]', '', $template);
$template = str_replace('[checked_femmina]', '', $template);

if ($dati['sesso'] == 'Maschio') {
    $template = str_replace('value="Maschio"', 'value="Maschio" checked', $template);
} elseif ($dati['sesso'] == 'Femmina') {
    $template = str_replace('value="Femmina"', 'value="Femmina" checked', $template);
}

// 1. Imposto la data massima selezionabile (Oggi - 18 anni)
$dataMassima = date('Y-m-d', strtotime('-18 years'));
// Aggiungo l'attributo max all'input data_nascita
$template = str_replace('id="data_nascita"', 'id="data_nascita" max="'.$dataMassima.'"', $template);

// 5. STAMPA FINALE
$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <a href="/php/pages/profilo.php">Profilo</a> / <span>'.$titoloPagina.'</span></p>';
echo costruisciPagina($template, $breadcrumb, "registrazione_donatore.php");
?>