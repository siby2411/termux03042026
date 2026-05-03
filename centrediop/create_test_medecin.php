<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Vérifier si le médecin existe déjà
$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute(['dr.fall']);
$exists = $stmt->fetch();

if (!$exists) {
    // Créer le médecin
    $password = password_hash('pediatre123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO users (nom, prenom, username, password, role, service_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        'Fall',
        'Aminata',
        'dr.fall',
        $password,
        'medecin',
        1 // Service de pédiatrie
    ]);
    
    if ($result) {
        echo "✅ Médecin créé avec succès\n";
        echo "   Username: dr.fall\n";
        echo "   Password: pediatre123\n";
    } else {
        echo "❌ Erreur lors de la création\n";
    }
} else {
    echo "✅ Le médecin dr.fall existe déjà\n";
}

// Lister tous les médecins
$stmt = $db->query("SELECT id, nom, prenom, username FROM users WHERE role = 'medecin'");
$medecins = $stmt->fetchAll();

echo "\nListe des médecins:\n";
foreach ($medecins as $m) {
    echo "  - Dr. " . $m['prenom'] . " " . $m['nom'] . " (username: " . $m['username'] . ")\n";
}
?>
