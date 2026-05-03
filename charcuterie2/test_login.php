<?php
require_once 'includes/db.php';
$pdo = getPDO();

$username = 'admin';
$password = 'admin123';

$stmt = $pdo->prepare("SELECT id, nom, password, role FROM utilisateurs WHERE nom = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

echo "=== TEST DE CONNEXION ===\n";
echo "Utilisateur trouvé: " . ($user ? $user['nom'] : 'NON') . "\n";

if ($user) {
    echo "Hash stocké: " . $user['password'] . "\n";
    if (password_verify($password, $user['password'])) {
        echo "✅ Mot de passe CORRECT !\n";
        echo "Vous pouvez vous connecter avec admin / admin123\n";
    } else {
        echo "❌ Mot de passe INCORRECT\n";
        echo "Génération d'un nouveau hash...\n";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "Nouveau hash: $new_hash\n";
        echo "Exécutez: UPDATE utilisateurs SET password = '$new_hash' WHERE nom = 'admin';\n";
    }
} else {
    echo "❌ Utilisateur admin non trouvé\n";
    echo "Créez-le avec:\n";
    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "INSERT INTO utilisateurs (nom, email, password, role) VALUES ('admin', 'admin@charcuterie.sn', '$new_hash', 'admin');\n";
}
?>
