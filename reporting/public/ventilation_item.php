<?php
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../views/sidebar.php';
include __DIR__ . '/../views/topbar.php';

if(isset($_POST['submit'])){
    $item_code = $_POST['item_code'];
    $item_label = $_POST['item_label'];
    $item_type = $_POST['item_type'];
    $criteria = $_POST['criteria'];
    $display_order = $_POST['display_order'];

    $stmt = $conn->prepare("INSERT INTO VENTILATION_ITEMS (item_code, item_label, item_type, criteria, display_order) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$item_code, $item_label, $item_type, $criteria, $display_order]);
    echo '<div class="alert alert-success">Insertion réussie !</div>';
}
?>

<div class="container mt-4">
    <h2>Ajouter un item de ventilation</h2>
    <form method="post" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Code item</label>
            <input type="text" class="form-control" name="item_code" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Libellé</label>
            <input type="text" class="form-control" name="item_label" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Type</label>
            <select class="form-select" name="item_type" required>
                <option value="BILAN_ACTIF">BILAN_ACTIF</option>
                <option value="BILAN_PASSIF">BILAN_PASSIF</option>
                <option value="CR_CHARGES">CR_CHARGES</option>
                <option value="CR_PRODUITS">CR_PRODUITS</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Critères</label>
            <textarea class="form-control" name="criteria"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Ordre affichage</label>
            <input type="number" class="form-control" name="display_order" value="0">
        </div>
        <div class="col-12">
            <button type="submit" name="submit" class="btn btn-primary">Ajouter</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>

