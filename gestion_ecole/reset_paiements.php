<?php
session_start();
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }

require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// Exécution du TRUNCATE pour réinitialiser la table
if ($conn->query("TRUNCATE TABLE paiements_scolarite")) {
    echo "<script>alert('Table paiements réinitialisée avec succès !'); window.location.href='index.php';</script>";
} else {
    echo "Erreur lors de la réinitialisation : " . $conn->error;
}
?>
