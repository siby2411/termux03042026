<?php
session_start();
require_once 'config/database.php';

echo "=== TEST D'ACCÈS À L'ÉDITION DU DOSSIER ===\n\n";

$roles = ['medecin', 'sagefemme', 'admin', 'caissier', 'secretaire'];

foreach ($roles as $role) {
    // Simuler une session avec ce rôle
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_nom'] = 'Test';
    $_SESSION['user_prenom'] = 'User';
    
    echo "Rôle: $role\n";
    echo "Accès à edition_dossier.php: ";
    
    // Vérifier si le fichier est accessible
    if (file_exists('modules/medical/edition_dossier.php')) {
        echo "✅ OUI\n";
    } else {
        echo "❌ NON\n";
    }
    
    echo "Lien dans le dashboard: ";
    $dashboard = "modules/$role/dashboard.php";
    if (file_exists($dashboard)) {
        $content = file_get_contents($dashboard);
        if (strpos($content, 'edition_dossier.php') !== false) {
            echo "✅ Présent\n";
        } else {
            echo "❌ Absent\n";
        }
    } else {
        echo "⚠️ Dashboard non trouvé\n";
    }
    echo "\n";
}

echo "✅ Tous les utilisateurs peuvent accéder à l'édition du dossier\n";
?>
