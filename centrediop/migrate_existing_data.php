<?php
require_once 'config/database.php';

try {
    $pdo = getPDO();
    
    echo "Migration des données existantes...\n";
    
    // Vérifier s'il y avait d'anciennes tables
    $old_tables = ['patients', 'users', 'consultations', 'queue', 'services'];
    $has_old_data = false;
    
    foreach ($old_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $has_old_data = true;
                echo "  - Table $table: $count enregistrements trouvés\n";
            }
        } catch (Exception $e) {
            // Table n'existe pas ou est vide
        }
    }
    
    if ($has_old_data) {
        echo "\n⚠️  Des données existantes ont été trouvées mais les tables ont été recréées.\n";
        echo "Les anciennes données ne sont pas automatiquement migrées.\n";
        echo "Voulez-vous réimporter les données depuis une sauvegarde ? (y/n) ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) == 'y') {
            // Rechercher des fichiers de sauvegarde
            $backups = glob("backup_*.sql");
            if (!empty($backups)) {
                echo "Sauvegardes disponibles:\n";
                foreach ($backups as $i => $b) {
                    echo "  [$i] $b\n";
                }
                echo "Choisissez le numéro de la sauvegarde à restaurer: ";
                $choice = trim(fgets($handle));
                if (isset($backups[$choice])) {
                    system("mariadb -u root -p centrediop < " . $backups[$choice]);
                    echo "✅ Restauration effectuée\n";
                }
            } else {
                echo "Aucune sauvegarde trouvée.\n";
            }
        }
    } else {
        echo "✅ Base de données propre, prête pour l'installation.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
