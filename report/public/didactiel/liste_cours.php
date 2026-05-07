<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Liste des cours - Didacticiel";
$page_icon = "list-ul";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

$cours = [
    ['ecriture.php', '📝', 'Écritures comptables', 'La partie double expliquée'],
    ['bilan.php', '📊', 'Bilan comptable', 'Structure Actif/Passif'],
    ['compte_resultat.php', '📈', 'Compte de résultat', 'Produits et charges'],
    ['sig.php', '📉', 'SIG', 'Soldes Intermédiaires de Gestion'],
    ['flux_tresorerie.php', '💵', 'Flux de trésorerie', 'Tableau des flux'],
    ['ratios_financiers.php', '🎯', 'Ratios financiers', 'Indicateurs clés'],
    ['immobilisations.php', '🏗️', 'Immobilisations', 'Classe 2 SYSCOHADA'],
    ['amortissements.php', '📉', 'Amortissements', 'Calculs linéaires'],
    ['tva.php', '💰', 'TVA Sénégal', 'Taux 18% et déclaration'],
    ['capitaux_propres.php', '📊', 'Variation capitaux', 'Capitaux propres']
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-journal-bookmark-fill"></i> Catalogue des formations</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach($cours as $c): ?>
                    <a href="<?= $c[0] ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="fs-2 me-3"><?= $c[1] ?></div>
                            <div>
                                <h6 class="mb-0"><?= $c[2] ?></h6>
                                <small class="text-muted"><?= $c[3] ?></small>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-outline-primary">← Retour</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
