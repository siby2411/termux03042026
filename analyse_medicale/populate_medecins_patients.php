<?php
require_once 'includes/db.php';

$pdo = getPDO();

// Médecins prescripteurs (si pas déjà présents)
$medecins = [
    ['nom' => 'Diop', 'prenom' => 'Mamadou', 'specialite' => 'Généraliste', 'numero_ordre' => 'ORD1001', 'telephone' => '771234567', 'hopital' => 'Hôpital Principal de Dakar'],
    ['nom' => 'Fall', 'prenom' => 'Aminata', 'specialite' => 'Pédiatre', 'numero_ordre' => 'ORD1002', 'telephone' => '772345678', 'hopital' => 'Hôpital pour Enfants Albert Royer'],
    ['nom' => 'Ndiaye', 'prenom' => 'Oumar', 'specialite' => 'Cardiologue', 'numero_ordre' => 'ORD1003', 'telephone' => '773456789', 'hopital' => 'Institut de Cardiologie'],
];

foreach ($medecins as $m) {
    $stmt = $pdo->prepare("SELECT id FROM medecins_prescripteurs WHERE numero_ordre = ?");
    $stmt->execute([$m['numero_ordre']]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO medecins_prescripteurs (nom, prenom, specialite, numero_ordre, telephone, hopital) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$m['nom'], $m['prenom'], $m['specialite'], $m['numero_ordre'], $m['telephone'], $m['hopital']]);
        echo "Médecin ajouté : Dr. " . $m['prenom'] . " " . $m['nom'] . "\n";
    }
}

// Patients
$patients_data = [
    ['first' => 'Abdoulaye', 'last' => 'Diallo', 'phone' => '771112233', 'email' => 'abdoulaye.diallo@email.sn', 'naissance' => '1985-03-15', 'sexe' => 'M', 'groupe' => 'O+', 'assurance' => 'IPM'],
    ['first' => 'Maimouna', 'last' => 'Ndiaye', 'phone' => '782223344', 'email' => 'maimouna.ndiaye@email.sn', 'naissance' => '1990-07-22', 'sexe' => 'F', 'groupe' => 'A+', 'assurance' => 'CSS'],
    ['first' => 'Serigne', 'last' => 'Fall', 'phone' => '763334455', 'email' => 'serigne.fall@email.sn', 'naissance' => '1978-11-05', 'sexe' => 'M', 'groupe' => 'B+', 'assurance' => 'SUNU'],
    ['first' => 'Awa', 'last' => 'Dieng', 'phone' => '704445566', 'email' => 'awa.dieng@email.sn', 'naissance' => '1982-09-18', 'sexe' => 'F', 'groupe' => 'AB+', 'assurance' => 'Privée'],
];

foreach ($patients_data as $p) {
    $username = strtolower($p['first'] . '.' . $p['last']);
    $password = password_hash('patient123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo "Patient $username existe déjà.\n";
        continue;
    }
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'patient')");
        $stmt->execute([$username, $password, $p['email'], $p['first'], $p['last'], $p['phone']]);
        $user_id = $pdo->lastInsertId();
        
        // Générer code patient
        $last_code = $pdo->query("SELECT code_patient FROM patients ORDER BY id DESC LIMIT 1")->fetchColumn();
        if ($last_code) {
            $num = (int)substr($last_code, -4) + 1;
        } else {
            $num = 1;
        }
        $code_patient = "PAT-" . date('Y') . "-" . sprintf("%04d", $num);
        
        $stmt = $pdo->prepare("INSERT INTO patients (user_id, code_patient, date_naissance, sexe, groupe_sanguin, assurance) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $code_patient, $p['naissance'], $p['sexe'], $p['groupe'], $p['assurance']]);
        
        $pdo->commit();
        echo "Patient ajouté : " . $p['first'] . " " . $p['last'] . " ($code_patient)\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur pour " . $p['first'] . " : " . $e->getMessage() . "\n";
    }
}

echo "\nPeuplement terminé.\n";
