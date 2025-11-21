<?php
$page_title = "Prenotazione Confermata";
require_once "../php/header.php";
?>
<h1>Prenotazione avvenuta con successo!</h1>
<p>Grazie <?php echo $_SESSION['username']; ?> per aver prenotato la tua donazione.</p>

<?php require_once "../php/footer.php"; ?>
