<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$page_title = "Analyse des Marges";
include '../../includes/header.php';

$query = "SELECT cv.id_commande_vente, cv.date_vente, cv.total_commande, cv.cout_total_cump, cv.marge_brute,
          c.nom, c.prenom
          FROM COMMANDE_VENTE cv
          JOIN CLIENTS c ON cv.id_client = c.id_client
          ORDER BY cv.date_vente DESC";
$stmt = $db->query($query);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-primary text-white p-3">
            <h4>Rentabilité sur les Ventes</h4>
            <p class="mb-0">Suivi des gains réels basés sur le Coût Unitaire Moyen Pondéré (CUMP).</p>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Vente #</th>
                    <th>Client</th>
                    <th class="text-end">CA (F)</th>
                    <th class="text-end">Coût (CUMP)</th>
                    <th class="text-end">Marge Brute</th>
                    <th class="text-center">Performance</th>
                </tr>
            </thead>
            <tbody>
                <?php while($v = $stmt->fetch(PDO::FETCH_ASSOC)): 
                    $pct = ($v['total_commande'] > 0) ? ($v['marge_brute'] / $v['total_commande']) * 100 : 0;
                ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($v['date_vente'])) ?></td>
                    <td>#<?= $v['id_commande_vente'] ?></td>
                    <td><?= $v['nom'] ?></td>
                    <td class="text-end fw-bold"><?= number_format($v['total_commande'], 0, ',', ' ') ?> F</td>
                    <td class="text-end text-muted"><?= number_format($v['cout_total_cump'], 0, ',', ' ') ?> F</td>
                    <td class="text-end text-success fw-bold">+ <?= number_format($v['marge_brute'], 0, ',', ' ') ?> F</td>
                    <td class="text-center">
                        <span class="badge <?= $pct > 20 ? 'bg-success' : 'bg-warning' ?>">
                            <?= round($pct, 1) ?> %
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
