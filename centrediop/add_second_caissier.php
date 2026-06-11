<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== CRÉATION DU DEUXIÈME CAISSIER ===\n\n";

try {
    // Vérifier si le caissier existe déjà
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute(['caissier2']);
    
    if ($check->fetch()) {
        echo "ℹ️ Le caissier2 existe déjà\n";
    } else {
        // Récupérer le service Caisse (ID 2)
        $service_id = 2;
        
        // Ajouter le deuxième caissier
        $stmt = $conn->prepare("
            INSERT INTO users (
                username, password, role, code_personnel, prenom, nom, 
                service_id, actif, created_at
            ) VALUES (
                :username, :password, :role, :code_personnel, :prenom, :nom,
                :service_id, 1, NOW()
            )
        ");
        
        $stmt->execute([
            ':username' => 'caissier2',
            ':password' => password_hash('caisse456', PASSWORD_DEFAULT),
            ':role' => 'caissier',
            ':code_personnel' => 'CS-003',
            ':prenom' => 'Fatou',
            ':nom' => 'Dieng',
            ':service_id' => $service_id
        ]);
        
        echo "✅ Caissier2 créé avec succès !\n";
    }
    
    // Afficher tous les caissiers
    echo "\n=== LISTE DES CAISSIERS ===\n";
    $caissiers = $conn->query("
        SELECT id, username, prenom, nom, role 
        FROM users 
        WHERE role = 'caissier'
        ORDER BY id
    ")->fetchAll();
    
    foreach ($caissiers as $c) {
        echo "ID: {$c['id']} - {$c['username']} - {$c['prenom']} {$c['nom']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
