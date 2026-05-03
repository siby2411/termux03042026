<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    // Liste des utilisateurs à créer
    $users = [
        ['admin', 'admin123', 'admin', 'Administrateur', 'admin@centrediop.sn'],
        ['dr.fall', 'pediatre123', 'medecin', 'Dr. Aminata Fall', 'dr.fall@centrediop.sn'],
        ['dr.diop', 'medecin123', 'medecin', 'Dr. Moussa Diop', 'dr.diop@centrediop.sn'],
        ['sagefemme1', 'sagefemme123', 'sagefemme', 'Mme Fatou Ndiaye', 'sagefemme@centrediop.sn'],
        ['caissier1', 'caissier123', 'caissier', 'M. Oumar Sow', 'caissier@centrediop.sn'],
        ['secretaire1', 'secret123', 'secretaire', 'Mme Awa Dieng', 'secretaire@centrediop.sn']
    ];
    
    $created = 0;
    $existing = 0;
    
    foreach ($users as $user) {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user[0]]);
        
        if ($stmt->rowCount() == 0) {
            // Créer le nouvel utilisateur
            $hashed = password_hash($user[1], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nom_complet, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user[0], $hashed, $user[2], $user[3], $user[4]]);
            echo "✅ Utilisateur créé : {$user[0]} ({$user[2]})\n";
            $created++;
        } else {
            echo "ℹ️ Utilisateur déjà existant : {$user[0]}\n";
            $existing++;
        }
    }
    
    echo "\n📊 RÉCAPITULATIF :\n";
    echo "  - {$created} nouveaux utilisateurs créés\n";
    echo "  - {$existing} utilisateurs déjà existants\n";
    
    // Afficher tous les utilisateurs
    $stmt = $pdo->query("SELECT id, username, role, nom_complet, email FROM users ORDER BY id");
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 LISTE DES UTILISATEURS DISPONIBLES :\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-20s %-15s %-25s %-25s\n", "ID", "Username", "Rôle", "Nom complet", "Email");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($all_users as $u) {
        printf("%-5d %-20s %-15s %-25s %-25s\n", 
            $u['id'], 
            $u['username'], 
            $u['role'], 
            $u['nom_complet'], 
            $u['email']
        );
    }
    echo str_repeat("-", 80) . "\n";
    
    echo "\n🔑 INFORMATIONS DE CONNEXION :\n";
    echo "  Admin    : admin / admin123\n";
    echo "  Médecin  : dr.fall / pediatre123\n";
    echo "  Médecin  : dr.diop / medecin123\n";
    echo "  Sage-femme : sagefemme1 / sagefemme123\n";
    echo "  Caissier : caissier1 / caissier123\n";
    echo "  Secrétaire : secretaire1 / secret123\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
