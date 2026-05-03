<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== CRÉATION DU DEUXIÈME CAISSIER ===\n\n";

try {
    // Désactiver temporairement les triggers si nécessaire
    // $conn->exec("SET FOREIGN_KEY_CHECKS=0");
    
    // Vérifier si le caissier existe déjà
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute(['caissier2']);
    
    if ($check->fetch()) {
        echo "ℹ️ Le caissier2 existe déjà\n";
        
        // Afficher ses informations
        $info = $conn->prepare("SELECT id, username, prenom, nom FROM users WHERE username = ?");
        $info->execute(['caissier2']);
        $user = $info->fetch();
        echo "   ID: {$user['id']} - {$user['prenom']} {$user['nom']}\n";
        
    } else {
        // Récupérer le service Caisse (ID 2)
        $service_id = 2;
        
        // Générer un code personnel unique
        $code_personnel = 'CS-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Ajouter le deuxième caissier sans utiliser de colonne 'next_num'
        $stmt = $conn->prepare("
            INSERT INTO users (
                username, password, role, code_personnel, prenom, nom, 
                service_id, actif, created_at
            ) VALUES (
                :username, :password, :role, :code_personnel, :prenom, :nom,
                :service_id, 1, NOW()
            )
        ");
        
        $result = $stmt->execute([
            ':username' => 'caissier2',
            ':password' => password_hash('caisse456', PASSWORD_DEFAULT),
            ':role' => 'caissier',
            ':code_personnel' => $code_personnel,
            ':prenom' => 'Fatou',
            ':nom' => 'Dieng',
            ':service_id' => $service_id
        ]);
        
        if ($result) {
            $new_id = $conn->lastInsertId();
            echo "✅ Caissier2 créé avec succès !\n";
            echo "   ID: $new_id\n";
            echo "   Username: caissier2\n";
            echo "   Mot de passe: caisse456\n";
            echo "   Code personnel: $code_personnel\n";
        } else {
            echo "❌ Échec de la création\n";
        }
    }
    
    // Afficher tous les caissiers
    echo "\n=== LISTE DES CAISSIERS ===\n";
    $caissiers = $conn->query("
        SELECT id, username, prenom, nom, code_personnel, role 
        FROM users 
        WHERE role = 'caissier'
        ORDER BY id
    ")->fetchAll();
    
    foreach ($caissiers as $c) {
        echo "ID: {$c['id']} - {$c['username']} - {$c['prenom']} {$c['nom']} (Code: {$c['code_personnel']})\n";
    }
    
    // Réactiver les triggers si nécessaire
    // $conn->exec("SET FOREIGN_KEY_CHECKS=1");
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Détail: " . $e->getTraceAsString() . "\n";
}
?>
