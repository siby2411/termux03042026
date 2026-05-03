<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "<h2>Structure des tables</h2>";
    
    // Table services
    echo "<h3>Table: services</h3>";
    $services_cols = $pdo->query("DESCRIBE services")->fetchAll();
    echo "<ul>";
    foreach ($services_cols as $col) {
        echo "<li>{$col['Field']} - {$col['Type']}</li>";
    }
    echo "</ul>";
    
    // Table actes_medicaux
    echo "<h3>Table: actes_medicaux</h3>";
    $actes_cols = $pdo->query("DESCRIBE actes_medicaux")->fetchAll();
    echo "<ul>";
    foreach ($actes_cols as $col) {
        echo "<li>{$col['Field']} - {$col['Type']}</li>";
    }
    echo "</ul>";
    
    // Table file_attente
    echo "<h3>Table: file_attente</h3>";
    $file_cols = $pdo->query("DESCRIBE file_attente")->fetchAll();
    echo "<ul>";
    foreach ($file_cols as $col) {
        echo "<li>{$col['Field']} - {$col['Type']}</li>";
    }
    echo "</ul>";
    
    // Table paiements
    echo "<h3>Table: paiements</h3>";
    $paiements_cols = $pdo->query("DESCRIBE paiements")->fetchAll();
    echo "<ul>";
    foreach ($paiements_cols as $col) {
        echo "<li>{$col['Field']} - {$col['Type']}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
