<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$page_title = "Suivi des Créances Clients";
$db = (new Database())->getConnection();

// Récupération des clients ayant un solde positif via la vue SQL
$clients_financier = $db->query("SELECT * FROM vue_etat_financier_clients WHERE reste_a_payer > 0 ORDER BY reste_a_payer DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-danger"><i class="fas fa-money-bill-wave me-2"></i> Clients avec impayés</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Client</th>
                    <th class="text-end">Total Facturé</th>
                    <th class="text-end">Total Payé</th>
                    <th class="text-end">Reste à payer</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients_financier)): ?>
                    <tr><td colspan="5" class="text-center py-4">Aucun impayé en cours.</td></tr>
                <?php else: ?>
                    <?php foreach ($clients_financier as $row): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($row['nom'] . ' ' . $row['prenom']) ?></td>
                            <td class="text-end"><?= number_format($row['total_facture'], 0, ',', ' ') ?> F</td>
                            <td class="text-end"><?= number_format($row['total_paye'], 0, ',', ' ') ?> F</td>
                            <td class="text-end fw-bold text-danger"><?= number_format($row['reste_a_payer'], 0, ',', ' ') ?> F</td>
                            <td class="text-center">
                                <a href="saisie_paiement.php?id_client=<?= $row['id_client'] ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-check"></i> Encaisser
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
