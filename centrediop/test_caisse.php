<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['user_id'] = 2; // ID de caissier1
$_SESSION['user_role'] = 'caissier';
$_SESSION['user_name'] = 'Oumar Sow';

echo "<h2>Test de session</h2>";
echo "Session user_id: " . $_SESSION['user_id'] . "<br>";
echo "Session user_role: " . $_SESSION['user_role'] . "<br>";

require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color:green'>✅ Connexion DB OK</p>";
    
    // Test requête
    $result = $conn->query("SELECT COUNT(*) as count FROM services")->fetch();
    echo "Nombre de services: " . $result['count'] . "<br>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<p><a href='modules/caisse/index.php'>Aller à la caisse</a></p>";
?>
