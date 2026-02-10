<?php
include 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "✅ Connexion à la base 'clinique' réussie!<br>";
    
    // Test des tables
    $query = "SHOW TABLES";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tables dans la base:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch(PDOException $exception) {
    echo "❌ Erreur de connexion: " . $exception->getMessage();
}
?>
