<?php
require_once 'config/database.php';

echo "=== TEST DU FLUX COMPLET AVEC Dr. Fall (Pédiatrie) ===\n\n";

$db = new Database();
$conn = $db->getConnection();

// 1. Créer un patient test (si pas déjà fait)
$code_test = 'PAT-DEMO-' . date('Ymd');

$check = $conn->prepare("SELECT id FROM patients WHERE code_patient_unique = ?");
$check->execute([$code_test]);
if (!$check->fetch()) {
    // Créer un patient de démonstration
    $insert = $conn->prepare("INSERT INTO patients 
        (code_patient_unique, numero_patient, prenom, nom, date_naissance, sexe, telephone, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $insert->execute([
        $code_test,
        $code_test,
        'Mamadou',
        'Diop',
        '2020-03-15', // Un enfant de 6 ans
        'M',
        '77 123 45 67'
    ]);
    
    $patient_id = $conn->lastInsertId();
    echo "✅ Patient de démo créé: Mamadou Diop (Code: $code_test)\n";
} else {
    $patient_id = $conn->query("SELECT id FROM patients WHERE code_patient_unique = '$code_test'")->fetchColumn();
    echo "ℹ️ Patient existant: Mamadou Diop (Code: $code_test)\n";
}

// 2. Créer un rendez-vous avec Dr. Fall pour demain
$demain = date('Y-m-d', strtotime('+1 day'));
$heure = '10:30:00';

$check_rdv = $conn->prepare("SELECT id FROM rendez_vous WHERE patient_id = ? AND date_rdv = ?");
$check_rdv->execute([$patient_id, $demain]);

if (!$check_rdv->fetch()) {
    $insert_rdv = $conn->prepare("INSERT INTO rendez_vous 
        (patient_id, service_id, medecin_id, date_rdv, heure_rdv, motif, statut, cree_le)
        VALUES (?, ?, ?, ?, ?, ?, 'programme', NOW())");
    
    $insert_rdv->execute([
        $patient_id,
        3, // Pédiatrie
        4, // Dr. Fall (ID 4)
        $demain,
        $heure,
        'Consultation pédiatrique - Vaccin'
    ]);
    
    $rdv_id = $conn->lastInsertId();
    echo "✅ Rendez-vous créé pour demain à 10h30 (ID: $rdv_id)\n";
} else {
    $rdv_id = $check_rdv->fetchColumn();
    echo "ℹ️ Rendez-vous déjà existant (ID: $rdv_id)\n";
}

// 3. Afficher les informations
echo "\n=== RÉCAPITULATIF ===\n";
echo "Patient: Mamadou Diop\n";
echo "Code patient: $code_test\n";
echo "Service: Pédiatrie\n";
echo "Médecin: Dr. Aminata Fall\n";
echo "Date du rendez-vous: " . date('d/m/Y', strtotime($demain)) . " à 10h30\n";
echo "ID du rendez-vous: $rdv_id\n";

// 4. Vérifier que le caissier peut trouver ce rendez-vous
echo "\n=== RECHERCHE POUR LE CAISSIER ===\n";
echo "Le caissier pourra retrouver ce rendez-vous par :\n";
echo "1️⃣ Code patient: $code_test\n";
echo "2️⃣ Nom: Diop\n";
echo "3️⃣ Prénom: Mamadou\n";
echo "4️⃣ Téléphone: 77 123 45 67\n";
echo "5️⃣ Date: " . date('d/m/Y', strtotime($demain)) . "\n";
echo "6️⃣ Service: Pédiatrie\n";
echo "7️⃣ ID Rendez-vous: $rdv_id\n";

echo "\n=== INSTRUCTIONS ===\n";
echo "1. Connectez-vous en tant que Dr. Fall:\n";
echo "   URL: http://localhost:8000/modules/auth/login.php\n";
echo "   Identifiants: dr.fall / pediatre123\n";
echo "   → Vous verrez le rendez-vous de Mamadou Diop dans votre liste\n\n";

echo "2. Connectez-vous en tant que caissier:\n";
echo "   URL: http://localhost:8000/modules/auth/login.php\n";
echo "   Identifiants: caissier1 / caissier123\n";
echo "   → Utilisez la recherche multi-critères pour trouver le patient\n\n";

echo "3. Sur le dashboard caissier, dans la section 'Recherche patient':\n";
echo "   - Tapez '$code_test' dans 'Code patient' et cliquez Rechercher\n";
echo "   OU\n";
echo "   - Tapez 'Diop' dans 'Nom' et cliquez Rechercher\n";
echo "   → Vous verrez Mamadou Diop apparaître\n\n";

echo "4. Cliquez sur 'Payer' à côté du patient\n";
echo "5. Sélectionnez le rendez-vous du " . date('d/m/Y', strtotime($demain)) . " à 10h30\n";
echo "6. Effectuez le paiement\n";
echo "7. Le patient sera ajouté à la file d'attente\n";
?>
