<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

// Gestion des dates (par défaut le mois en cours)
$date_debut = $_GET['debut'] ?? date('Y-m-01');
$date_fin = $_GET['fin'] ?? date('Y-m-t');

// Requête de récupération avec jointure pour avoir le nom du client
$sql = "SELECT p.*, c.nom, c.prenom 
        FROM PAIEMENTS p
        JOIN CLIENTS c ON p.id_tiers = c.id_client
        WHERE p.type_tiers = 'CLIENT' 
        AND DATE(p.date_paiement) BETWEEN ? AND ?
        ORDER BY p.date_paiement DESC";

$stmt = $db->prepare($sql);
$stmt->execute([$date_debut, $date_fin]);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i> Historique des encaissements</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="debut" value="<?= $date_debut ?>" class="form-control form-control-sm">
                <input type="date" name="fin" value="<?= $date_fin ?>" class="form-control form-control-sm">
                <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Mode</th>
                    <th>Réf. / Note</th>
                    <th class="text-end">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; foreach ($paiements as $p): $total += $p['montant']; ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($p['date_paiement'])) ?></td>
                    <td><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></td>
                    <td><span class="badge bg-secondary"><?= $p['mode_paiement'] ?></span></td>
                    <td><?= htmlspecialchars($p['notes']) ?></td>
                    <td class="text-end fw-bold"><?= number_format($p['montant'], 0, ',', ' ') ?> F</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="4" class="text-end fw-bold">TOTAL PÉRIODE</td>
                    <td class="text-end fw-bold text-primary fs-5"><?= number_format($total, 0, ',', ' ') ?> F</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
