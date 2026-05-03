<?php
// Version ultra simple
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>SYSCOHADA - Test</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container mt-5'>
        <h1>SYSCOHADA - Test de fonctionnement</h1>";
        
// Test config
if (file_exists('config.php')) {
    require_once 'config.php';
    echo "<div class='alert alert-success'>✅ config.php chargé</div>";
    
    // Afficher menu simple
    if (defined('SYSCOHADA_MODULES')) {
        $modules = unserialize(SYSCOHADA_MODULES);
        echo "<h3>Modules disponibles:</h3>";
        echo "<div class='row'>";
        foreach ($modules as $key => $module) {
            echo "<div class='col-md-3 mb-3'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5><i class='{$module[1]}'></i> {$module[0]}</h5>
                            <a href='?module=$key' class='btn btn-primary'>Accéder</a>
                        </div>
                    </div>
                  </div>";
        }
        echo "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>❌ config.php introuvable</div>";
}

echo "</div></body></html>";
