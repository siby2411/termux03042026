<?php
// /var/www/piece_auto/public/modules/analyse_marges.php
// Module d'analyse de la rentabilité des pièces

$page_title = "Analyse de la Rentabilité et des Marges";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php'; // Correction de la ligne 7

$database = new Database();
$db = $database->getConnection();

$message = '';
$resultats_marges = [];
$labels = [];
$marge_data = [];

try {
    // 1. Requête pour récupérer les données nécessaires au calcul de la marge
    // La marge est calculée par : (Prix Vente HT - CUMP) * Quantité Vendue
    $query = "SELECT
                p.reference,
                p.nom_piece,
                p.cump_actuel,
                p.prix_vente_ht,
                SUM(lv.quantite) AS total_ventes,
                SUM(lv.quantite * lv.prix_vente_unitaire) AS total_revenu,
                SUM(lv.quantite * p.cump_actuel) AS total_cump
              FROM LIGNE_VENTE lv
              JOIN PIECES p ON lv.id_piece = p.id_piece
              GROUP BY p.id_piece
              ORDER BY total_revenu DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resultats_marges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Traitement des données pour les graphiques et l'affichage
    foreach ($resultats_marges as &$resultat) {
        $resultat['marge_brute'] = $resultat['total_revenu'] - $resultat['total_cump'];
        
        // Calcul du Taux de Marge (%) : (Marge Brute / Coût d'Achat) * 100
        $resultat['taux_marge'] = ($resultat['total_cump'] > 0) 
                                ? ($resultat['marge_brute'] / $resultat['total_cump']) * 100 
                                : 0;
        
        // Calcul du Taux de Marque (%) : (Marge Brute / Prix de Vente) * 100
        $resultat['taux_marque'] = ($resultat['total_revenu'] > 0)
                                ? ($resultat['marge_brute'] / $resultat['total_revenu']) * 100
                                : 0;
        
        // Pour le graphique (Top 10 des pièces par Marge Brute)
        if (count($labels) < 10) {
            $labels[] = $resultat['reference'];
            $marge_data[] = round($resultat['marge_brute'], 2);
        }
    }
    
} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Erreur de base de données lors de l\'analyse des marges : ' . $e->getMessage() . '</div>';
}

?>

<h1><i class="fas fa-hand-holding-usd"></i> <?= $page_title ?></h1>
<p class="lead">Visualisez la rentabilité de chaque pièce en comparant le Prix de Vente (HT) au Coût Unitaire Moyen Pondéré (CUMP).</p>
<hr>

<?= $message ?>

<div class="card mb-4">
    <div class="card-header">Top 10 des Pièces par Marge Brute (€)</div>
    <div class="card-body">
        <canvas id="margeChart" style="height: 300px;"></canvas>
    </div>
</div>

<h3>Détail de la Marge par Pièce</h3>
<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Désignation</th>
                <th class="text-end">CUMP</th>
                <th class="text-end">Prix Vente HT</th>
                <th class="text-end">Total Marge Brute (€)</th>
                <th class="text-end">Taux Marge (%)</th>
                <th class="text-end">Taux Marque (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultats_marges as $res): ?>
            <tr>
                <td><?= htmlspecialchars($res['reference']) ?></td>
                <td><?= htmlspecialchars($res['nom_piece']) ?></td>
                <td class="text-end"><?= number_format($res['cump_actuel'], 2, ',', ' ') ?></td>
                <td class="text-end"><?= number_format($res['prix_vente_ht'], 2, ',', ' ') ?></td>
                <td class="text-end fw-bold text-success"><?= number_format($res['marge_brute'], 2, ',', ' ') ?></td>
                <td class="text-end"><?= number_format($res['taux_marge'], 1, ',', ' ') ?></td>
                <td class="text-end"><?= number_format($res['taux_marque'], 1, ',', ' ') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('margeChart').getContext('2d');
    const labels = <?= json_encode($labels) ?>;
    const data = <?= json_encode($marge_data) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Marge Brute Totale (€)',
                data: data,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Marge Brute (€)'
                    }
                }
            }
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
