<?php
session_start();
require_once 'includes/auth.php';

// Simuler une connexion médecin
$_SESSION['user_id'] = 1; // ID du médecin
$_SESSION['user_role'] = 'medecin';
$_SESSION['user_nom'] = 'Fall';
$_SESSION['user_prenom'] = 'Aminata';

// Créer un token avec le patient 1 (Siby Momo)
$patient1 = [
    'id' => 1,
    'code' => 'PAT-000001',
    'nom' => 'Siby',
    'prenom' => 'Momo',
    'telephone' => '77 654 28 03'
];

$token_data = [
    'user_id' => 1,
    'role' => 'medecin',
    'patient' => $patient1
];

$_SESSION['user_token'] = base64_encode(json_encode($token_data));

echo "=== TOKEN AVEC PATIENT 1 (Siby Momo) ===\n\n";
echo "Token encodé: " . $_SESSION['user_token'] . "\n\n";
echo "Token décodé:\n";
print_r(getUserToken());
echo "\n\nPatient du token:\n";
print_r(getPatientFromToken());
?>
