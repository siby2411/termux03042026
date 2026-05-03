<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/Database.php';

$page_title = "OMEGA PIÈCE AUTO - Dashboard";
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$count_pieces = 0;
$count_alertes = 0;
$ca_mensuel = 0;

try {
    $count_pieces = $db->query("SELECT COUNT(*) FROM PIECES")->fetchColumn() ?: 0;
    $count_alertes = $db->query("SELECT COUNT(*) FROM PIECES WHERE stock_actuel <= 5")->fetchColumn() ?: 0;
    // Utilisation du nom de colonne vérifié : date_vente
    $ca_mensuel = $db->query("SELECT SUM(total_commande) FROM COMMANDE_VENTE WHERE MONTH(date_vente) = MONTH(CURRENT_DATE)")->fetchColumn() ?: 0;
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold"><i class="fas fa-tachometer-alt text-primary"></i> Système de Gestion Intégré</h1>
            <p class="text-muted">Tableau de bord complet - Omega Informatique Consulting</p>
        </div>
    </div>

    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body">
                    <h6>CATALOGUE PIÈCES</h6>
                    <h2><?= $count_pieces ?> <small class="fs-6">références</small></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow-sm border-0">
                <div class="card-body">
                    <h6>ALERTES RUPTURE</h6>
                    <h2><?= $count_alertes ?> <small class="fs-6">à commander</small></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body">
                    <h6>CA MENSUEL</h6>
                    <h2><?= number_format($ca_mensuel, 0, ',', ' ') ?> F</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-truck-loading me-2"></i> Achats</div>
                <div class="list-group list-group-flush">
                    <a href="modules/creation_commande_achat.php" class="list-group-item list-group-item-action">Nouvelle Commande Achat</a>
                    <a href="modules/gestion_achats.php" class="list-group-item list-group-item-action">Suivi des Achats</a>
                    <a href="modules/gestion_fournisseurs.php" class="list-group-item list-group-item-action">Fournisseurs</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-shopping-cart me-2"></i> Ventes</div>
                <div class="list-group list-group-flush">
                    <a href="modules/creation_vente.php" class="list-group-item list-group-item-action text-primary fw-bold">🚀 Nouvelle Vente Rapide</a>
                    <a href="modules/gestion_commandes_vente.php" class="list-group-item list-group-item-action">Historique des Ventes</a>
                    <a href="modules/gestion_clients.php" class="list-group-item list-group-item-action">Portefeuille Clients</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-boxes me-2"></i> Stock</div>
                <div class="list-group list-group-flush">
                    <a href="modules/gestion_pieces.php" class="list-group-item list-group-item-action">Consulter Catalogue</a>
                    <a href="modules/gestion_stock.php" class="list-group-item list-group-item-action">Mouvements de Stock</a>
                    <a href="modules/tracabilite_vin.php" class="list-group-item list-group-item-action">Recherche par VIN</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
