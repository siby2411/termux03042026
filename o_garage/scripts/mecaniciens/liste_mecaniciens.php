<?php require_once '../../includes/header.php'; 
$ms = $db->query("SELECT * FROM personnel WHERE role LIKE '%Méc% ' OR role LIKE '%Ing%'")->fetchAll(); ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between">
        <h5 class="mb-0">Équipe Technique</h5>
        <a href="formulaire_mecanicien.php" class="btn btn-warning btn-sm">Nouveau</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Code</th><th>Nom</th><th>Spécialité</th><th>Performance</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach($ms as $m): ?>
                <tr>
                    <td><?= $m['code_interne'] ?></td>
                    <td><strong><?= $m['nom_complet'] ?></strong></td>
                    <td><?= $m['role'] ?></td>
                    <td><span class="badge bg-info"><?= $m['note_performance'] ?>%</span></td>
                    <td><a href="paie.php?id=<?= $m['id_personnel'] ?>" class="btn btn-sm btn-outline-primary">Bulletin</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
