<?php
require_once "../utility.php";
session_start();

// Sicurezza
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 1. GESTIONE SALVATAGGIO (Se l'utente ha premuto "Invia")
if (isset($_POST['nome'])) {
    // Raccolta dati
    $nuoviDati = array(
        'nome' => pulisciInput($_POST['nome']),
        'cognome' => pulisciInput($_POST['cognome']),
        'data_nascita' => pulisciInput($_POST['data_nascita']),
        'luogo_nascita' => pulisciInput($_POST['luogo_nascita']),
        'codice_fiscale' => pulisciInput($_POST['codice_fiscale']),
        'residenza' => pulisciInput($_POST['residenza']),
        'telefono' => pulisciInput($_POST['telefono']),
        'email' => pulisciInput($_POST['email']),
        'gruppo' => pulisciInput($_POST['gruppo_sanguigno']),
        'sesso' => pulisciInput($_POST['sesso']), // maschio o femmina
        'peso' => pulisciInput($_POST['peso_corporeo_in_kg'])
    );

    // Salvataggio (sovrascrive i vecchi se c'erano)
    // TODO: $dbAccess->insertOrUpdateDonatore(...)
    $_SESSION['dati_donatore'] = $nuoviDati;

    header("Location: profilo.php");
    exit();
}


// 2. PREPARAZIONE DELLA PAGINA (Visualizzazione)
$template = file_get_contents('../../html/registrazione_donatore.html');

// Inizializzo variabili vuote (caso "Nuova Registrazione")
$dati = [
    'nome' => '', 'cognome' => '', 'data_nascita' => '', 
    'luogo_nascita' => '', 'codice_fiscale' => '', 
    'residenza' => '', 'telefono' => '', 'email' => '', 
    'gruppo' => '', 'sesso' => '', 'peso' => ''
];
$titoloPagina = "Registrazione Donatore"; // Titolo H1 di default

// Se l'utente HA già i dati, li carico (caso "Modifica")
if (isset($_SESSION['dati_donatore'])) {
    $dati = $_SESSION['dati_donatore'];
    $titoloPagina = "Modifica Dati Donatore";
}

// 3. SOSTITUZIONE DEI SEGNAPOSTI (Input di testo)
$template = str_replace('[valore_nome]', $dati['nome'], $template);
$template = str_replace('[valore_cognome]', $dati['cognome'], $template);
$template = str_replace('[valore_data_nascita]', $dati['data_nascita'], $template);
$template = str_replace('[valore_luogo_nascita]', $dati['luogo_nascita'], $template);
$template = str_replace('[valore_codice_fiscale]', $dati['codice_fiscale'], $template);
$template = str_replace('[valore_residenza]', $dati['residenza'], $template);
$template = str_replace('[valore_telefono]', $dati['telefono'], $template);
$template = str_replace('[valore_email]', $dati['email'], $template);
$template = str_replace('[valore_peso]', $dati['peso'], $template);

// Aggiorno anche il titolo H1 della pagina
$template = str_replace('<h1>Registrazione Donatore</h1>', '<h1>'.$titoloPagina.'</h1>', $template);


// 4. GESTIONE SELEZIONI (Select e Radio)
// Trucco: cerchiamo il valore nell'HTML e aggiungiamo l'attributo "selected" o "checked"

if ($dati['gruppo'] != "") {
    // Esempio: cerco value="Apos" e lo cambio in value="Apos" selected
    $find = 'value="'.$dati['gruppo'].'"'; 
    $replace = 'value="'.$dati['gruppo'].'" selected';
    $template = str_replace($find, $replace, $template);
}

if ($dati['sesso'] != "") {
    // Esempio: cerco value="maschio" e lo cambio in value="maschio" checked
    // Nota: aggiungo uno spazio prima di value per essere sicuro di non rompere altre stringhe
    $find = 'value="'.$dati['sesso'].'"';
    $replace = 'value="'.$dati['sesso'].'" checked';
    $template = str_replace($find, $replace, $template);
}


// 5. STAMPA FINALE
$breadcrumb = '<p><a href="/index.php" lang="en">Home</a> / <span>'.$titoloPagina.'</span></p>';
echo costruisciPagina($template, $breadcrumb, "registrazione_donatore.php");
?>