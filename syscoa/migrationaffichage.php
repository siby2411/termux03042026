<?php
// migration_affichage.php
// À inclure dans votre application

function mettre_a_jour_affichage_syscohada() {
    // Remplacer les anciennes requêtes par les nouvelles
    $remplacements = [
        'soldes_comptes' => 'vue_balance_compatible',
        'ecritures' => 'vue_grand_livre_syscohada', 
        'soldes_gestion' => 'vue_soldes_comptables',
        'operations_comptables' => 'vue_module_comptabilite'
    ];
    
    // Mettre à jour les configurations d'affichage
    foreach ($remplacements as $ancien => $nouveau) {
        // Code pour mettre à jour vos templates/requêtes
    }
    
    return "Affichage SYSCOHADA mis à jour avec succès";
}
?>
