<?php
// includes/functions.php
session_start();


function secure_input($data) {
    if (is_array($data)) {
        return array_map('secure_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function format_montant($montant, $devise = 'FCFA') {
    if ($montant === null || $montant === '') {
        return '0 ' . $devise;
    }
    
    // Convertir en nombre si c'est une chaîne
    $montant = floatval($montant);
    
    // Formater avec séparateurs de milliers
    return number_format($montant, 0, ',', ' ') . ' ' . $devise;
}

function formatMontant($montant, $devise = 'FCFA') {
    // Alias pour compatibilité
    return format_montant($montant, $devise);
}

function get_exercice_courant($pdo) {
    $sql = "SELECT * FROM exercices_comptables 
           WHERE statut_cloture = 'OUVERT' 
           ORDER BY date_debut DESC LIMIT 1";
    return $pdo->query($sql)->fetch();
}

function getGlobalStats($pdo) {
    $id_exercice = $_SESSION['id_exercice'];
    
    $stats = [
        'total_ecritures' => $pdo->query("SELECT COUNT(*) FROM ecritures WHERE id_exercice = $id_exercice")->fetchColumn(),
        'total_articles' => $pdo->query("SELECT COUNT(*) FROM articles_stock WHERE actif = 1")->fetchColumn(),
        'total_tiers' => $pdo->query("SELECT COUNT(*) FROM tiers WHERE actif = 1")->fetchColumn()
    ];
    
    // Écritures du mois
    $sql_mois = "SELECT COUNT(*) FROM ecritures 
                WHERE id_exercice = $id_exercice 
                AND MONTH(date_ecriture) = MONTH(NOW())";
    $stats['ecritures_mois'] = $pdo->query($sql_mois)->fetchColumn();
    
    // Solde bancaire
    $sql_banque = "SELECT SUM(debit - credit) FROM ecritures 
                  WHERE id_exercice = $id_exercice 
                  AND compte_num LIKE '52%'";
    $stats['solde_banque'] = $pdo->query($sql_banque)->fetchColumn();
    
    // Jours avant clôture
    $sql_date = "SELECT DATEDIFF(date_fin, CURDATE()) as jours 
                FROM exercices_comptables 
                WHERE id_exercice = $id_exercice";
    $jours = $pdo->query($sql_date)->fetchColumn();
    $stats['jours_restants'] = max(0, $jours);
    
    return $stats;
}

function logger($action, $details = '') {
    global $pdo;
    
    $sql = "INSERT INTO logs (user_id, action, details, ip_address, user_agent, created_at)
            VALUES (:user_id, :action, :details, :ip, :agent, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':action' => $action,
        ':details' => $details,
        ':ip' => $_SERVER['REMOTE_ADDR'],
        ':agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
}

function checkPermission($permission) {
    $user_role = $_SESSION['user_role'];
    
    $permissions = [
        'admin' => ['*'],
        'comptable' => ['comptabilite', 'rapprochement', 'soldes', 'bilans'],
        'gestionnaire' => ['articles', 'inventaire'],
        'consultant' => ['rapports', 'soldes', 'bilans']
    ];
    
    return isset($permissions[$user_role]) && 
           (in_array('*', $permissions[$user_role]) || 
            in_array($permission, $permissions[$user_role]));
}

// Fonction pour calculer les flux de trésorerie
function calculerFluxTresorerie($pdo, $id_exercice, $date_debut, $date_fin) {
    $flux = [
        'exploitation' => [],
        'investissement' => [],
        'financement' => [],
        'variations' => []
    ];
    
    // 1. FLUX DE TRÉSORERIE D'EXPLOITATION
    // Récupérer les entrées d'exploitation (comptes 70-78)
    $sql_exploitation = "SELECT 
        SUM(CASE WHEN c.numero BETWEEN '70' AND '78' THEN e.credit - e.debit ELSE 0 END) as produits_exploitation,
        SUM(CASE WHEN c.numero BETWEEN '60' AND '68' THEN e.debit - e.credit ELSE 0 END) as charges_exploitation,
        SUM(CASE WHEN c.numero LIKE '40%' THEN e.debit - e.credit ELSE 0 END) as variation_stocks,
        SUM(CASE WHEN c.numero LIKE '41%' THEN e.credit - e.debit ELSE 0 END) as variation_clients,
        SUM(CASE WHEN c.numero LIKE '44%' THEN e.debit - e.credit ELSE 0 END) as variation_personnel,
        SUM(CASE WHEN c.numero LIKE '43%' THEN e.credit - e.debit ELSE 0 END) as variation_fournisseurs,
        SUM(CASE WHEN c.numero LIKE '445%' THEN e.debit - e.credit ELSE 0 END) as variation_etat,
        SUM(CASE WHEN c.numero LIKE '651%' THEN e.debit - e.credit ELSE 0 END) as impot_societes
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice
        AND e.date_ecriture BETWEEN :date_debut AND :date_fin";
    
    $stmt = $pdo->prepare($sql_exploitation);
    $stmt->execute([
        ':id_exercice' => $id_exercice,
        ':date_debut' => $date_debut,
        ':date_fin' => $date_fin
    ]);
    $flux['exploitation'] = $stmt->fetch();
    
    // 2. FLUX DE TRÉSORERIE D'INVESTISSEMENT
    $sql_investissement = "SELECT 
        SUM(CASE WHEN c.numero LIKE '2%' AND c.numero NOT LIKE '20%' THEN e.debit - e.credit ELSE 0 END) as acquisitions_immobilisations,
        SUM(CASE WHEN c.numero LIKE '2%' AND c.numero NOT LIKE '20%' THEN e.credit - e.debit ELSE 0 END) as cessions_immobilisations,
        SUM(CASE WHEN c.numero LIKE '77%' AND e.libelle LIKE '%cession%' THEN e.credit - e.debit ELSE 0 END) as produits_cessions,
        SUM(CASE WHEN c.numero LIKE '67%' AND e.libelle LIKE '%cession%' THEN e.debit - e.credit ELSE 0 END) as charges_cessions
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice
        AND e.date_ecriture BETWEEN :date_debut AND :date_fin";
    
    $stmt = $pdo->prepare($sql_investissement);
    $stmt->execute([
        ':id_exercice' => $id_exercice,
        ':date_debut' => $date_debut,
        ':date_fin' => $date_fin
    ]);
    $flux['investissement'] = $stmt->fetch();
    
    // 3. FLUX DE TRÉSORERIE DE FINANCEMENT
    $sql_financement = "SELECT 
        SUM(CASE WHEN c.numero LIKE '16%' THEN e.credit - e.debit ELSE 0 END) as emprunts,
        SUM(CASE WHEN c.numero LIKE '16%' THEN e.debit - e.credit ELSE 0 END) as remboursements_emprunts,
        SUM(CASE WHEN c.numero LIKE '101%' THEN e.credit - e.debit ELSE 0 END) as augmentations_capital,
        SUM(CASE WHEN c.numero LIKE '106%' THEN e.debit - e.credit ELSE 0 END) as dividendes,
        SUM(CASE WHEN c.numero LIKE '66%' AND c.numero NOT IN ('661', '665') THEN e.debit - e.credit ELSE 0 END) as charges_financieres
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice
        AND e.date_ecriture BETWEEN :date_debut AND :date_fin";
    
    $stmt = $pdo->prepare($sql_financement);
    $stmt->execute([
        ':id_exercice' => $id_exercice,
        ':date_debut' => $date_debut,
        ':date_fin' => $date_fin
    ]);
    $flux['financement'] = $stmt->fetch();
    
    // 4. VARIATION DE TRÉSORERIE
    $sql_variation = "SELECT 
        (SELECT SUM(debit - credit) FROM ecritures WHERE compte_num LIKE '5%' AND id_exercice = :id_exercice AND date_ecriture < :date_debut) as tresorerie_initiale,
        (SELECT SUM(debit - credit) FROM ecritures WHERE compte_num LIKE '5%' AND id_exercice = :id_exercice AND date_ecriture <= :date_fin) as tresorerie_finale
        FROM DUAL";
    
    $stmt = $pdo->prepare($sql_variation);
    $stmt->execute([
        ':id_exercice' => $id_exercice,
        ':date_debut' => $date_debut,
        ':date_fin' => $date_fin
    ]);
    $flux['variations'] = $stmt->fetch();
    
    return $flux;
}
?>
