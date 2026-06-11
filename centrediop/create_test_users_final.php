<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    // Récupérer les services disponibles
    $stmt = $pdo->query("SELECT id, nom FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($services)) {
        // Créer des services par défaut si aucun n'existe
        $default_services = [
            ['Pédiatrie', 'Service de pédiatrie'],
            ['Gynécologie', 'Service de gynécologie'],
            ['Consultation générale', 'Consultations générales'],
            ['Administration', 'Service administratif'],
            ['Caisse', 'Service de caisse']
        ];
        
        foreach ($default_services as $s) {
            $stmt = $pdo->prepare("INSERT INTO services (nom, description) VALUES (?, ?)");
            $stmt->execute([$s[0], $s[1]]);
        }
        
        // Recharger les services
        $stmt = $pdo->query("SELECT id, nom FROM services");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✅ Services par défaut créés\n";
    }
    
    // Créer un mapping service par rôle
    $service_map = [];
    foreach ($services as $s) {
        if (strpos($s['nom'], 'Pédiatrie') !== false) $service_map['medecin'] = $s['id'];
        if (strpos($s['nom'], 'Gynécologie') !== false) $service_map['sagefemme'] = $s['id'];
        if (strpos($s['nom'], 'Administration') !== false) $service_map['admin'] = $s['id'];
        if (strpos($s['nom'], 'Caisse') !== false) $service_map['caissier'] = $s['id'];
        if (strpos($s['nom'], 'Consultation') !== false) $service_map['secretaire'] = $s['id'];
    }
    
    // Prendre le premier service disponible comme fallback
    $default_service_id = $services[0]['id'];
    
    // Liste des utilisateurs à créer avec leur service_id
    $users = [
        ['admin', 'admin123', 'admin', 'Administrateur', 'admin@centrediop.sn', $service_map['admin'] ?? $default_service_id],
        ['dr.fall', 'pediatre123', 'medecin', 'Dr. Aminata Fall', 'dr.fall@centrediop.sn', $service_map['medecin'] ?? $default_service_id],
        ['dr.diop', 'medecin123', 'medecin', 'Dr. Moussa Diop', 'dr.diop@centrediop.sn', $service_map['medecin'] ?? $default_service_id],
        ['sagefemme1', 'sagefemme123', 'sagefemme', 'Mme Fatou Ndiaye', 'sagefemme@centrediop.sn', $service_map['sagefemme'] ?? $default_service_id],
        ['caissier1', 'caissier123', 'caissier', 'M. Oumar Sow', 'caissier@centrediop.sn', $service_map['caissier'] ?? $default_service_id],
        ['secretaire1', 'secret123', 'secretaire', 'Mme Awa Dieng', 'secretaire@centrediop.sn', $service_map['secretaire'] ?? $default_service_id]
    ];
    
    $created = 0;
    $existing = 0;
    
    foreach ($users as $user) {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user[0]]);
        
        if ($stmt->rowCount() == 0) {
            // Créer le nouvel utilisateur avec service_id
            $hashed = password_hash($user[1], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nom_complet, email, service_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user[0], $hashed, $user[2], $user[3], $user[4], $user[5]]);
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
    
    // Afficher tous les utilisateurs avec leur service
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.role, u.nom_complet, u.email, s.nom as service_nom 
        FROM users u 
        LEFT JOIN services s ON u.service_id = s.id 
        ORDER BY u.id
    ");
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 LISTE DES UTILISATEURS DISPONIBLES :\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-5s %-20s %-15s %-25s %-20s %-15s\n", "ID", "Username", "Rôle", "Nom complet", "Email", "Service");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($all_users as $u) {
        printf("%-5d %-20s %-15s %-25s %-20s %-15s\n", 
            $u['id'], 
            $u['username'], 
            $u['role'], 
            $u['nom_complet'], 
            $u['email'], 
            $u['service_nom'] ?? 'N/A'
        );
    }
    echo str_repeat("-", 100) . "\n";
    
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
