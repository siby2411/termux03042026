<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

// Requête calculant le solde en temps réel
$sql = "SELECT c.*, 
        (SELECT IFNULL(SUM(total_commande), 0) FROM COMMANDE_VENTE WHERE id_client = c.id_client) as total_facture,
        (SELECT IFNULL(SUM(montant), 0) FROM PAIEMENTS WHERE id_client = c.id_client) as total_paye
        FROM CLIENTS c";
$clients = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="card shadow-sm">
    <div class="card-header bg-white"><h5><i class="fas fa-users"></i> État Financier Clients</h5></div>
    <table class="table align-middle">
        <thead class="table-light">
            <tr><th>Client</th><th>Total Facturé</th><th>Total Payé</th><th>Solde (Dette)</th></tr>
        </thead>
        <tbody>
            <?php foreach($clients as $c): 
                $solde = $c['total_facture'] - $c['total_paye'];
            ?>
            <tr>
                <td><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></td>
                <td><?= number_format($c['total_facture'], 0) ?> F</td>
                <td><?= number_format($c['total_paye'], 0) ?> F</td>
                <td class="fw-bold <?= $solde > 0 ? 'text-danger' : 'text-success' ?>">
                    <?= number_format($solde, 0) ?> F
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>
