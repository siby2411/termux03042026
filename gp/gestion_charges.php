<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>

<h2><i class="fas fa-coins"></i> Gestion des charges</h2>

<?php
// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libelle = $_POST['libelle'];
    $montant = $_POST['montant'];
    $date_charge = $_POST['date_charge'];
    $categorie = $_POST['categorie'];
    $notes = $_POST['notes'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO charges (libelle, montant, date_charge, categorie, notes) VALUES (?,?,?,?,?)");
    $stmt->execute([$libelle, $montant, $date_charge, $categorie, $notes]);
    echo "<div class='alert alert-success'>Charge ajoutée.</div>";
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM charges WHERE id=?")->execute([$id]);
    echo "<div class='alert alert-warning'>Charge supprimée.</div>";
}

$charges = $pdo->query("SELECT * FROM charges ORDER BY date_charge DESC")->fetchAll();
$total = $pdo->query("SELECT SUM(montant) FROM charges")->fetchColumn();
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Total des charges</h5>
                <h2><?= number_format($total ?? 0, 2) ?> €</h2>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <i class="fas fa-plus-circle"></i> Ajouter une charge
    </div>
    <div class="card-body">
        <form method="post" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="libelle" class="form-control" placeholder="Libellé" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" name="montant" class="form-control" placeholder="Montant (€)" required>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_charge" class="form-control" required>
            </div>
            <div class="col-md-2">
                <select name="categorie" class="form-select">
                    <option value="carburant">⛽ Carburant</option>
                    <option value="personnel">👥 Personnel</option>
                    <option value="location">🏢 Location</option>
                    <option value="maintenance">🔧 Maintenance</option>
                    <option value="douane">📦 Douane</option>
                    <option value="autres">📝 Autres</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
            </div>
            <div class="col-12">
                <textarea name="notes" class="form-control" rows="2" placeholder="Notes (optionnel)"></textarea>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-info text-white">
        <i class="fas fa-list"></i> Liste des charges
    </div>
    <div class="card-body">
        <?php if (empty($charges)): ?>
            <p class="text-muted">Aucune charge enregistrée.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>Date</th><th>Libellé</th><th>Catégorie</th><th>Montant</th><th>Note</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($charges as $c): ?>
                        <tr>
                            <td><?= $c['date_charge'] ?></td>
                            <td><?= htmlspecialchars($c['libelle']) ?></td>
                            <td><?= $c['categorie'] ?></td>
                            <td class="text-danger fw-bold"><?= number_format($c['montant'], 2) ?> €</td>
                            <td><?= htmlspecialchars($c['notes'] ?? '-') ?></td>
                            <td>
                                <a href="gestion_charges.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette charge ?')">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('footer.php'); ?>
