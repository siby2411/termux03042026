<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "=== CRÉATION DE DEUX CAISSIERS ===\n\n";

// Vérifier si les caissiers existent déjà
$check = $db->query("SELECT username FROM users WHERE role = 'caissier'");
$existing = $check->fetchAll();

if (count($existing) >= 2) {
    echo "✅ Les deux caissiers existent déjà :\n";
    foreach ($existing as $c) {
        echo "   - " . $c['username'] . "\n";
    }
    exit;
}

// Caissier 1: Alioune Diop
$password1 = password_hash('caissier123', PASSWORD_DEFAULT);
$stmt1 = $db->prepare("
    INSERT INTO users (nom, prenom, username, password, role, service_id, created_at)
    VALUES (?, ?, ?, ?, 'caissier', 2, NOW())
");

try {
    $stmt1->execute(['Diop', 'Alioune', 'caissier.diop', $password1, 'caissier', 2]);
    echo "✅ Caissier 1 créé: Alioune Diop (caissier.diop / caissier123)\n";
} catch (Exception $e) {
    echo "⚠️ Caissier 1 existe peut-être déjà\n";
}

// Caissier 2: Fatou Ndiaye
$password2 = password_hash('caisse2026', PASSWORD_DEFAULT);
$stmt2 = $db->prepare("
    INSERT INTO users (nom, prenom, username, password, role, service_id, created_at)
    VALUES (?, ?, ?, ?, 'caissier', 2, NOW())
");

try {
    $stmt2->execute(['Ndiaye', 'Fatou', 'caissier.ndiaye', $password2, 'caissier', 2]);
    echo "✅ Caissier 2 créé: Fatou Ndiaye (caissier.ndiaye / caisse2026)\n";
} catch (Exception $e) {
    echo "⚠️ Caissier 2 existe peut-être déjà\n";
}

// Afficher la liste des caissiers
echo "\n=== LISTE DES CAISSIERS ===\n";
$caissiers = $db->query("SELECT id, nom, prenom, username FROM users WHERE role = 'caissier'");
while ($c = $caissiers->fetch()) {
    echo "ID {$c['id']}: {$c['prenom']} {$c['nom']} ({$c['username']})\n";
}
?>
