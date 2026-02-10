<?php
// /var/www/piece_auto/public/modules/rapports_financiers.php
$page_title = "Rapports Financiers";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Chiffre d'Affaires Total (Correction : total_commande)
    $stmt_ca = $db->query("SELECT SUM(total_commande) FROM COMMANDE_VENTE");
    $total_ca = $stmt_ca->fetchColumn() ?: 0;

    // 2. Panier Moyen
    $stmt_panier = $db->query("SELECT AVG(total_commande) FROM COMMANDE_VENTE");
    $panier_moyen = $stmt_panier->fetchColumn() ?: 0;

    // 3. Total des Achats (Dépenses fournisseurs)
    // On calcule la somme des lignes d'achats car la table COMMANDES_ACHAT n'a pas toujours de total direct
    $stmt_achats = $db->query("SELECT SUM(quantite_commandee * prix_achat_unitaire) FROM LIGNES_COMMANDE_ACHAT");
    $total_achats = $stmt_achats->fetchColumn() ?: 0;

    // 4. Marge Brute Estimée
    $marge = $total_ca - $total_achats;

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur de base de données : ' . $e->getMessage() . '</div>';
    $total_ca = $panier_moyen = $total_achats = $marge = 0;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-chart-pie"></i> Rapports & Indicateurs Clés</h1>
    <div>
        <a href="export_ventes.php" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel (Ventes)
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white shadow">
            <div class="card-body">
                <div class="small opacity-75">Chiffre d'Affaires (HT)</div>
                <div class="h3 fw-bold"><?= number_format($total_ca, 2, ',', ' ') ?> €</div>
                <i class="fas fa-coins fa-2x opacity-25 float-end"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card bg-dark text-white shadow">
            <div class="card-body">
                <div class="small opacity-75">Total Achats Fournisseurs</div>
                <div class="h3 fw-bold"><?= number_format($total_achats, 2, ',', ' ') ?> €</div>
                <i class="fas fa-shopping-basket fa-2x opacity-25 float-end"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card <?= $marge >= 0 ? 'bg-success' : 'bg-danger' ?> text-white shadow">
            <div class="card-body">
                <div class="small opacity-75">Marge Brute (Estimée)</div>
                <div class="h3 fw-bold"><?= number_format($marge, 2, ',', ' ') ?> €</div>
                <i class="fas fa-balance-scale fa-2x opacity-25 float-end"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white shadow">
            <div class="card-body">
                <div class="small opacity-75">Panier Moyen</div>
                <div class="h3 fw-bold"><?= number_format($panier_moyen, 2, ',', ' ') ?> €</div>
                <i class="fas fa-shopping-cart fa-2x opacity-25 float-end"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-table"></i> Détails des performances</h5>
            </div>
            <div class="card-body">
                <p>Ce module compare vos revenus de vente et vos coûts d'achat pour calculer la rentabilité de votre stock.</p>
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle"></i> La marge brute est calculée sur la base de la totalité des achats effectués vs totalité des ventes.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
