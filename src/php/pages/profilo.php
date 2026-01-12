<?php
require_once "../utility.php";
session_start();

// 1. Controllo sicurezza: se non è loggato, via al login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. Carico il template HTML
$paginaHTML = file_get_contents('../../html/profilo.html');

// Logica donatore
$htmlDonatore = "";

if (!isset($_SESSION['dati_donatore'])) {
    // --- CASO A: NON è ancora donatore ---> Mostro il bottone per registrarsi come donatore
    $htmlDonatore = '
    <div class="button_std">
        <a href="/php/pages/registrazione_donatore.php" class="button">Completa la registrazione come donatore</a>
    </div>';
} else {
    // --- CASO B: È già donatore ---> Recupero i dati (dalla sessione per ora, dal DB in futuro)
    $dati = $_SESSION['dati_donatore'];
    
    // Costruisco un riepilogo HTML
    $htmlDonatore = '
    <div class="button_std">
        <a href="/php/pages/registrazione_donatore.php" class="button">Modifica i tuoi dati</a>
    </div>
    <section class="dati_donatore_box">
        <h3 class="titolo_terziario">Il tuo profilo Donatore</h3>
        <dl class="lista_dati">
            <dt>Nome e Cognome:</dt>
            <dd>' . $dati['nome'] . ' ' . $dati['cognome'] . '</dd>
            
            <dt>Gruppo Sanguigno:</dt>
            <dd class="evidenziato">' . $dati['gruppo'] . '</dd>
            
            <dt>Email:</dt>
            <dd>' . $dati['email'] . '</dd>
            
            <dt>Telefono:</dt>
            <dd>' . $dati['telefono'] . '</dd>
        </dl>
    </section>';
}

// Inserisco il blocco donatore nella pagina
$paginaHTML = str_replace('[sezioneDonatore]', $htmlDonatore, $paginaHTML);

// 3. Gestione contenuto specifico della pagina
$nomeUtente = '<h1> Profilo di ' . $_SESSION['username'] . '</h1>';
$paginaHTML = str_replace('[nomeUtente]', $nomeUtente, $paginaHTML);

$breadcrumb = '<p><a href="../../index.php" lang="en">Home</a> / <span>Profilo</span></p>';

echo costruisciPagina($paginaHTML, $breadcrumb, "profilo.php");
?>