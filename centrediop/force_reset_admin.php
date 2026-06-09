<?php
require_once 'config/database.php';
try {
    $pdo = getPDO();
    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // On met à jour l'utilisateur admin s'il existe
    $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = 'admin'");
    $stmt->execute([$new_hash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Mot de passe 'admin' mis à jour avec succès.\n";
    } else {
        echo "⚠️ Aucun utilisateur 'admin' trouvé pour mise à jour. Vérifiez si le login est correct.\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
