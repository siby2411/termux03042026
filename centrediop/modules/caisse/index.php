<?php
require_once '../../includes/db.php';
$factures = $conn->query("SELECT * FROM factures WHERE statut_paiement = 'en_attente'");
?>
<table class="table">
    <tr><th>Patient</th><th>Montant</th><th>Action</th></tr>
    <?php while($f = $factures->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($f['nom_patient']); ?></td>
        <td><?php echo number_format($f['montant'], 2); ?> FCFA</td>
        <td>
            <a href="encaisser.php?id=<?php echo $f['id']; ?>" class="btn btn-success btn-sm">Encaisser</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
