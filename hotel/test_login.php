<?php
require_once 'includes/db.php';

$pdo = getPDO();

echo "<h2>🔐 Test de connexion OMEGA Hôtel</h2>";

// Vérifier l'utilisateur admin
$stmt = $pdo->prepare("SELECT * FROM utilisateurs_hotel WHERE username = 'admin'");
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    echo "<p style='color:green'>✅ Utilisateur admin trouvé !</p>";
    echo "<p>Hash stocké: <code>" . $user['password'] . "</code></p>";
    
    // Tester la vérification du mot de passe
    $test_password = 'admin123';
    if (password_verify($test_password, $user['password'])) {
        echo "<p style='color:green; font-weight:bold'>✅ Le mot de passe 'admin123' est CORRECT !</p>";
        echo "<p>Vous pouvez vous connecter avec :<br><strong>admin / admin123</strong></p>";
    } else {
        echo "<p style='color:red'>❌ Le mot de passe 'admin123' ne correspond pas.</p>";
        
        // Proposer de recréer le mot de passe
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "<p>Pour corriger, exécutez la commande SQL suivante :</p>";
        echo "<pre>UPDATE utilisateurs_hotel SET password = '$new_hash' WHERE username = 'admin';</pre>";
    }
} else {
    echo "<p style='color:red'>❌ Utilisateur admin non trouvé !</p>";
    echo "<p>Créez-le avec :</p>";
    echo "<pre>INSERT INTO utilisateurs_hotel (username, password, role) VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin');</pre>";
}

// Afficher les tables disponibles
echo "<h3>📊 Tables disponibles :</h3>";
$tables = $pdo->query("SHOW TABLES")->fetchAll();
echo "<ul>";
foreach ($tables as $table) {
    $table_name = implode('', $table);
    $count = $pdo->query("SELECT COUNT(*) FROM $table_name")->fetchColumn();
    echo "<li><strong>$table_name</strong> ($count enregistrements)</li>";
}
echo "</ul>";
?>
