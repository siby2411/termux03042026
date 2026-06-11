<?php
require_once 'config/database.php';

try {
    $pdo = getPDO();
    
    // Désactiver les contraintes de clés étrangères temporairement pour vider les tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Liste des tables à vider
    $tables = ['paiements', 'consultations', 'rendez_vous', 'patients', 'file_attente'];

    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
        echo "Table $table vidée avec succès.\n";
    }

    // Réactiver les contraintes
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\nBase de données réinitialisée. Vous pouvez maintenant lancer vos démonstrations !\n";

} catch (Exception $e) {
    echo "Erreur lors de la réinitialisation : " . $e->getMessage();
}
?>
