<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>Test de connexion à la base de données</h2>";

try {
    // Test avec la classe Database
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color:green'>✅ Connexion avec Database OK</p>";
    
    // Test avec la fonction getPDO
    $pdo = getPDO();
    echo "<p style='color:green'>✅ Fonction getPDO OK</p>";
    
    // Lister les tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Tables trouvées:</h3>";
    echo "<ul>";
    foreach($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Compter les patients
    $count = $conn->query("SELECT COUNT(*) as total FROM patients")->fetch();
    echo "<p>Total patients: <strong>{$count['total']}</strong></p>";
    
} catch(Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<a href='index.php'>Retour à l'accueil</a>";
?>
