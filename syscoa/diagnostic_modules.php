// Vérifier la configuration de la base de données
echo "<h3>Vérification de la base de données</h3>";
try {
    $conn = new mysqli('127.0.0.1', 'root', '', 'sysco_ohada');  // CORRECTION ICI
    if ($conn->connect_error) {
        echo "<p style='color:red;'>✗ Connexion MySQL échouée: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color:green;'>✓ Connexion MySQL réussie</p>";
        // ... reste du code
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Exception: " . $e->getMessage() . "</p>";
}
