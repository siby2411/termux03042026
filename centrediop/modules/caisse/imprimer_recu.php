<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_GET['id'])) die("Consultation introuvable.");
$c_id = (int)$_GET['id'];

$query = $conn->query("SELECT p.nom, p.prenom, c.prix, c.date_consultation 
                      FROM consultations c 
                      JOIN patients p ON c.patient_id = p.id 
                      WHERE c.id = $c_id");
$data = $query->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Reçu #<?= $c_id ?></title></head>
<body onload="window.print()">
    <div style="width: 300px; border: 1px solid #000; padding: 10px; font-family: Arial;">
        <h3>Centre Mamadou Diop</h3>
        <p>Date: <?= date('d/m/Y H:i') ?></p>
        <hr>
        <p>Patient: <?= $data['nom'] . ' ' . $data['prenom'] ?></p>
        <p>Montant payé: <strong><?= number_format($data['prix'], 0, ',', ' ') ?> FCFA</strong></p>
        <hr>
        <p style="text-align:center;">Merci de votre confiance</p>
    </div>
</body>
</html>
