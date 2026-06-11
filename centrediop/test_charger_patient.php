<?php
session_start();
require_once 'includes/auth.php';

// Simuler la session
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'medecin';

echo "=== TEST CHARGER PATIENT ===\n\n";

// Tester avec patient 1
echo "Chargement patient 1 (Siby Momo):\n";
if (updateTokenPatient(1)) {
    $patient = getPatientFromToken();
    echo "✅ Succès - Patient chargé: " . $patient['prenom'] . " " . $patient['nom'] . "\n";
    echo "   Code: " . $patient['code'] . "\n";
    echo "   Téléphone: " . $patient['telephone'] . "\n";
} else {
    echo "❌ Échec chargement patient 1\n";
}

echo "\n";

// Tester avec patient 2
echo "Chargement patient 2 (Fall Aminata):\n";
if (updateTokenPatient(2)) {
    $patient = getPatientFromToken();
    echo "✅ Succès - Patient chargé: " . $patient['prenom'] . " " . $patient['nom'] . "\n";
    echo "   Code: " . $patient['code'] . "\n";
    echo "   Téléphone: " . $patient['telephone'] . "\n";
} else {
    echo "❌ Échec chargement patient 2\n";
}

echo "\n=== TOKEN FINAL ===\n";
print_r(getUserToken());
?>
