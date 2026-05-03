<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== RÉINITIALISATION DE LA BASE DE DONNÉES ===\n\n";

try {
    $conn->beginTransaction();
    
    // 1. Vider les tables dans l'ordre (enfants d'abord)
    echo "1. Vidage des tables...\n";
    $conn->exec("DELETE FROM mouvements_materiel");
    $conn->exec("DELETE FROM file_attente");
    $conn->exec("DELETE FROM paiements");
    $conn->exec("DELETE FROM consultations");
    $conn->exec("DELETE FROM rendez_vous");
    $conn->exec("DELETE FROM dossiers_medicaux");
    $conn->exec("DELETE FROM patients");
    echo "   ✅ Tables vidées\n";
    
    // 2. Réinitialiser les auto-incréments
    $conn->exec("ALTER TABLE patients AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE dossiers_medicaux AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE rendez_vous AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE consultations AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE paiements AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE file_attente AUTO_INCREMENT = 1");
    echo "   ✅ Auto-incréments réinitialisés\n";
    
    // 3. Récupérer les services et médecins
    $services = $conn->query("SELECT id, name FROM services")->fetchAll(PDO::FETCH_KEY_PAIR);
    $medecins = $conn->query("SELECT id, prenom, nom, service_id FROM users WHERE role = 'medecin'")->fetchAll();
    
    echo "\n2. Services disponibles:\n";
    foreach ($services as $id => $name) {
        $prefix = strtolower(preg_replace('/[^a-z]/i', '', $name));
        $prefix = substr($prefix, 0, 6);
        echo "   - ID $id : $name (prefixe: $prefix)\n";
    }
    
    echo "\n3. Médecins disponibles:\n";
    foreach ($medecins as $m) {
        $service_nom = $services[$m['service_id']] ?? 'Inconnu';
        echo "   - Dr. {$m['prenom']} {$m['nom']} - {$service_nom}\n";
    }
    
    // 4. Créer un système de génération de code patient explicite
    echo "\n4. Création de la fonction de génération de code...\n";
    $conn->exec("DROP FUNCTION IF EXISTS generate_patient_code");
    $conn->exec("
        CREATE FUNCTION generate_patient_code(service_id INT, prenom VARCHAR(100), nom VARCHAR(100))
        RETURNS VARCHAR(50) DETERMINISTIC
        BEGIN
            DECLARE service_prefix VARCHAR(10);
            DECLARE year_code VARCHAR(4);
            DECLARE random_num INT;
            DECLARE code VARCHAR(50);
            
            -- Obtenir le préfixe du service
            SELECT CASE 
                WHEN s.name LIKE '%Cardio%' THEN 'CARDIO'
                WHEN s.name LIKE '%Pédia%' THEN 'PEDIA'
                WHEN s.name LIKE '%Odonto%' THEN 'ODONTO'
                WHEN s.name LIKE '%Gynéco%' THEN 'GYNECO'
                WHEN s.name LIKE '%Ophtal%' THEN 'OPHTA'
                WHEN s.name LIKE '%Derma%' THEN 'DERMA'
                WHEN s.name LIKE '%Neuro%' THEN 'NEURO'
                WHEN s.name LIKE '%Radio%' THEN 'RADIO'
                WHEN s.name LIKE '%Labor%' THEN 'LABO'
                ELSE UPPER(SUBSTRING(REPLACE(s.name, ' ', ''), 1, 5))
            END INTO service_prefix
            FROM services s WHERE s.id = service_id;
            
            -- Année sur 2 chiffres
            SET year_code = DATE_FORMAT(NOW(), '%y');
            
            -- Numéro aléatoire
            SET random_num = FLOOR(1000 + RAND() * 9000);
            
            -- Construire le code
            SET code = CONCAT(service_prefix, year_code, random_num);
            
            RETURN code;
        END
    ");
    echo "   ✅ Fonction de génération de code créée\n";
    
    // 5. Créer un trigger pour générer automatiquement le code
    $conn->exec("DROP TRIGGER IF EXISTS before_insert_patient");
    $conn->exec("
        CREATE TRIGGER before_insert_patient
        BEFORE INSERT ON patients
        FOR EACH ROW
        BEGIN
            DECLARE service_id_val INT;
            
            -- Récupérer le service_id depuis le contexte (à définir via une variable utilisateur)
            SET service_id_val = @current_service_id;
            
            -- Générer le code
            IF service_id_val IS NOT NULL THEN
                SET NEW.code_patient_unique = generate_patient_code(service_id_val, NEW.prenom, NEW.nom);
                SET NEW.numero_patient = NEW.code_patient_unique;
            END IF;
        END
    ");
    echo "   ✅ Trigger de génération automatique créé\n";
    
    $conn->commit();
    echo "\n🎉 Base de données réinitialisée avec succès !\n";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
