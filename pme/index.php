<?php
include 'includes/auth_check.php';
include 'includes/db.php';
include 'includes/header.php';

// Diagnostic Data
$nb_retards = $pdo->query("SELECT COUNT(*) FROM commandes WHERE etat = 'validee' AND date_commande < DATE_SUB(NOW(), INTERVAL 48 HOUR)")->fetchColumn();
$nb_msg = $pdo->query("SELECT COUNT(*) FROM messages WHERE service_dest_id = ".$_SESSION['service_id']." AND lu = 0")->fetchColumn();
?>

<div class="container-fluid px-4">
    <div class="row mb-4">
        <?php if($nb_retards > 0): ?>
            <div class="col-md-6"><div class="alert alert-warning border-0 shadow-sm mb-0"><i class="fas fa-exclamation-circle"></i> <?= $nb_retards ?> facturations en retard !</div></div>
        <?php endif; ?>
        <?php if($nb_msg > 0): ?>
            <div class="col-md-6"><div class="alert alert-info border-0 shadow-sm mb-0"><i class="fas fa-envelope"></i> <?= $nb_msg ?> nouveau(x) message(s) service.</div></div>
        <?php endif; ?>
    </div>

<?php if ($alerte_stock > 0): ?>     <div class='alert alert-danger border-0 shadow-sm mb-4 animate__animated animate__pulse animate__infinite'>         <i class='fas fa-exclamation-triangle me-2'></i> <strong>Rupture imminente :</strong> <?= $alerte_stock ?> produit(s) sous le seuil d'alerte. Les messages ont été envoyés aux Achats.     </div> <?php endif; ?>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 p-3 text-center">
                        <i class="fas fa-file-signature fa-3x text-warning mb-2"></i>
                        <h5>Devis & Propositions</h5>
                        <div class="btn-group mt-auto">
                            <a href="creer_devis.php" class="btn btn-sm btn-outline-warning">Créer</a>
                            <a href="liste_devis.php" class="btn btn-sm btn-warning">Gérer</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 p-3 text-center">
                        <i class="fas fa-shopping-cart fa-3x text-primary mb-2"></i>
                        <h5>Commandes Intelligentes</h5>
                        <div class="btn-group mt-auto">
                            <a href="creer_commande.php" class="btn btn-sm btn-primary">Nouvelle</a>
                            <a href="logistique.php" class="btn btn-sm btn-outline-primary">Suivi</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm bg-dark text-white p-4">
                        <div class="d-flex justify-content-between align-items-center">
<?php if($_SESSION['user_role'] == 'direction'): ?>
                            <div><h4 class="mb-0">Business Intelligence</h4><p class="small opacity-50">Analyses et KPI en temps réel</p></div>
                            <a href="reporting.php" class="btn btn-light">Ouvrir Analytics</a>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="list-group shadow-sm border-0">
                <div class="list-group-item bg-light fw-bold border-0">Services Collaboratifs</div>
                <a href="messagerie.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-comments me-2 text-primary"></i> Messagerie Interne</div>
                    <span class="badge bg-primary rounded-pill"><?= $nb_msg ?></span>
                </a>
                <a href="documents.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-folder-open me-2 text-info"></i> Documents Partagés
                </a>
                <a href="clients.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-users me-2 text-success"></i> Portefeuille Clients
                </a>
                <a href="stock.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-boxes me-2 text-secondary"></i> Stock & Inventaire
                </a>
                <a href="admin_users.php" class="list-group-item list-group-item-action border-top mt-2 text-muted">
                    <i class="fas fa-user-shield me-2"></i> Administration RH
                </a>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
