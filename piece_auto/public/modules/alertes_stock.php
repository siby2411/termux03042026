<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$page_title = "Alertes de Stock";
include '../../includes/header.php';

$query = "SELECT p.*, f.nom_fournisseur, f.telephone as f_tel 
          FROM PIECES p 
          LEFT JOIN FOURNISSEURS f ON p.id_fournisseur = f.id_fournisseur 
          WHERE p.stock_actuel <= 5 
          ORDER BY p.stock_actuel ASC";
$stmt = $db->query($query);
?>

<div class="row g-4">
    <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <span class="badge bg-danger">Stock : <?= $row['stock_actuel'] ?></span>
                    <small class="text-muted"><?= $row['reference'] ?></small>
                </div>
                <h5 class="card-title mt-2"><?= $row['nom_piece'] ?></h5>
                <p class="small text-muted mb-0">Fournisseur : <?= $row['nom_fournisseur'] ?? 'Inconnu' ?></p>
                <p class="small text-muted">Tél : <?= $row['f_tel'] ?? 'N/A' ?></p>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include '../../includes/footer.php'; ?>
