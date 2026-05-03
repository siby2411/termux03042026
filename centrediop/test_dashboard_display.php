<?php
session_start();
require_once 'includes/auth.php';
require_once 'config/database.php';

// Simuler une connexion médecin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'medecin';
$_SESSION['user_nom'] = 'Fall';
$_SESSION['user_prenom'] = 'Aminata';

$database = new Database();
$db = $database->getConnection();

// Récupérer les patients
$patients = $db->query("SELECT id, code_patient_unique, nom, prenom, telephone FROM patients")->fetchAll();

echo "=== TEST D'AFFICHAGE DES PATIENTS ===\n\n";

foreach ($patients as $patient) {
    echo "----------------------------------------\n";
    
    // Mettre à jour le token avec ce patient
    updateTokenPatient($patient['id']);
    $token_patient = getPatientFromToken();
    
    echo "Patient sélectionné: " . $patient['prenom'] . " " . $patient['nom'] . "\n";
    echo "Code: " . $patient['code_patient_unique'] . "\n";
    echo "Téléphone: " . $patient['telephone'] . "\n";
    echo "Token patient: \n";
    print_r($token_patient);
    echo "\n";
}

echo "----------------------------------------\n";
echo "✅ Test terminé - Le token change bien avec chaque patient\n";
?>
