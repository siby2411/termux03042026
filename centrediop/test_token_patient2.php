<?php
session_start();
require_once 'includes/auth.php';

// Simuler une connexion médecin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'medecin';
$_SESSION['user_nom'] = 'Fall';
$_SESSION['user_prenom'] = 'Aminata';

// Créer un token avec le patient 2 (Fall Aminata)
$patient2 = [
    'id' => 2,
    'code' => 'PAT-000002',
    'nom' => 'Fall',
    'prenom' => 'Aminata',
    'telephone' => '78 123 45 67'
];

$token_data = [
    'user_id' => 1,
    'role' => 'medecin',
    'patient' => $patient2
];

$_SESSION['user_token'] = base64_encode(json_encode($token_data));

echo "=== TOKEN AVEC PATIENT 2 (Fall Aminata) ===\n\n";
echo "Token encodé: " . $_SESSION['user_token'] . "\n\n";
echo "Token décodé:\n";
print_r(getUserToken());
echo "\n\nPatient du token:\n";
print_r(getPatientFromToken());
?>
