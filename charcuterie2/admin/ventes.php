<?php
require_once 'header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pdo = getPDO();
$action = $_GET['action'] ?? 'liste';

if ($action == 'nouvelle') {
    // Formulaire de vente (à garder existant)
    include 'caisse_pos.php';
} else {
    // Liste des ventes avec recherche
    $search = $_GET['search'] ?? '';
    $date_debut = $_GET['date_debut'] ?? '';
    $date_fin = $_GET['date_fin'] ?? '';
    
    $sql = "SELECT v.*, c.nom as client_nom, c.prenom as client_prenom 
            FROM ventes v 
            LEFT JOIN clients c ON v.client_id = c.id 
            WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (v.numero_vente LIKE ? OR c.nom LIKE ? OR c.prenom LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if (!empty($date_debut)) {
        $sql .= " AND v.date_vente >= ?";
        $params[] = $date_debut;
    }
    if (!empty($date_fin)) {
        $sql .= " AND v.date_vente <= ?";
        $params[] = $date_fin;
    }
    
    $sql .= " ORDER BY v.date_vente DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ventes = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-shopping-cart me-2"></i>Gestion des ventes</h2>
        <a href="ventes.php?action=nouvelle" class="btn btn-success"><i class="fas fa-plus me-1"></i>Nouvelle vente</a>
    </div>
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    
    <!-- Formulaire de recherche -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Recherche avancée</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label>Recherche</label>
                    <input type="text" name="search" class="form-control" placeholder="N° vente, client..." value="<?= escape($search) ?>">
                </div>
                <div class="col-md-3">
                    <label>Date début</label>
                    <input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>">
                </div>
                <div class="col-md-3">
                    <label>Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Rechercher</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>N° vente</th><th>Client</th><th>Date</th><th>Total HT</th><th>Total TTC</th><th>Statut</th><th>Actions</th> </thead>
                    <tbody>
                        <?php foreach ($ventes as $v): ?>
                        响应
                            <td><code><?= escape($v['numero_vente']) ?></code>响应
                            <td><?= escape($v['client_nom'] . ' ' . ($v['client_prenom'] ?? '')) ?>响应
                            <td><?= formatDateTime($v['date_vente']) ?>响应
                            <td><?= formatMoney($v['total_ht']) ?>响应
                            <td><?= formatMoney($v['total_ttc']) ?>响应
                            <td><span class="badge bg-<?= $v['statut'] == 'confirmée' ? 'success' : 'warning' ?>"><?= escape($v['statut'] ?? 'confirmée') ?></span>响应
                            <td>
                                <a href="bon_livraison.php?type=vente&id=<?= $v['id'] ?>" class="btn btn-sm btn-success" title="Bon de livraison">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="vente_detail.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-info" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                            响应
                         ?>
                        <?php endforeach; ?>
                        <?php if (empty($ventes)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucune vente trouvée</td></tr>
                        <?php endif; ?>
                    </tbody>
                 ?>
            </div>
        </div>
    </div>
    <?php
}

require_once 'footer.php';
?>
