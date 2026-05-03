<?php
/**
 * Fichier de Fonctions Utilitaires pour l'application Mutuelle & Crédit
 */

/**
 * Récupère les données KPI de Dépôts/Retraits pour le tableau de bord.
 * @param mysqli $conn La connexion à la base de données.
 * @return array Données KPI (TotalDepots, TotalRetraits, FluxNet).
 */
function getRatioData($conn) {
    // Structure de retour par défaut (IMPORTANT pour éviter les erreurs d'indice)
    $kpi_data = [
        'Deposits' => 0.00,
        'Withdrawals' => 0.00,
        'NetFlow' => 0.00
    ];

    // Calcul sur les 30 derniers jours
    $start_date = date('Y-m-d', strtotime('-30 days'));
    $end_date = date('Y-m-d');
    
    // Tentative d'appel à la procédure stockée
    // ATTENTION : Si la procédure n'existe pas, $result sera FALSE.
    $query = "CALL GetDepositWithdrawalRatio('$start_date', '$end_date')";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $ratio_data = $result->fetch_assoc();
        
        // Assurez-vous de convertir les valeurs en flottants
        $kpi_data['Deposits'] = (float)($ratio_data['TotalDepots'] ?? 0.00);
        $kpi_data['Withdrawals'] = (float)($ratio_data['TotalRetraits'] ?? 0.00);
        $kpi_data['NetFlow'] = $kpi_data['Deposits'] - $kpi_data['Withdrawals'];

        // TRES IMPORTANT: Fermer les résultats et vider les tampons après un CALL dans MySQLi.
        while($conn->more_results()) { $conn->next_result(); }
    } else {
        // Enregistrer l'erreur si l'appel de la procédure a échoué (pour le débogage)
        error_log("Erreur ou procédure stockée GetDepositWithdrawalRatio manquante : " . $conn->error);
    }
    
    return $kpi_data;
}

/**
 * Récupère la liste des clients actifs (Nom et Prénoms) pour les Dropdowns.
 * (Fonction non utilisée ici mais incluse pour complétude)
 */
function getActiveClients($conn) {
    // ... code précédent ...
}
?>
