<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT f.*, p.nom, p.prenom FROM factures f JOIN patients p ON f.id_patient = p.id ORDER BY f.date_facture DESC";
$stmt = $db->query($query);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold text-primary"><i class="bi bi-cash-stack me-2"></i>Facturation</h2>
    <a href="add.php" class="btn btn-primary">Créer une Facture</a>
</div>
<div class="card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>N° Facture</th>
                <th>Patient</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($factures as $f): ?>
            <tr>
                <td class="fw-bold"><?= $f['numero_facture'] ?></td>
                <td><?= $f['nom'] ?> <?= $f['prenom'] ?></td>
                <td><?= number_format($f['montant_total'], 0, ',', ' ') ?> FCFA</td>
                <td><span class="badge bg-<?= $f['statut']=='Payée'?'success':'warning' ?>"><?= $f['statut'] ?></span></td>
                <td><a href="print_facture.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../../includes/footer.php'; ?>
