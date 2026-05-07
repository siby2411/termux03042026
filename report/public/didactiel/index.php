<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Formation SYSCOHADA";
$page_icon = "book";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

$modules = [
    ['ecriture.php', '📝', 'Écritures comptables', 'Apprenez la partie double'],
    ['bilan.php', '📊', 'Bilan comptable', 'Actif, passif et équilibre'],
    ['compte_resultat.php', '📈', 'Compte de résultat', 'Produits, charges, résultat'],
    ['sig.php', '📉', 'SIG & Ratios', 'EBE, CAF, BFR'],
    ['flux_tresorerie.php', '💵', 'Flux de trésorerie', 'Mouvements de liquidités'],
    ['ratios_financiers.php', '🎯', 'Ratios financiers', 'Analyse performance'],
    ['immobilisations.php', '🏗️', 'Immobilisations', 'Gestion des actifs'],
    ['amortissements.php', '📉', 'Amortissements', 'Calcul des dotations'],
    ['tva.php', '💰', 'TVA Sénégal', 'Collecte et déduction'],
    ['capitaux_propres.php', '📊', 'Variation capitaux', 'Suivi patrimoine']
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-mortarboard"></i> Didacticiel - Formation Complète SYSCOHADA UEMOA</h5>
                <small>10 modules pour maîtriser la comptabilité financière</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($modules as $module): ?>
                    <div class="col-md-4 mb-3">
                        <a href="<?= $module[0] ?>" class="text-decoration-none">
                            <div class="card h-100 text-center module-card">
                                <div class="card-body">
                                    <div class="fs-1"><?= $module[1] ?></div>
                                    <h6 class="mt-2 text-dark"><?= $module[2] ?></h6>
                                    <small class="text-muted"><?= $module[3] ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="alert alert-success mt-4">
                    <i class="bi bi-trophy"></i>
                    <strong>Parcours recommandé :</strong> Suivez l'ordre des modules pour une progression optimale !
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
