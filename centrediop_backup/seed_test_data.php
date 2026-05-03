<?php
require_once 'config/database.php';

try {
    $pdo = getPDO();
    
    echo "Création des données de test...\n";
    
    // Récupérer les IDs des services
    $services = $pdo->query("SELECT id, name FROM services")->fetchAll(PDO::FETCH_ASSOC);
    $service_ids = array_column($services, 'id');
    
    if (empty($service_ids)) {
        throw new Exception("Aucun service trouvé dans la base de données");
    }
    
    // Créer des patients de test
    $patients = [
        ['Awa', 'Ndiaye', '2010-05-15', 'F', '781234567', 'Dakar'],
        ['Omar', 'Diallo', '1990-08-20', 'M', '782345678', 'Pikine'],
        ['Fatoumata', 'Sow', '1985-11-10', 'F', '783456789', 'Guediawaye'],
        ['Ibrahima', 'Ba', '2005-03-25', 'M', '784567890', 'Rufisque'],
        ['Khadija', 'Fall', '1978-07-18', 'F', '785678901', 'Thiès'],
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO patients (numero_patient, prenom, nom, date_naissance, sexe, telephone, adresse)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($patients as $i => $p) {
        $numero = 'PAT-' . date('Ymd') . '-' . str_pad($i+1, 4, '0', STR_PAD_LEFT);
        $stmt->execute([$numero, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5]]);
        $patient_id = $pdo->lastInsertId();
        
        // Créer dossier médical
        $pdo->prepare("INSERT INTO dossiers_medicaux (patient_id) VALUES (?)")->execute([$patient_id]);
        echo "  ✅ Patient créé: {$p[0]} {$p[1]}\n";
    }
    
    // Récupérer les IDs des patients créés
    $patients_db = $pdo->query("SELECT id FROM patients")->fetchAll();
    
    // Créer des tokens de test
    echo "  Création des tokens...\n";
    $stmt_token = $pdo->prepare("
        INSERT INTO file_attente (token, patient_id, service_id, priorite, statut)
        VALUES (?, ?, ?, ?, 'en_attente')
    ");
    
    for ($i=0; $i<5; $i++) {
        if (!isset($patients_db[$i])) continue;
        
        $patient = $patients_db[$i];
        $service_id = $service_ids[array_rand($service_ids)];
        $priority = ($i % 2 == 0) ? 'senior' : 'normal';
        $token = 'TKN' . date('His') . str_pad($i+1, 2, '0', STR_PAD_LEFT);
        
        $stmt_token->execute([$token, $patient['id'], $service_id, $priority]);
    }
    echo "  ✅ Tokens de test créés\n";
    
    // Récupérer les médecins
    $medecins = $pdo->query("SELECT id FROM users WHERE role='medecin'")->fetchAll();
    
    if (!empty($medecins)) {
        // Créer quelques rendez-vous
        echo "  Création des rendez-vous...\n";
        $stmt_rdv = $pdo->prepare("
            INSERT INTO rendez_vous (patient_id, service_id, medecin_id, date_rdv, heure_rdv, motif, statut)
            VALUES (?, ?, ?, ?, ?, ?, 'programme')
        ");
        
        for ($i=0; $i<3; $i++) {
            if (!isset($patients_db[$i])) continue;
            
            $patient = $patients_db[$i];
            $medecin = $medecins[array_rand($medecins)];
            $service_id = $service_ids[array_rand($service_ids)];
            $date_rdv = date('Y-m-d', strtotime('+' . ($i+1) . ' days'));
            $heure_rdv = sprintf('%02d:00:00', 9 + $i);
            
            $stmt_rdv->execute([
                $patient['id'], 
                $service_id, 
                $medecin['id'], 
                $date_rdv, 
                $heure_rdv, 
                'Consultation de routine'
            ]);
        }
        echo "  ✅ Rendez-vous de test créés\n";
    } else {
        echo "  ⚠️ Aucun médecin trouvé, les rendez-vous n'ont pas été créés\n";
    }
    
    echo "\n✅ Données de test créées avec succès !\n";
    echo "📊 Résumé:\n";
    echo "  - Patients: " . count($patients) . "\n";
    echo "  - Tokens en file d'attente: 5\n";
    echo "  - Rendez-vous: 3\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
