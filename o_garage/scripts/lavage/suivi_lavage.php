<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';

$dbObj = new Database();
$pdo = $dbObj->getConnection();

// On récupère les lavages en cours
$stmt = $pdo->query("SELECT l.*, t.libelle as type_nom FROM lavage_operations l 
                     JOIN lavage_tarifs t ON l.id_tarif = t.id_tarif 
                     WHERE l.statut != 'Terminé' ORDER BY l.heure_entree DESC");
$encours = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold"><i class="fas fa-users-cog text-info"></i> Affectation des Agents Lavage</h2>
        <p class="text-muted">Suivi temps réel de la station de lavage</p>
    </div>
</div>

<div class="row">
    <?php if (empty($encours)): ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-coffee fa-3x text-muted mb-3"></i>
            <p class="fs-5 text-muted">Aucun véhicule en cours de lavage actuellement.</p>
            <a href="entree_lavage.php" class="btn btn-info text-white">Réceptionner un véhicule</a>
        </div>
    <?php else: ?>
        <?php foreach ($encours as $l): ?>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm border-top border-info border-4 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="fw-bold text-primary"><?= htmlspecialchars($l['agent_affecte']) ?></h5>
                        <span class="badge bg-warning text-dark"><?= $l['statut'] ?></span>
                    </div>
                    <hr>
                    <p class="mb-1"><i class="fas fa-car me-2"></i> <strong>VÉHICULE :</strong> DK-TEST-01</p>
                    <p class="mb-1"><i class="fas fa-tag me-2"></i> <strong>SERVICE :</strong> <?= $l['type_nom'] ?></p>
                    <p class="mb-3"><i class="fas fa-clock me-2"></i> <strong>ENTRÉE :</strong> <?= date('H:i', strtotime($l['heure_entree'])) ?></p>
                    
                    <form action="traitement_lavage.php" method="POST">
                        <input type="hidden" name="action" value="terminer">
                        <input type="hidden" name="id_lavage" value="<?= $l['id_lavage'] ?>">
                        <button type="submit" class="btn btn-outline-success w-100 btn-sm fw-bold">MARQUER COMME TERMINÉ</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
