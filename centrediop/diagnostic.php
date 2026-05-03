<?php
echo "=== DIAGNOSTIC DU SYSTÈME ===\n\n";

// Vérifier si le serveur est accessible
$host = 'localhost';
$port = 8000;
$connection = @fsockopen($host, $port, $errno, $errstr, 5);

if (is_resource($connection)) {
    echo "✅ Serveur PHP actif sur http://$host:$port\n";
    fclose($connection);
} else {
    echo "❌ Serveur PHP non accessible: $errstr ($errno)\n";
    echo "   Exécutez: php -S localhost:8000\n";
}

// Vérifier les fichiers
echo "\n=== VÉRIFICATION DES FICHIERS ===\n";
$files = [
    'modules/caisse/dashboard.php',
    'modules/caisse/paiement_traitement.php',
    'modules/caisse/ajout_patient.php',
    'modules/caisse/etat_journalier.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe (" . filesize($file) . " octets)\n";
    } else {
        echo "❌ $file n'existe pas\n";
    }
}

// Vérifier la base de données
echo "\n=== VÉRIFICATION DE LA BASE DE DONNÉES ===\n";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Connexion BDD réussie\n";
    
    // Compter les patients
    $count = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    echo "   Patients: $count\n";
    
    // Vérifier le patient PAT-000003
    $stmt = $db->prepare("SELECT * FROM patients WHERE code_patient_unique = ?");
    $stmt->execute(['PAT-000003']);
    $patient = $stmt->fetch();
    if ($patient) {
        echo "✅ Patient PAT-000003 trouvé: {$patient['prenom']} {$patient['nom']}\n";
    } else {
        echo "❌ Patient PAT-000003 non trouvé\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur BDD: " . $e->getMessage() . "\n";
}

echo "\n=== INSTRUCTIONS ===\n";
echo "1. Exécutez: php -S localhost:8000\n";
echo "2. Allez sur: http://localhost:8000/modules/caisse/dashboard.php\n";
echo "3. Connectez-vous avec: caissier.diop / caissier123\n";
echo "4. Utilisez la recherche en bas à gauche avec le code PAT-000003\n";
?>
