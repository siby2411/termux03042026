<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== TEST DES SESSIONS CAISSIERS ===\n\n";

$caissiers = [
    ['id' => 2, 'nom' => 'Oumar Sow', 'username' => 'caissier1', 'mdp' => 'caissier123'],
    ['id' => 45, 'nom' => 'Fatou Dieng', 'username' => 'caissier2', 'mdp' => 'caisse456']
];

foreach ($caissiers as $c) {
    echo "👤 {$c['nom']} (ID: {$c['id']})\n";
    
    // Simuler une session
    $_SESSION['user_id'] = $c['id'];
    
    // Statistiques personnelles
    $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE created_by = ?");
    $stmt->execute([$c['id']]);
    echo "   Patients créés: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*), COALESCE(SUM(montant_total), 0) FROM paiements WHERE caissier_id = ?");
    $stmt->execute([$c['id']]);
    list($nb_paiements, $montant) = $stmt->fetch();
    echo "   Paiements: $nb_paiements (Total: " . number_format($montant, 0, ',', ' ') . " FCFA)\n";
    echo "\n";
}

echo "=== POUR TESTER EN NAVIGATEUR ===\n";
echo "Caissier 1: http://localhost:8000/modules/caisse/index.php\n";
echo "Caissier 2: http://localhost:8000/modules/caisse/index.php\n";
?>
