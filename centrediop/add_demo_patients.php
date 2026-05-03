<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== AJOUT DE PATIENTS DE DÉMONSTRATION ===\n\n";

try {
    $conn->beginTransaction();
    
    // 1. Pédiatrie (Dr. Fall - service_id 3)
    echo "Ajout de patients en Pédiatrie...\n";
    $conn->exec("SET @current_service_id = 3");
    
    $patients_pediatrie = [
        ['Emma', 'Diallo', '2019-05-10', 'Dakar', 'F', '77 111 22 33'],
        ['Lucas', 'Diop', '2020-03-15', 'Dakar', 'M', '78 222 33 44'],
        ['Chloé', 'Fall', '2018-11-20', 'Thiès', 'F', '76 333 44 55']
    ];
    
    foreach ($patients_pediatrie as $p) {
        $conn->prepare("INSERT INTO patients (prenom, nom, date_naissance, lieu_naissance, sexe, telephone, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())")->execute($p);
        $patient_id = $conn->lastInsertId();
        
        // Créer dossier médical
        $conn->prepare("INSERT INTO dossiers_medicaux (patient_id, created_at) VALUES (?, NOW())")->execute([$patient_id]);
        
        // Ajouter à la file d'attente
        $token = 'PED' . date('ymd') . str_pad($patient_id, 3, '0', STR_PAD_LEFT);
        $conn->prepare("INSERT INTO file_attente (token, patient_id, service_id, priorite, statut, cree_a) 
                        VALUES (?, ?, 3, 'normal', 'en_attente', NOW())")->execute([$token, $patient_id]);
        
        // Récupérer le code généré
        $code = $conn->query("SELECT code_patient_unique FROM patients WHERE id = $patient_id")->fetchColumn();
        echo "   ✅ Patient: {$p[0]} {$p[1]} - Code: $code - Token: $token\n";
    }
    
    // 2. Cardiologie (service_id 8)
    echo "\nAjout de patients en Cardiologie...\n";
    $conn->exec("SET @current_service_id = 8");
    
    $patients_cardio = [
        ['Amadou', 'Ba', '1965-08-20', 'Dakar', 'M', '77 444 55 66'],
        ['Fatou', 'Dieng', '1970-12-03', 'Saint-Louis', 'F', '78 555 66 77']
    ];
    
    foreach ($patients_cardio as $p) {
        $conn->prepare("INSERT INTO patients (prenom, nom, date_naissance, lieu_naissance, sexe, telephone, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())")->execute($p);
        $patient_id = $conn->lastInsertId();
        
        $conn->prepare("INSERT INTO dossiers_medicaux (patient_id, created_at) VALUES (?, NOW())")->execute([$patient_id]);
        
        $token = 'CAR' . date('ymd') . str_pad($patient_id, 3, '0', STR_PAD_LEFT);
        $conn->prepare("INSERT INTO file_attente (token, patient_id, service_id, priorite, statut, cree_a) 
                        VALUES (?, ?, 8, 'normal', 'en_attente', NOW())")->execute([$token, $patient_id]);
        
        $code = $conn->query("SELECT code_patient_unique FROM patients WHERE id = $patient_id")->fetchColumn();
        echo "   ✅ Patient: {$p[0]} {$p[1]} - Code: $code - Token: $token\n";
    }
    
    // 3. Odontologie (service_id 4)
    echo "\nAjout de patients en Odontologie...\n";
    $conn->exec("SET @current_service_id = 4");
    
    $patients_odonto = [
        ['Ibrahima', 'Sow', '1985-02-14', 'Dakar', 'M', '76 777 88 99'],
        ['Mariama', 'Ndiaye', '1990-07-22', 'Thiès', 'F', '77 888 99 00']
    ];
    
    foreach ($patients_odonto as $p) {
        $conn->prepare("INSERT INTO patients (prenom, nom, date_naissance, lieu_naissance, sexe, telephone, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())")->execute($p);
        $patient_id = $conn->lastInsertId();
        
        $conn->prepare("INSERT INTO dossiers_medicaux (patient_id, created_at) VALUES (?, NOW())")->execute([$patient_id]);
        
        $token = 'ODO' . date('ymd') . str_pad($patient_id, 3, '0', STR_PAD_LEFT);
        $conn->prepare("INSERT INTO file_attente (token, patient_id, service_id, priorite, statut, cree_a) 
                        VALUES (?, ?, 4, 'normal', 'en_attente', NOW())")->execute([$token, $patient_id]);
        
        $code = $conn->query("SELECT code_patient_unique FROM patients WHERE id = $patient_id")->fetchColumn();
        echo "   ✅ Patient: {$p[0]} {$p[1]} - Code: $code - Token: $token\n";
    }
    
    $conn->commit();
    
    echo "\n🎉 Patients de démonstration ajoutés avec succès !\n";
    echo "\n=== RÉCAPITULATIF DES CODES PATIENTS ===\n";
    
    $patients = $conn->query("
        SELECT p.*, s.name as service_nom
        FROM patients p
        JOIN file_attente f ON p.id = f.patient_id
        JOIN services s ON f.service_id = s.id
        ORDER BY s.name
    ")->fetchAll();
    
    foreach ($patients as $p) {
        echo "{$p['service_nom']}: {$p['prenom']} {$p['nom']} - Code: {$p['code_patient_unique']}\n";
    }
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
