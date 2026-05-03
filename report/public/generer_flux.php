<?php
require_once __DIR__ . '/../includes/db.php';

try {
    // 1. Calcul de l'Exploitation (Somme des classes 6 et 7 ayant transité par la caisse/banque)
    $exploit = $pdo->query("SELECT SUM(montant) as total FROM ECRITURES_COMPTABLES 
                            WHERE (compte_debite_id LIKE '5%' AND compte_credite_id LIKE '7%')
                            OR (compte_debite_id LIKE '6%' AND compte_credite_id LIKE '5%')")->fetchColumn();

    // 2. Calcul de l'Investissement (Mouvements de la classe 2 via trésorerie)
    $invest = $pdo->query("SELECT SUM(CASE WHEN compte_debite_id LIKE '2%' THEN -montant ELSE montant END) 
                           FROM ECRITURES_COMPTABLES 
                           WHERE (compte_debite_id LIKE '2%' AND compte_credite_id LIKE '5%')
                           OR (compte_debite_id LIKE '5%' AND compte_credite_id LIKE '2%')")->fetchColumn();

    // 3. Calcul du Financement (Mouvements de la classe 1 via trésorerie)
    $finance = $pdo->query("SELECT SUM(CASE WHEN compte_debite_id LIKE '5%' THEN montant ELSE -montant END) 
                            FROM ECRITURES_COMPTABLES 
                            WHERE (compte_debite_id LIKE '5%' AND compte_credite_id LIKE '1%')
                            OR (compte_debite_id LIKE '1%' AND compte_credite_id LIKE '5%')")->fetchColumn();

    $periode = date('Y-m');
    $variation = $exploit + $invest + $finance;

    // 4. Mise à jour ou Insertion
    $stmt = $pdo->prepare("INSERT INTO FLUX_TRESORERIE (periode, flux_activite_exploit, flux_activite_invest, flux_activite_finance, variation_tresorerie) 
                           VALUES (?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE flux_activite_exploit=VALUES(flux_activite_exploit), flux_activite_invest=VALUES(flux_activite_invest), 
                           flux_activite_finance=VALUES(flux_activite_finance), variation_tresorerie=VALUES(variation_tresorerie)");
    
    $stmt->execute([$periode, $exploit ?? 0, $invest ?? 0, $finance ?? 0, $variation ?? 0]);

    echo "✅ Flux de trésorerie mis à jour pour la période $periode";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
