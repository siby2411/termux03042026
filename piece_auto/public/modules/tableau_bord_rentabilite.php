<?php
$page_title = "Rentabilité Réelle (CUMP)";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$db = (new Database())->getConnection();

// KPI basés sur le cumul des marges réelles (Prix de vente - Coût de revient figé)
$query = "SELECT 
            SUM(prix_vente_unitaire * quantite_vendue) as ca_total,
            SUM(cump_au_moment_vente * quantite_vendue) as cout_total
          FROM DETAIL_VENTE";
$res = $db->query($query)->fetch(PDO::FETCH_ASSOC);

$ca = $res['ca_total'] ?? 0;
$cout = $res['cout_total'] ?? 0;
$marge = $ca - $cout;
$taux = ($ca > 0) ? ($marge / $ca) * 100 : 0;
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="fas fa-balance-scale text-success"></i> Analyse de Rentabilité</h1>
            <p class="text-muted">Calcul basé sur le Coût Unitaire Moyen Pondéré (CUMP).</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <h6>Ventes Totales (HT)</h6>
                    <h2><?= number_format($ca, 2, ',', ' ') ?> €</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <h6>Marge Brute Réelle</h6>
                    <h2><?= number_format($marge, 2, ',', ' ') ?> €</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white shadow">
                <div class="card-body">
                    <h6>Taux de Marge Moyen</h6>
                    <h2><?= number_format($taux, 1) ?> %</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Dernières ventes et marges unitaires</strong></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Référence</th>
                        <th>Prix Vente</th>
                        <th>Coût Revient (CUMP)</th>
                        <th>Marge</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $details = $db->query("SELECT dv.*, p.reference, cv.date_commande 
                                         FROM DETAIL_VENTE dv 
                                         JOIN PIECES p ON dv.id_piece = p.id_piece 
                                         JOIN COMMANDE_VENTE cv ON dv.id_commande_vente = cv.id_commande_vente
                                         ORDER BY cv.date_commande DESC LIMIT 10");
                    while($row = $details->fetch(PDO::FETCH_ASSOC)):
                        $m_unitaire = $row['prix_vente_unitaire'] - $row['cump_au_moment_vente'];
                    ?>
                    <tr>
                        <td><?= date('d/m/y', strtotime($row['date_commande'])) ?></td>
                        <td><strong><?= $row['reference'] ?></strong></td>
                        <td><?= number_format($row['prix_vente_unitaire'], 2) ?> €</td>
                        <td class="text-muted"><?= number_format($row['cump_au_moment_vente'], 2) ?> €</td>
                        <td class="fw-bold text-<?= ($m_unitaire > 0) ? 'success' : 'danger' ?>">
                            <?= number_format($m_unitaire, 2) ?> €
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
