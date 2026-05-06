<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Liste des cours - Didacticiel SYSCOHADA";
$page_icon = "list-ul";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

$cours = [
    ['ecriture.php', '📝', 'Écritures comptables', 'Apprenez la partie double et la saisie des opérations'],
    ['bilan.php', '📊', 'Bilan comptable', 'Comprenez l\'actif, le passif et l\'équilibre financier'],
    ['sig.php', '📈', 'SIG & Ratios', 'Maîtrisez EBE, CAF, BFR et les indicateurs de performance'],
    ['tva.php', '💰', 'TVA Sénégal', 'Calcul, collecte, déduction et déclaration'],
    ['capitaux_propres.php', '📉', 'Variation capitaux', 'Suivez l\'évolution du patrimoine net']
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-journal-bookmark-fill"></i> Catalogue des formations</h5>
                <small>Sélectionnez un module pour commencer votre formation</small>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach($cours as $c): ?>
                    <a href="<?= $c[0] ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3"><?= $c[1] ?></div>
                            <div>
                                <h5 class="mb-1"><?= $c[2] ?></h5>
                                <p class="mb-0 text-muted"><?= $c[3] ?></p>
                            </div>
                            <div class="ms-auto">
                                <i class="bi bi-arrow-right-circle fs-3"></i>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="alert alert-info mt-4">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Conseil de formation :</strong> Parcourez les modules dans l'ordre pour une progression optimale.
                </div>
                
                <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-outline-primary">← Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
