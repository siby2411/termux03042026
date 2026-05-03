<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Simuler la session de Dr. Fall
$_SESSION['user_id'] = 4;
$_SESSION['user_role'] = 'medecin';
$_SESSION['user_nom'] = 'Fall';
$_SESSION['user_prenom'] = 'Aminata';

// Créer un token avec le dernier patient
$db = getDB();
$stmt = $db->prepare("SELECT * FROM patients ORDER BY id DESC LIMIT 1");
$stmt->execute();
$last_patient = $stmt->fetch(PDO::FETCH_ASSOC);

if ($last_patient) {
    $token_data = [
        'user_id' => 4,
        'role' => 'medecin',
        'patient' => [
            'id' => $last_patient['id'],
            'code' => $last_patient['code_patient_unique'],
            'nom' => $last_patient['nom'],
            'prenom' => $last_patient['prenom'],
            'telephone' => $last_patient['telephone']
        ]
    ];
    $_SESSION['user_token'] = base64_encode(json_encode($token_data));
    echo "✅ Token créé avec le patient: " . $last_patient['prenom'] . " " . $last_patient['nom'] . "\n";
}

echo "\n=== REDIRECTION VERS LE DASHBOARD ===\n";
echo "URL: http://localhost:8000/modules/medecin/dashboard.php\n";
echo "Appuyez sur Ctrl+clic pour ouvrir le lien\n";
?>
