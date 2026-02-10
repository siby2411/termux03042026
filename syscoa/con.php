<?php
// test_connection.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sysco_ohada;charset=utf8mb4', 'root', '123');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion réussie à la base de données<br>";
    
    // Tester la table users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Table users : " . $result['count'] . " utilisateurs<br>";
    
    // Tester la table comptes_ohada
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM comptes_ohada");
    $result = $stmt->fetch();
    echo "✅ Table comptes_ohada : " . $result['count'] . " comptes<br>";
    
    // Tester la table ecritures
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ecritures");
    $result = $stmt->fetch();
    echo "✅ Table ecritures : " . $result['count'] . " écritures<br>";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
