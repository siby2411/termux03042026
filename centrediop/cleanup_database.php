<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== NETTOYAGE COMPLET DE LA BASE ===\n\n";

try {
    $conn->beginTransaction();
    
    // 1. Supprimer tous les doublons de Siby Momo (garder le plus récent)
    echo "1. Suppression des doublons de Siby Momo...\n";
    
    // Trouver le dernier ID de Siby Momo
    $last_siby = $conn->query("
        SELECT id FROM patients 
        WHERE nom = 'Momo' AND prenom = 'Siby' 
        ORDER BY id DESC LIMIT 1
    ")->fetchColumn();
    
    if ($last_siby) {
        // Supprimer les autres Siby Momo
        $conn->exec("
            DELETE FROM file_attente 
            WHERE patient_id IN (
                SELECT id FROM patients 
                WHERE nom = 'Momo' AND prenom = 'Siby' 
                AND id != $last_siby
            )
        ");
        
        $conn->exec("
            DELETE FROM patients 
            WHERE nom = 'Momo' AND prenom = 'Siby' 
            AND id != $last_siby
        ");
        
        echo "   ✅ Doublons supprimés, gardé ID: $last_siby\n";
    }
    
    // 2. Supprimer tous les patients avec codes PAT- (sauf ceux qu'on veut garder)
    echo "\n2. Suppression des patients avec codes non explicites...\n";
    
    // Garder seulement les patients avec codes explicites (PEDIA, CARDIO, etc.)
    $conn->exec("
        DELETE FROM file_attente 
        WHERE patient_id IN (
            SELECT id FROM patients 
            WHERE code_patient_unique LIKE 'PAT-%'
        )
    ");
    
    $conn->exec("
        DELETE FROM patients 
        WHERE code_patient_unique LIKE 'PAT-%'
    ");
    
    echo "   ✅ Patients avec codes PAT- supprimés\n";
    
    // 3. Réaffecter les bons services pour Awa Ndiaye
    echo "\n3. Correction des services...\n";
    
    // Mettre à jour la file d'attente pour Awa Ndiaye (ID 1) vers Pédiatrie
    $conn->exec("
        UPDATE file_attente 
        SET service_id = 3 
        WHERE patient_id = 1
    ");
    echo "   ✅ Awa Ndiaye déplacée en Pédiatrie\n";
    
    // 4. Re-générer les codes explicites pour tous les patients
    echo "\n4. Régénération des codes patients explicites...\n";
    
    // Récupérer tous les patients
    $patients = $conn->query("
        SELECT p.id, p.prenom, p.nom, f.service_id, s.name as service_nom
        FROM patients p
        JOIN file_attente f ON p.id = f.patient_id
        JOIN services s ON f.service_id = s.id
    ")->fetchAll();
    
    foreach ($patients as $p) {
        // Générer le préfixe selon le service
        $service = $p['service_nom'];
        $prefix = '';
        
        if (strpos($service, 'Cardio') !== false) $prefix = 'CARDIO';
        elseif (strpos($service, 'Pédia') !== false) $prefix = 'PEDIA';
        elseif (strpos($service, 'Odonto') !== false) $prefix = 'ODONTO';
        elseif (strpos($service, 'Gynéco') !== false) $prefix = 'GYNECO';
        elseif (strpos($service, 'Ophtal') !== false) $prefix = 'OPHTA';
        elseif (strpos($service, 'Derma') !== false) $prefix = 'DERMA';
        elseif (strpos($service, 'Neuro') !== false) $prefix = 'NEURO';
        elseif (strpos($service, 'Radio') !== false) $prefix = 'RADIO';
        elseif (strpos($service, 'Labor') !== false) $prefix = 'LABO';
        elseif (strpos($service, 'Pharma') !== false) $prefix = 'PHARMA';
        else $prefix = 'MEDEC';
        
        // Année sur 2 chiffres
        $year = date('y');
        
        // Numéro unique (basé sur l'ID)
        $num = str_pad($p['id'], 4, '0', STR_PAD_LEFT);
        
        $new_code = $prefix . $year . $num;
        
        // Mettre à jour
        $conn->prepare("UPDATE patients SET code_patient_unique = ?, numero_patient = ? WHERE id = ?")
             ->execute([$new_code, $new_code, $p['id']]);
        
        echo "   ✅ {$p['prenom']} {$p['nom']} -> $new_code\n";
    }
    
    // 5. Réinitialiser les tokens de file d'attente
    echo "\n5. Régénération des tokens...\n";
    
    $files = $conn->query("
        SELECT f.id, f.patient_id, p.prenom, p.nom, s.name as service_nom
        FROM file_attente f
        JOIN patients p ON f.patient_id = p.id
        JOIN services s ON f.service_id = s.id
        WHERE f.statut = 'en_attente'
    ")->fetchAll();
    
    foreach ($files as $f) {
        // Générer un token explicite
        $service_prefix = '';
        if (strpos($f['service_nom'], 'Cardio') !== false) $service_prefix = 'CAR';
        elseif (strpos($f['service_nom'], 'Pédia') !== false) $service_prefix = 'PED';
        elseif (strpos($f['service_nom'], 'Odonto') !== false) $service_prefix = 'ODO';
        elseif (strpos($f['service_nom'], 'Gynéco') !== false) $service_prefix = 'GYN';
        elseif (strpos($f['service_nom'], 'Neuro') !== false) $service_prefix = 'NEU';
        else $service_prefix = 'MED';
        
        $date = date('ymd');
        $num = str_pad($f['patient_id'], 3, '0', STR_PAD_LEFT);
        $new_token = $service_prefix . $date . $num;
        
        $conn->prepare("UPDATE file_attente SET token = ? WHERE id = ?")
             ->execute([$new_token, $f['id']]);
        
        echo "   ✅ {$f['prenom']} {$f['nom']} -> $new_token\n";
    }
    
    $conn->commit();
    
    echo "\n🎉 Nettoyage terminé !\n";
    echo "\n=== ÉTAT FINAL ===\n";
    
    $result = $conn->query("
        SELECT p.id, p.prenom, p.nom, p.code_patient_unique, 
               s.name as service_nom, f.token, f.statut
        FROM patients p
        JOIN file_attente f ON p.id = f.patient_id
        JOIN services s ON f.service_id = s.id
        WHERE f.statut = 'en_attente'
        ORDER BY s.name, p.nom
    ")->fetchAll();
    
    foreach ($result as $r) {
        echo "\n{$r['service_nom']}:\n";
        echo "   👤 {$r['prenom']} {$r['nom']}\n";
        echo "   🆔 Code: {$r['code_patient_unique']}\n";
        echo "   🎫 Token: {$r['token']}\n";
    }
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
