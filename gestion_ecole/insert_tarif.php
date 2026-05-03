<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classe_id = intval($_POST['classe_id']);
    $montant_scolarite = floatval($_POST['montant_scolarite']);
    $droit_inscription = floatval($_POST['droit_inscription']);
    $annee = "2025-2026"; 

    $stmt = $conn->prepare("INSERT INTO tarifs (classe_id, montant_scolarite, droit_inscription, annee_academique) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE montant_scolarite=?, droit_inscription=?");
    $stmt->bind_param("iddsdd", $classe_id, $montant_scolarite, $droit_inscription, $annee, $montant_scolarite, $droit_inscription);

    if ($stmt->execute()) {
        header("Location: ajouter_tarif.php?status=success");
    } else {
        echo "Erreur : " . $stmt->error;
    }
}
