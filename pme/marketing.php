<?php
include 'includes/db.php';
include 'includes/header.php';

// On récupère les documents liés au service Marketing (supposons que l'ID marketing est 3, à ajuster selon votre BDD)
// Ou plus simplement, on cherche les fichiers qui contiennent "pub", "logo", "campagne" dans le nom
$docs_marketing = $pdo->query("SELECT * FROM documents WHERE nom_fichier LIKE '%pub%' OR nom_fichier LIKE '%logo%' OR nom_fichier LIKE '%marketing%'")->fetchAll();
?>

<h1 class="h2 border-bottom pb-2 text-danger"><i class="fas fa-bullhorn"></i> Service Marketing</h1>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">Campagnes en cours</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Promo Hiver 2025
                        <span class="badge bg-success rounded-pill">Active</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Lancement Produit X
                        <span class="badge bg-warning text-dark rounded-pill">Planification</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white">Assets Graphiques & Documents</div>
            <div class="card-body">
                <?php if(count($docs_marketing) > 0): ?>
                    <ul class="list-group">
                        <?php foreach($docs_marketing as $doc): ?>
                        <li class="list-group-item">
                            <i class="fas fa-file-image"></i> 
                            <a href="<?= $doc['chemin_fichier'] ?>"><?= $doc['nom_fichier'] ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">Aucun document marketing spécifique trouvé.</p>
                    <a href="documents.php" class="btn btn-sm btn-outline-danger">Uploader un visuel</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
