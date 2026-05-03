<?php
// test_minimal.php - Test sans config
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=gestion_auto", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Connexion directe OK</h1>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    
    echo "<p>Tables trouvées: " . count($tables) . "</p>";
    foreach($tables as $table) {
        echo "<li>" . $table[0] . "</li>";
    }
    
} catch (Exception $e) {
    echo "<h1>Erreur: " . $e->getMessage() . "</h1>";
}
?>
