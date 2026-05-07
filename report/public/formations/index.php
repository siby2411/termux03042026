<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Centre de Formation - OMEGA ERP";
$page_icon = "mortarboard";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

$modules_formation = [
    ['tiers.php', '🏢', 'Gestion des Tiers', 'Clients, Fournisseurs, contacts', 'Créer et gérer les fiches tiers'],
    ['facturation.php', '📄', 'Facturation', 'Émission de factures', 'Créer des factures de vente/achat'],
    ['engagements_hors_bilan.php', '🛡️', 'Engagements Hors Bilan', 'Classe 8 SYSCOHADA', 'Caution, aval, garantie'],
    ['audit_trail.php', '👁️', 'Audit Trail', 'Traçabilité', 'Journal des actions'],
    ['declarations_fiscales.php', '📋', 'Déclarations fiscales', 'TVA, IS, IR', 'Déclarations Sénégal'],
    ['../didactiel/ecriture.php', '📝', 'Écritures comptables', 'Partie double', 'Saisie des opérations'],
    ['../didactiel/bilan.php', '📊', 'Bilan comptable', 'Actif/Passif', 'Analyse patrimoniale'],
    ['../didactiel/sig.php', '📈', 'SIG & Ratios', 'EBE, CAF, BFR', 'Indicateurs de gestion'],
    ['../didactiel/amortissements.php', '📉', 'Amortissements', 'Linéaire/Dégressif', 'Calcul des dotations'],
    ['../didactiel/tva.php', '💰', 'TVA Sénégal', 'Taux 18%', 'Collecte et déduction']
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-mortarboard"></i> Centre de Formation - OMEGA ERP</h5>
                <small>Apprenez à maîtriser chaque module de l'application</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Bienvenue dans votre espace de formation !</strong><br>
                    Sélectionnez un module ci-dessous pour accéder à son tutoriel complet, ses cas pratiques et son évaluation.
                </div>
                
                <div class="row">
                    <?php foreach($modules_formation as $module): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="fs-1 me-3"><?= $module[1] ?></div>
                                    <div>
                                        <h6 class="mb-0"><?= $module[2] ?></h6>
                                        <small class="text-muted"><?= $module[3] ?></small>
                                        <p class="mt-1 mb-0"><small><?= $module[4] ?></small></p>
                                        <a href="<?= $module[0] ?>" class="btn btn-sm btn-primary mt-2">
                                            <i class="bi bi-play-circle"></i> Accéder à la formation
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="alert alert-success mt-3 text-center">
                    <i class="bi bi-trophy"></i>
                    <strong>Parcours certifiant :</strong> Suivez les 10 modules pour obtenir votre certification OMEGA ERP !
                </div>
                
                <div class="text-center">
                    <a href="../dashboard_expert.php" class="btn btn-outline-primary">
                        <i class="bi bi-speedometer2"></i> Retour au Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
