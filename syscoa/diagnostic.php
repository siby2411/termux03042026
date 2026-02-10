<?php
// Diagnostic complet
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic SYSCOHADA</title>
    <style>
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Diagnostic SYSCOHADA</h1>";

// 1. Test PHP
echo "<h2>1. Environnement PHP</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Error Reporting: " . ini_get('error_reporting') . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";

// 2. Test fichiers
echo "<h2>2. Fichiers</h2>";
$files = [
    'config.php' => 'Configuration principale',
    'index.php' => 'Point d\'entrée',
    'includes/header.php' => 'En-tête',
    'includes/footer.php' => 'Pied de page',
    'pages/dashboard.php' => 'Tableau de bord'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<span class='success'>✅</span> $desc ($file) - " . $size . " octets<br>";
        
        // Vérifier syntaxe PHP
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $output = shell_exec("php -l $file 2>&1");
            if (strpos($output, 'No syntax errors') !== false) {
                echo "<span class='success'>   Syntaxe OK</span><br>";
            } else {
                echo "<span class='error'>   ERREUR: $output</span><br>";
            }
        }
    } else {
        echo "<span class='error'>❌</span> $desc ($file) - INTROUVABLE<br>";
    }
}

// 3. Test config.php
echo "<h2>3. Configuration</h2>";
if (file_exists('config.php')) {
    require_once 'config.php';
    
    $constants = ['SITE_NAME', 'DEFAULT_MODULE', 'DEFAULT_SUBMODULE', 'COMPANY_NAME', 'SYSCOHADA_MODULES'];
    foreach ($constants as $constant) {
        if (defined($constant)) {
            echo "<span class='success'>✅</span> $constant: " . constant($constant) . "<br>";
        } else {
            echo "<span class='error'>❌</span> $constant: NON DÉFINIE<br>";
        }
    }
    
    // Test modules
    if (defined('SYSCOHADA_MODULES')) {
        $modules = unserialize(SYSCOHADA_MODULES);
        echo "Modules définis: " . count($modules) . "<br>";
    }
} else {
    echo "<span class='error'>❌ config.php introuvable</span><br>";
}

// 4. Test base de données
echo "<h2>4. Base de données</h2>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "123");
    echo "<span class='success'>✅ Connexion MySQL réussie</span><br>";
    
    // Test base sysco_ohada
    $stmt = $pdo->query("SHOW DATABASES LIKE 'sysco_ohada'");
    if ($stmt->rowCount() > 0) {
        echo "<span class='success'>✅ Base sysco_ohada existe</span><br>";
    } else {
        echo "<span class='error'>❌ Base sysco_ohada n'existe pas</span><br>";
    }
} catch (PDOException $e) {
    echo "<span class='error'>❌ Erreur MySQL: " . $e->getMessage() . "</span><br>";
}

// 5. Test session
echo "<h2>5. Session</h2>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
if (isset($_SESSION['user_id'])) {
    echo "Utilisateur connecté: " . ($_SESSION['username'] ?? 'Inconnu') . "<br>";
} else {
    echo "Aucun utilisateur connecté<br>";
}

echo "</body></html>";
