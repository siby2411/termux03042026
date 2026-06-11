<?php
require_once '../../includes/db.php';
$factures = $conn->query("SELECT * FROM factures ORDER BY id DESC");
?>
<table class="table">
    <tr><th>ID</th><th>Patient</th><th>Montant</th><th>Action</th></tr>
    <?php while($f = $factures->fetch_assoc()): ?>
    <tr>
        <td><?php echo $f['id']; ?></td>
        <td><?php echo htmlspecialchars($f['nom_patient']); ?></td>
        <td><?php echo number_format($f['montant'], 2); ?> FCFA</td>
        <td>
            <a href="export_facture.php?id=<?php echo $f['id']; ?>" class="btn btn-primary btn-sm">PDF</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
