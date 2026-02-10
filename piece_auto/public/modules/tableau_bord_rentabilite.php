<?php
// /var/www/piece_auto/public/modules/tableau_bord_rentabilite.php

$page_title = "Tableau de Bord de Rentabilité";
include '../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// --- 1. FONCTION POUR RÉCUPÉRER LES INDICATEURS CLÉS (KPI) ---
function get_kpis($db) {
    // Récupère les totaux globaux pour les indicateurs (depuis COMMANDE_VENTE)
    $query = "SELECT 
                SUM(montant_total) AS total_ca,
                SUM(cout_total_cump) AS total_cump,
                SUM(marge_brute) AS total_marge
              FROM COMMANDE_VENTE";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $kpis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si les valeurs sont NULL (aucune vente), les forcer à zéro
    foreach ($kpis as $key => $value) {
        $kpis[$key] = $value ?? 0;
    }
    
    // Calcul du Taux de Marge Brute
    $kpis['taux_marge_brute'] = ($kpis['total_ca'] > 0) 
        ? number_format(($kpis['total_marge'] / $kpis['total_ca']) * 100, 2) 
        : 0.00;
        
    return $kpis;
}

// --- 2. FONCTION POUR RÉCUPÉRER LES VENTES RÉCENTES ---
function get_recent_sales($db, $limit = 10) {
    $query = "SELECT 
                cv.id_commande_vente,
                cv.date_commande,
                cv.montant_total,
                cv.marge_brute,
                c.nom AS client_nom,
                c.prenom AS client_prenom
              FROM COMMANDE_VENTE cv
              JOIN CLIENTS c ON cv.id_client = c.id_client
              ORDER BY cv.date_commande DESC
              LIMIT :limit";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Exécution des fonctions
$kpis = get_kpis($db);
$recent_sales = get_recent_sales($db);

?>

<h1><i class="fas fa-chart-line"></i> <?= $page_title ?></h1>
<p class="lead">Aperçu des indicateurs de performance clés (KPI) et de la rentabilité.</p>
<hr>

<div class="row mb-5">
    
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Chiffre d'Affaires (CA)</div>
            <div class="card-body">
                <h4 class="card-title"><?= number_format($kpis['total_ca'], 2, ',', ' ') ?> €</h4>
                <p class="card-text">Total des ventes enregistrées.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-header">Coût Total des Ventes (CUMP)</div>
            <div class="card-body">
                <h4 class="card-title"><?= number_format($kpis['total_cump'], 2, ',', ' ') ?> €</h4>
                <p class="card-text">Coût de revient des pièces vendues.</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Marge Brute Totale</div>
            <div class="card-body">
                <h4 class="card-title"><?= number_format($kpis['total_marge'], 2, ',', ' ') ?> €</h4>
                <p class="card-text">CA - CUMP (Votre bénéfice brut).</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Taux de Marge Brute</div>
            <div class="card-body">
                <h4 class="card-title"><?= number_format($kpis['taux_marge_brute'], 2, ',', ' ') ?> %</h4>
                <p class="card-text">Rentabilité moyenne des ventes (Marge / CA).</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Ventes Récentes (Top 10)</div>
    <div class="card-body">
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>N° Vente</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th class="text-end">Montant Total (€)</th>
                    <th class="text-end text-success">Marge Brute (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recent_sales) > 0): ?>
                    <?php foreach ($recent_sales as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['id_commande_vente']) ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($sale['date_commande'])) ?></td>
                            <td><?= htmlspecialchars($sale['client_prenom'] . ' ' . $sale['client_nom']) ?></td>
                            <td class="text-end"><?= number_format($sale['montant_total'], 2, ',', ' ') ?></td>
                            <td class="text-end text-success"><?= number_format($sale['marge_brute'], 2, ',', ' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucune vente enregistrée pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
