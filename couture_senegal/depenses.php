<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO depenses (date_dep, categorie, description, montant) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['date_dep'], $_POST['categorie'], $_POST['description'], $_POST['montant']]);
    setFlash('success', "Dépense enregistrée.");
    header("Location: depenses.php"); exit;
}

$depenses = $pdo->query("SELECT * FROM depenses ORDER BY date_dep DESC LIMIT 50")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Gestion des Charges (Dépenses)</h4>
    <button class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDep"><i class="bi bi-dash-circle"></i> Nouvelle Dépense</button>
</div>

<div class="card border-0 shadow-sm">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Date</th><th>Catégorie</th><th>Description</th><th>Montant</th></tr></thead>
        <tbody>
            <?php foreach($depenses as $d): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($d['date_dep'])) ?></td>
                <td><span class="badge bg-secondary"><?= $d['categorie'] ?></span></td>
                <td><?= $d['description'] ?></td>
                <td class="fw-bold text-danger">- <?= number_format($d['montant'], 0, ',', ' ') ?> F</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalDep" tabindex="-1">
    <form method="POST" class="modal-dialog modal-content">
        <div class="modal-header"><h5>Enregistrer une charge</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body row g-3">
            <div class="col-md-6"><label>Date</label><input type="date" name="date_dep" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
            <div class="col-md-6"><label>Catégorie</label>
                <select name="categorie" class="form-select">
                    <option>Loyer</option><option>Senelec/Eau</option><option>Fournitures</option><option>Transport</option><option>Salaires</option>
                </select>
            </div>
            <div class="col-12"><label>Description</label><input type="text" name="description" class="form-control" placeholder="Ex: Achat fils dorés" required></div>
            <div class="col-12"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger w-100">Valider la dépense</button></div>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
