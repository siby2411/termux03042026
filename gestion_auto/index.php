<?php 
include 'config.php'; 
include 'header.php';
$db = Database::getInstance();

// Statistiques
$stats = [];
try {
    $query = $db->query("SELECT COUNT(*) as total FROM vehicules");
    $stats['total_vehicules'] = $query->fetch()['total'];

    $query = $db->query("SELECT COUNT(*) as total FROM vehicules WHERE statut = 'disponible'");
    $stats['vehicules_disponibles'] = $query->fetch()['total'];

    $query = $db->query("SELECT COUNT(*) as total FROM ventes WHERE DATE(date_vente) = CURDATE()");
    $stats['ventes_ajd'] = $query->fetch()['total'];

    $query = $db->query("SELECT COUNT(*) as total FROM locations WHERE date_debut <= CURDATE() AND date_fin >= CURDATE() AND statut = 'encours'");
    $stats['locations_encours'] = $query->fetch()['total'];

    $query = $db->query("SELECT SUM(prix_vente) as total FROM ventes WHERE MONTH(date_vente) = MONTH(CURDATE())");
    $stats['ca_mois'] = $query->fetch()['total'] ?? 0;
    
    // Véhicules récents
    $vehicules_recents = $db->fetchAll("
        SELECT v.*, m.nom as marque, mo.nom as modele 
        FROM vehicules v 
        LEFT JOIN marques m ON v.marque_id = m.id 
        LEFT JOIN modeles mo ON v.modele_id = mo.id 
        ORDER BY v.created_at DESC 
        LIMIT 5
    ");
    
    // Alertes véhicules
    $alertes_vehicules = $db->fetchAll("
        SELECT * FROM vehicules 
        WHERE statut = 'maintenance' OR statut = 'panne'
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    
} catch (Exception $e) {
    // Valeurs par défaut en cas d'erreur
    $stats = [
        'total_vehicules' => 0,
        'vehicules_disponibles' => 0,
        'ventes_ajd' => 0,
        'locations_encours' => 0,
        'ca_mois' => 0
    ];
    $vehicules_recents = [];
    $alertes_vehicules = [];
}
?>

<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Tableau de Bord</h1>
        </div>
    </div>
    
    <!-- Cartes de statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?= $stats['total_vehicules'] ?></h3>
                    <p class="mb-0">Véhicules Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <h3 class="text-success"><?= $stats['vehicules_disponibles'] ?></h3>
                    <p class="mb-0">Disponibles</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?= $stats['ventes_ajd'] ?></h3>
                    <p class="mb-0">Ventes Aujourd'hui</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <h3 class="text-info"><?= $stats['locations_encours'] ?></h3>
                    <p class="mb-0">Locations en Cours</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <h3 class="text-success"><?= number_format($stats['ca_mois'], 0, ',', ' ') ?> €</h3>
                    <p class="mb-0">CA du Mois</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?= $stats['total_vehicules'] - $stats['vehicules_disponibles'] ?></h3>
                    <p class="mb-0">Indisponibles</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et contenu principal -->
    <div class="row">
        <!-- Véhicules récents -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-car-front me-2"></i>Véhicules Récents
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($vehicules_recents)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($vehicules_recents as $vehicule): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($vehicule['marque'] ?? '') ?> <?= htmlspecialchars($vehicule['modele'] ?? '') ?></h6>
                                        <small class="text-muted">Immat: <?= htmlspecialchars($vehicule['immatriculation'] ?? '') ?></small>
                                    </div>
                                    <span class="badge bg-<?= $vehicule['statut'] == 'disponible' ? 'success' : ($vehicule['statut'] == 'vendu' ? 'secondary' : 'warning') ?>">
                                        <?= ucfirst($vehicule['statut'] ?? 'inconnu') ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">Aucun véhicule récent</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alertes et notifications -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Alertes Véhicules
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($alertes_vehicules)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($alertes_vehicules as $vehicule): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($vehicule['immatriculation'] ?? '') ?></h6>
                                        <span class="badge bg-danger"><?= ucfirst($vehicule['statut'] ?? '') ?></span>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($vehicule['probleme'] ?? 'Problème signalé') ?></p>
                                    <small class="text-muted">Ajouté le <?= date('d/m/Y', strtotime($vehicule['created_at'] ?? '')) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-success text-center">
                            <i class="bi bi-check-circle me-2"></i>Aucune alerte en cours
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="vehicule_ajouter.php" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-2"></i>Ajouter Véhicule
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="vehicules.php" class="btn btn-success w-100">
                                <i class="bi bi-list-ul me-2"></i>Liste Véhicules
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="ventes.php" class="btn btn-warning w-100">
                                <i class="bi bi-currency-euro me-2"></i>Nouvelle Vente
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="locations.php" class="btn btn-info w-100">
                                <i class="bi bi-calendar-plus me-2"></i>Nouvelle Location
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'footer.php'; ?>

<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Scripts simples pour le dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Animation des cartes statistiques
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
        card.classList.add('animate__animated', 'animate__fadeInUp');
    });
    
    // Tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<!-- CSS Animation -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</body>
</html>
