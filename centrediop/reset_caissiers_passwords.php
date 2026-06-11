<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "=== RÉINITIALISATION DES MOTS DE PASSE DES CAISSIERS ===\n\n";

// Liste des caissiers avec leurs nouveaux mots de passe
$caissiers = [
    ['username' => 'caissier.diop', 'password' => 'caissier123', 'nom' => 'Diop', 'prenom' => 'Oumar'],
    ['username' => 'caissier.ndiaye', 'password' => 'caisse2026', 'nom' => 'Ndiaye', 'prenom' => 'Fatou'],
    ['username' => 'caissier.fall', 'password' => 'caisse123', 'nom' => 'Fall', 'prenom' => 'Aminata'], // Au cas où
];

foreach ($caissiers as $c) {
    // Vérifier si le caissier existe
    $check = $db->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$c['username']]);
    $user = $check->fetch();
    
    if ($user) {
        // Mettre à jour le mot de passe
        $hashed = password_hash($c['password'], PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update->execute([$hashed, $c['username']]);
        
        echo "✅ Mot de passe réinitialisé pour {$c['prenom']} {$c['nom']} ({$c['username']})\n";
        echo "   Nouveau mot de passe: {$c['password']}\n";
        
        // Vérifier
        $verify = $db->prepare("SELECT password FROM users WHERE username = ?");
        $verify->execute([$c['username']]);
        $hash = $verify->fetchColumn();
        
        if (password_verify($c['password'], $hash)) {
            echo "   ✅ Vérification: mot de passe correct\n\n";
        } else {
            echo "   ❌ Erreur de vérification\n\n";
        }
    } else {
        echo "⚠️ Caissier {$c['username']} non trouvé, création...\n";
        
        // Créer le caissier
        $hashed = password_hash($c['password'], PASSWORD_DEFAULT);
        $insert = $db->prepare("
            INSERT INTO users (nom, prenom, username, password, role, service_id, created_at)
            VALUES (?, ?, ?, ?, 'caissier', 2, NOW())
        ");
        $insert->execute([$c['nom'], $c['prenom'], $c['username'], $hashed]);
        echo "✅ Caissier créé: {$c['prenom']} {$c['nom']} ({$c['username']} / {$c['password']})\n\n";
    }
}

// Afficher tous les caissiers avec leurs infos
echo "\n=== LISTE DES CAISSIERS ===\n";
$caissiers = $db->query("SELECT id, nom, prenom, username FROM users WHERE role = 'caissier' ORDER BY id");

while ($c = $caissiers->fetch()) {
    echo "ID {$c['id']}: {$c['prenom']} {$c['nom']} - username: {$c['username']}\n";
}

echo "\n✅ Réinitialisation terminée!\n";
?>
