<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

$page_title = "Catalogue des Pièces";
include '../../includes/header.php';

// Récupération des pièces avec le nom du fournisseur pour plus de clarté
$query = "SELECT p.*, f.nom_fournisseur 
          FROM PIECES p 
          LEFT JOIN FOURNISSEURS f ON p.id_fournisseur = f.id_fournisseur 
          ORDER BY p.categorie ASC, p.nom_piece ASC";
$stmt = $db->query($query);
$pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" id="catalogSearch" class="form-control border-start-0 ps-0" placeholder="Rechercher par nom, référence ou catégorie...">
        </div>
    </div>
    <div class="col-md-4 text-end">
        <a href="ajouter_piece.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> Ajouter une pièce
        </a>
    </div>
</div>

<div class="row g-3" id="catalogGrid">
    <?php foreach($pieces as $p): 
        $stock_class = ($p['stock_actuel'] <= 0) ? 'bg-danger' : (($p['stock_actuel'] <= 5) ? 'bg-warning text-dark' : 'bg-success');
    ?>
    <div class="col-md-4 col-lg-3 catalog-item" 
         data-search="<?= strtolower($p['nom_piece'] . ' ' . $p['reference'] . ' ' . $p['categorie']) ?>">
        <div class="card h-100 border-0 shadow-sm card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-light text-primary border border-primary-subtle"><?= $p['categorie'] ?></span>
                    <span class="badge <?= $stock_class ?>">Stock: <?= $p['stock_actuel'] ?></span>
                </div>
                <h6 class="fw-bold mb-1"><?= $p['nom_piece'] ?></h6>
                <small class="text-muted d-block mb-3">Réf: <?= $p['reference'] ?></small>
                
                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                    <div class="text-primary fw-bold h5 mb-0"><?= number_format($p['prix_vente'], 0, ',', ' ') ?> F</div>
                    <div class="btn-group">
                        <a href="modifier_piece.php?id=<?= $p['id_piece'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                        <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='tracabilite_vin.php?ref=<?= $p['reference'] ?>'"><i class="fas fa-history"></i></button>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2">
                <small class="text-muted" style="font-size: 0.7rem;">Fournisseur: <?= $p['nom_fournisseur'] ?? 'Générique' ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
.card-hover { transition: transform 0.2s, shadow 0.2s; cursor: default; }
.card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>

<script>
document.getElementById('catalogSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.catalog-item').forEach(item => {
        let text = item.getAttribute('data-search');
        item.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
