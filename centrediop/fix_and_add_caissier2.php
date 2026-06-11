<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== RÉSOLUTION DU PROBLÈME ET AJOUT DU CAISSIER2 ===\n\n";

try {
    // 1. Lister les triggers
    echo "1. Recherche des triggers...\n";
    $triggers = $conn->query("SHOW TRIGGERS")->fetchAll();
    
    $trigger_trouve = false;
    foreach ($triggers as $t) {
        if ($t['Table'] == 'users') {
            echo "   ⚠️ Trigger trouvé sur users: {$t['Trigger']}\n";
            $trigger_trouve = true;
            
            // Sauvegarder le trigger avant suppression
            $backup_file = 'trigger_backup_' . $t['Trigger'] . '.sql';
            file_put_contents($backup_file, "-- Trigger sauvegardé le " . date('Y-m-d H:i:s') . "\n");
            file_put_contents($backup_file, "DROP TRIGGER IF EXISTS {$t['Trigger']};\n", FILE_APPEND);
            
            echo "   ✅ Trigger sauvegardé dans $backup_file\n";
            
            // Supprimer le trigger
            $conn->exec("DROP TRIGGER IF EXISTS {$t['Trigger']}");
            echo "   ✅ Trigger supprimé\n";
        }
    }
    
    if (!$trigger_trouve) {
        echo "   ✅ Aucun trigger sur users trouvé\n";
    }
    
    // 2. Vérifier si caissier2 existe
    echo "\n2. Vérification de l'existence de caissier2...\n";
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute(['caissier2']);
    
    if ($check->fetch()) {
        echo "   ℹ️ Le caissier2 existe déjà\n";
    } else {
        // 3. Générer le hash du mot de passe
        $password = 'caisse456';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        echo "   Hash généré: " . substr($hash, 0, 30) . "...\n";
        
        // 4. Ajouter le caissier2
        echo "3. Ajout du caissier2...\n";
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
            ':password' => $hash,
            ':role' => 'caissier',
            ':code_personnel' => 'CS-003',
            ':prenom' => 'Fatou',
            ':nom' => 'Dieng',
            ':service_id' => 2
        ]);
        
        if ($result) {
            $new_id = $conn->lastInsertId();
            echo "   ✅ Caissier2 créé avec succès !\n";
            echo "   ID: $new_id\n";
            echo "   Username: caissier2\n";
            echo "   Mot de passe: caisse456\n";
        } else {
            echo "   ❌ Échec de la création\n";
        }
    }
    
    // 5. Afficher tous les caissiers
    echo "\n=== LISTE DES CAISSIERS ===\n";
    $caissiers = $conn->query("
        SELECT id, username, prenom, nom, code_personnel
        FROM users 
        WHERE role = 'caissier'
        ORDER BY id
    ")->fetchAll();
    
    foreach ($caissiers as $c) {
        echo "ID: {$c['id']} - {$c['username']} - {$c['prenom']} {$c['nom']} (Code: {$c['code_personnel']})\n";
    }
    
    echo "\n=== INSTRUCTIONS ===\n";
    echo "1. Caissier 1: http://localhost:8000/modules/caisse/index.php (caissier1 / caissier123)\n";
    echo "2. Caissier 2: http://localhost:8000/modules/caisse/index.php (caissier2 / caisse456)\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Détail: " . $e->getTraceAsString() . "\n";
}
?>
