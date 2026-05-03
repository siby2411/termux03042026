<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

// Statistiques flash
$stats = [
    'ca_mois' => $db->query("SELECT SUM(total_commande) FROM COMMANDE_VENTE WHERE MONTH(date_vente) = MONTH(NOW())")->fetchColumn() ?: 0,
    'nb_ventes' => $db->query("SELECT COUNT(*) FROM COMMANDE_VENTE WHERE DATE(date_vente) = DATE(NOW())")->fetchColumn() ?: 0,
    'ruptures' => $db->query("SELECT COUNT(*) FROM PIECES WHERE stock_actuel <= 5")->fetchColumn() ?: 0,
    'marge_total' => $db->query("SELECT SUM(marge_brute) FROM COMMANDE_VENTE")->fetchColumn() ?: 0
];

$page_title = "Tableau de Bord Décisionnel";
include '../../includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white border-0 shadow-sm">
            <div class="card-body">
                <h6>CA du Mois</h6>
                <h3><?= number_format($stats['ca_mois'], 0, ',', ' ') ?> F</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white border-0 shadow-sm">
            <div class="card-body">
                <h6>Ventes (Aujourd'hui)</h6>
                <h3><?= $stats['nb_ventes'] ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white border-0 shadow-sm">
            <div class="card-body">
                <h6>Articles en Rupture</h6>
                <h3><?= $stats['ruptures'] ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-dark text-white border-0 shadow-sm">
            <div class="card-body">
                <h6>Marge Brute Totale</h6>
                <h3><?= number_format($stats['marge_total'], 0, ',', ' ') ?> F</h3>
            </div>
        </div>
    </div>
</div>

<?php include 'reporting_strategique.php'; ?>
