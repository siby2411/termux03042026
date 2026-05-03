<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "INSERT INTO finances (immeuble_id, type_transaction, montant_total, commission_omega, date_transaction) VALUES (?, ?, ?, ?, NOW())";
    $pdo->prepare($sql)->execute([$_POST['i_id'], $_POST['type'], $_POST['total'], $_POST['com']]);
    
    // On met à jour le statut de l'immeuble automatiquement
    $nouveau_statut = ($_POST['type'] == 'Vente') ? 'Vendu' : 'Loué';
    $pdo->prepare("UPDATE immeubles SET statut = ? WHERE id = ?")->execute([$nouveau_statut, $_POST['i_id']]);
}

$stats = $pdo->query("SELECT SUM(commission_omega) as total_com, COUNT(*) as nb FROM finances")->fetch();
$transactions = $pdo->query("SELECT f.*, i.titre FROM finances f JOIN immeubles i ON f.immeuble_id = i.id ORDER BY date_transaction DESC")->fetchAll();
$immeubles = $pdo->query("SELECT * FROM immeubles WHERE statut = 'Disponible'")->fetchAll();
?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card bg-dark text-white p-4 shadow border-0">
            <h6 class="text-warning small text-uppercase">Total Commissions OMEGA</h6>
            <h2 class="display-6 fw-bold"><?= number_format($stats['total_com'] ?? 0, 0, ',', ' ') ?> <small class="fs-6">FCFA</small></h2>
            <p class="mb-0 text-muted"><?= $stats['nb'] ?> transactions réalisées</p>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-4 shadow-sm border-0">
            <h5 class="fw-bold mb-4">Enregistrer une Nouvelle Commission</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <select name="i_id" class="form-select" required>
                        <option value="">Sélectionner le bien...</option>
                        <?php foreach($immeubles as $i): ?>
                            <option value="<?= $i['id'] ?>"><?= $i['titre'] ?> (<?= $i['zone_nom'] ?? 'Dakar' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option>Vente</option><option>Location</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="number" name="total" class="form-control" placeholder="Montant de la transaction" required>
                </div>
                <div class="col-md-6">
                    <input type="number" name="com" class="form-control" placeholder="Commission OMEGA" required>
                </div>
                <div class="col-md-12 text-end">
                    <button type="submit" class="btn btn-warning px-4">Valider l'encaissement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card mt-4 p-4 shadow-sm border-0">
    <h5 class="fw-bold mb-3">Journal des Revenus</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>Date</th><th>Bien</th><th>Type</th><th>Montant Transac.</th><th>Commission</th></tr>
            </thead>
            <tbody>
                <?php foreach($transactions as $t): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($t['date_transaction'])) ?></td>
                    <td><b><?= $t['titre'] ?></b></td>
                    <td><span class="badge bg-<?= $t['type_transaction'] == 'Vente' ? 'primary' : 'info' ?>"><?= $t['type_transaction'] ?></span></td>
                    <td><?= number_format($t['montant_total'], 0, ',', ' ') ?> F</td>
                    <td class="fw-bold text-success">+ <?= number_format($t['commission_omega'], 0, ',', ' ') ?> F</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
