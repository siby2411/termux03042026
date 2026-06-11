<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

echo "=== TEST DE CONNEXION BDD ===\n\n";

$db = getDB();
if ($db) {
    echo "✅ Connexion BDD réussie\n";
    
    // Vérifier les médecins
    $stmt = $db->query("SELECT id, nom, prenom, username, role FROM users WHERE role = 'medecin'");
    $medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nMédecins trouvés:\n";
    foreach ($medecins as $m) {
        echo "  - Dr. " . $m['prenom'] . " " . $m['nom'] . " (username: " . $m['username'] . ")\n";
    }
    
    // Vérifier les patients
    $stmt = $db->query("SELECT id, nom, prenom, code_patient_unique FROM patients");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nPatients trouvés:\n";
    foreach ($patients as $p) {
        echo "  - " . $p['prenom'] . " " . $p['nom'] . " (code: " . $p['code_patient_unique'] . ")\n";
    }
    
} else {
    echo "❌ Échec connexion BDD\n";
}
?>
