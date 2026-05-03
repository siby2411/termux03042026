<?php
require_once 'config/database.php';

try {
    $pdo = getPDO();
    
    // Détecter les colonnes de la table patients
    $stmt = $pdo->query("DESCRIBE patients");
    $patient_columns = $stmt->fetchAll();
    
    $first_name_col = null;
    $last_name_col = null;
    $birth_date_col = null;
    
    foreach ($patient_columns as $col) {
        $field = $col['Field'];
        if (strpos($field, 'prenom') !== false || strpos($field, 'first') !== false) {
            $first_name_col = $field;
        } elseif (strpos($field, 'nom') !== false || strpos($field, 'last') !== false) {
            $last_name_col = $field;
        } elseif (strpos($field, 'naissance') !== false || strpos($field, 'birth') !== false) {
            $birth_date_col = $field;
        }
    }
    
    // Si non trouvé, utiliser des valeurs par défaut
    $first_name_col = $first_name_col ?? 'prenom';
    $last_name_col = $last_name_col ?? 'nom';
    $birth_date_col = $birth_date_col ?? 'date_naissance';
    
    echo "Colonnes détectées pour patients:\n";
    echo "  - Prénom: $first_name_col\n";
    echo "  - Nom: $last_name_col\n";
    echo "  - Date naissance: $birth_date_col\n";
    
    // Lire le fichier functions.php actuel
    $functions_content = file_get_contents('includes/functions.php');
    
    // Remplacer les noms de colonnes
    $functions_content = str_replace(
        ['p.first_name', 'p.last_name', 'p.date_naissance'],
        ["p.$first_name_col", "p.$last_name_col", "p.$birth_date_col"],
        $functions_content
    );
    
    // Sauvegarder le fichier modifié
    file_put_contents('includes/functions.php', $functions_content);
    
    echo "✅ Fichier functions.php mis à jour avec les bonnes colonnes !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
