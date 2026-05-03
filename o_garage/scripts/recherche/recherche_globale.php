<?php
require_once '../../includes/header.php';
require_once '../../includes/classes/Database.php';

$q = $_GET['q'] ?? '';
$db = (new Database())->getConnection();

// Recherche multi-tables : Personnel, Véhicules, Interventions
$sql = "SELECT 'PERSONNEL' as type, nom_complet as titre, code_interne as info, id_personnel as link_id 
        FROM personnel WHERE nom_complet LIKE ? OR code_interne LIKE ?
        UNION
        SELECT 'VEHICULE' as type, immatriculation as titre, marque as info, id_vehicule as link_id 
        FROM vehicules WHERE immatriculation LIKE ? OR marque LIKE ?
        UNION
        SELECT 'INTERVENTION' as type, description_panne as titre, complexite as info, id_fiche as link_id 
        FROM fiches_intervention WHERE description_panne LIKE ?";

$stmt = $db->prepare($sql);
$term = "%$q%";
$stmt->execute([$term, $term, $term, $term, $term]);
$results = $stmt->fetchAll();
?>

<div class="container">
    <h3 class="mb-4 text-primary"><i class="fas fa-search me-2"></i>Résultats pour : "<?= htmlspecialchars($q) ?>"</h3>
    
    <?php if(empty($results)): ?>
        <div class="alert alert-warning">Aucune correspondance trouvée dans la base Omega Tech.</div>
    <?php else: ?>
        <div class="list-group shadow-sm">
            <?php foreach($results as $r): ?>
                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                    <div>
                        <span class="badge bg-dark me-2"><?= $r['type'] ?></span>
                        <span class="fw-bold"><?= $r['titre'] ?></span>
                        <small class="text-muted ms-3"><?= $r['info'] ?></small>
                    </div>
                    <i class="fas fa-chevron-right text-primary"></i>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
