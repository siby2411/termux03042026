<?php
require_once 'config/config.php';
$db = getDB();

echo "<h2>Recréation de tous les caissiers</h2>";

$cashiers_data = [
    ['fatou', 'Fatou Diop', 'fatou@pizzeria.com', '77-123-45-67'],
    ['mamadou', 'Mamadou Diallo', 'mamadou@pizzeria.com', '77-234-56-78'],
    ['aminata', 'Aminata Sow', 'aminata@pizzeria.com', '77-345-67-89'],
    ['ibrahima', 'Ibrahima Ndiaye', 'ibrahima@pizzeria.com', '77-456-78-90']
];

$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "Mot de passe commun: <strong>$password</strong><br>";
echo "Hash utilisé: <code>$hashed_password</code><br><br>";

// Supprimer les anciens
$db->exec("DELETE FROM cashiers WHERE username IN ('fatou', 'mamadou', 'aminata', 'ibrahima')");
echo "✅ Anciens caissiers supprimés<br><br>";

// Insérer les nouveaux
$stmt = $db->prepare("INSERT INTO cashiers (username, password, full_name, email, phone, commission_rate, is_active) VALUES (?, ?, ?, ?, ?, 5, 1)");

foreach ($cashiers_data as $data) {
    $stmt->execute([$data[0], $hashed_password, $data[1], $data[2], $data[3]]);
    echo "✅ Caissier ajouté: {$data[1]} ({$data[0]})<br>";
}

echo "<br><h3>Vérification:</h3>";

$check = $db->query("SELECT id, username, full_name FROM cashiers");
while ($row = $check->fetch()) {
    echo "- {$row['full_name']} ({$row['username']})<br>";
}

echo "<br><a href='cashier_login.php' style='font-size:1.2rem;background:green;color:white;padding:10px;text-decoration:none;border-radius:5px;'>🔐 Aller à la page de connexion</a>";
?>
