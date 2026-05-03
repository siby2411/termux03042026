<?php
session_start();
require_once 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    echo "❌ Vous devez être connecté\n";
    exit;
}

$patient_id = $argv[1] ?? 1;

if (updateTokenPatient($patient_id)) {
    $patient = getPatientFromToken();
    echo "✅ Patient changé avec succès vers: " . $patient['prenom'] . " " . $patient['nom'] . "\n";
    echo "Code: " . $patient['code'] . "\n";
    echo "Téléphone: " . $patient['telephone'] . "\n";
} else {
    echo "❌ Erreur lors du changement de patient\n";
}
?>
