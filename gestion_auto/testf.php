<?php
require_once 'config.php';

echo "<h1>Test Final</h1>";

try {
    // Test 1: Singleton
    $db1 = Database::getInstance();
    echo "<p style='color: green;'>✓ Singleton OK</p>";
    
    // Test 2: Connection
    $pdo = $db1->getConnection();
    echo "<p style='color: green;'>✓ Connection OK</p>";
    
    // Test 3: Requête
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✓ Requête OK: " . $result['test'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erreur: " . $e->getMessage() . "</p>";
}
?>
