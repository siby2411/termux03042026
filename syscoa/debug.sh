#!/bin/bash
# debug_500.sh

echo "=== DÉBOGAGE ERREUR HTTP 500 ==="

echo "1. Vérification des logs Apache..."
sudo tail -20 /var/log/apache2/error.log | grep -A5 -B5 "syscoa"

echo ""
echo "2. Vérification de la syntaxe PHP..."
for file in /var/www/syscoa/*.php; do
    echo "Vérification de $file"
    php -l "$file" 2>/dev/null || echo "❌ Erreur de syntaxe dans $file"
done

echo ""
echo "3. Test de connexion à la base de données..."
sudo tee /var/www/syscoa/test_db_connect.php << 'EOF'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sysco_ohada', 'root', '123');
    echo "✅ Connexion MySQL réussie<br>";
    
    // Tester la requête sur la table users
    $stmt = $pdo->query("SELECT username, password_hash FROM users WHERE username='admin'");
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Utilisateur admin trouvé<br>";
        echo "Mot de passe: " . $user['password_hash'] . "<br>";
    } else {
        echo "❌ Utilisateur admin non trouvé<br>";
    }
} catch (PDOException $e) {
    echo "❌ Erreur PDO: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "<br>";
}
?>
EOF

echo "4. Accès au test de connexion..."
curl -I http://localhost:8080/syscoa/test_db_connect.php 2>/dev/null | head -1

echo ""
echo "5. Vérification des permissions..."
ls -la /var/www/syscoa/*.php | head -5

echo ""
echo "=== INSTRUCTIONS ==="
echo "1. Accédez à: http://192.168.1.33:8080/syscoa/test_db_connect.php"
echo "2. Consultez les logs: sudo tail -f /var/log/apache2/error.log"
echo "3. Si test_db_connect.php fonctionne, le problème est dans login.php"
echo "4. Si test_db_connect.php échoue, le problème est dans la configuration PHP/MySQL"
