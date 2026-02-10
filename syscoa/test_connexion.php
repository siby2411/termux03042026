<?php
// Test de connexion
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

echo "<h1>Test de session</h1>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Non connecté') . "<br>";
echo "Username: " . ($_SESSION['username'] ?? 'Non connecté') . "<br>";
echo "Full Name: " . ($_SESSION['full_name'] ?? 'Non connecté') . "<br>";

// Tester la connexion
if (isset($_SESSION['user_id'])) {
    echo "<div style='color: green;'>✅ Utilisateur connecté</div>";
    echo "<a href='index.php'>Accéder au tableau de bord</a><br>";
    echo "<a href='logout.php'>Se déconnecter</a>";
} else {
    echo "<div style='color: red;'>❌ Utilisateur non connecté</div>";
    echo "<a href='login.php'>Se connecter</a>";
}
