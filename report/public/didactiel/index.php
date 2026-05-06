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
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-mortarboard"></i> Didacticiel - Formation Comptable SYSCOHADA UEMOA</h5>
                <small>Apprenez à maîtriser chaque fonctionnalité du logiciel OMEGA ERP</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Bienvenue dans votre espace de formation !</strong><br>
                    Sélectionnez un module ci-dessous pour apprendre son fonctionnement, ses règles comptables et ses cas pratiques.
                </div>
                
                <div class="row g-4 mt-2">
                    <!-- Module Écritures -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-pencil-square fs-1 text-primary"></i>
                                <h5 class="mt-2">Écritures comptables</h5>
                                <p>Apprenez la partie double, le journal général, et les différents types d'opérations.</p>
                                <a href="ecriture.php" class="btn btn-outline-primary">Commencer →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module Grand Livre -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-book fs-1 text-success"></i>
                                <h5 class="mt-2">Grand Livre</h5>
                                <p>Analysez les mouvements par compte, consultez les soldes détaillés.</p>
                                <a href="grand_livre.php" class="btn btn-outline-success">Commencer →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module Balance -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-scale fs-1 text-warning"></i>
                                <h5 class="mt-2">Balance générale</h5>
                                <p>Vérifiez l'équilibre débit/crédit, validez vos écritures.</p>
                                <a href="balance.php" class="btn btn-outline-warning">Commencer →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module Bilan -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-pie-chart fs-1 text-danger"></i>
                                <h5 class="mt-2">Bilan comptable</h5>
                                <p>Comprenez l'actif, le passif, et l'équilibre financier.</p>
                                <a href="bilan.php" class="btn btn-outline-danger">Commencer →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module SIG -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-graph-up fs-1 text-info"></i>
                                <h5 class="mt-2">SIG & Ratios</h5>
                                <p>Maîtrisez les Soldes Intermédiaires de Gestion, EBE, CAF, BFR.</p>
                                <a href="sig.php" class="btn btn-outline-info">Commencer →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module TVA -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-percent fs-1 text-secondary"></i>
                                <h5 class="mt-2">TVA Sénégal</h5>
                                <p>Calcul, collecte, déduction et déclaration mensuelle.</p>
                                <a href="tva.php" class="btn btn-outline-secondary">Commencer →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module Amortissements -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-calculator fs-1 text-dark"></i>
                                <h5 class="mt-2">Amortissements</h5>
                                <p>Calculez les dotations, suivez les valeurs nettes comptables.</p>
                                <a href="amortissements.php" class="btn btn-outline-dark">Commencer →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module Immobilisations -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-building fs-1 text-primary"></i>
                                <h5 class="mt-2">Immobilisations</h5>
                                <p>Gestion des actifs, acquisitions, cessions.</p>
                                <a href="immobilisations.php" class="btn btn-outline-primary">Commencer →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module Compte de résultat -->
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-calculator fs-1 text-success"></i>
                                <h5 class="mt-2">Compte de résultat</h5>
                                <p>Analysez les produits, charges et le résultat net.</p>
                                <a href="compte_resultat.php" class="btn btn-outline-success">Commencer →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
