<?php
session_start();
require_once 'includes/auth.php';

// Simuler une session médecin
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'medecin';
$_SESSION['user_nom'] = 'Fall';
$_SESSION['user_prenom'] = 'Aminata';

echo "=== TEST MISE À JOUR DU TOKEN ===\n\n";

// Test avec patient 1
echo "Test patient 1 (Siby Momo):\n";
if (updateTokenPatient(1)) {
    $patient = getPatientFromToken();
    echo "✅ Succès: " . $patient['prenom'] . " " . $patient['nom'] . "\n";
    echo "   Code: " . $patient['code'] . "\n";
    echo "   Téléphone: " . $patient['telephone'] . "\n";
} else {
    echo "❌ Échec patient 1\n";
}

echo "\n";

// Test avec patient 2
echo "Test patient 2 (Fall Aminata):\n";
if (updateTokenPatient(2)) {
    $patient = getPatientFromToken();
    echo "✅ Succès: " . $patient['prenom'] . " " . $patient['nom'] . "\n";
    echo "   Code: " . $patient['code'] . "\n";
    echo "   Téléphone: " . $patient['telephone'] . "\n";
} else {
    echo "❌ Échec patient 2\n";
}

echo "\n=== TOKEN FINAL ===\n";
print_r(getUserToken());
?>
