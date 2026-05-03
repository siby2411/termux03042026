<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../includes/auth_check.php';
// /var/www/piece_auto/public/modules/analyse_motorisation.php
// Module d'analyse de la répartition des pièces et des ventes par type de motorisation

$page_title = "Analyse de la Transition Énergétique (VE/VH)";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php'; // Correction de la ligne 7

$database = new Database();
$db = $database->getConnection();

$message = '';
$repartition_pieces = [];
$repartition_stock = [];
$labels_motorisation = ['Thermique', 'Electrique', 'Hybride', 'Multitype', 'Non Classé'];

try {
    // 1. Logique de Classification des pièces
    // NOTE: Ceci dépend d'une colonne 'type_motorisation' ou 'compatible_motorisation'
    // Pour l'instant, nous allons SIMULER la classification basée sur le nom/description de la pièce si ces colonnes n'existent pas.
    
    // Si la colonne 'type_motorisation' existe, utilisez-la.
    // Sinon, nous utilisons une classification heuristique (moins fiable mais fonctionnelle)
    
    // Requête pour récupérer toutes les pièces et leurs stocks
    $query = "
        SELECT
            nom_piece,
            quantite_stock,
            reference
        FROM PIECES
        ORDER BY quantite_stock DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialisation des compteurs
    $compteurs_stock = array_fill_keys($labels_motorisation, 0);
    $compteurs_references = array_fill_keys($labels_motorisation, 0);

    // Heuristique de Classification (à adapter selon les données réelles)
    foreach ($pieces as $piece) {
        $nom = strtolower($piece['nom_piece']);
        $type = 'Non Classé';

        if (strpos($nom, 'batterie') !== false || strpos($nom, 'moteur electrique') !== false || strpos($nom, 'chargeur') !== false || strpos($nom, 've') !== false) {
            $type = 'Electrique';
        } elseif (strpos($nom, 'hybride') !== false || strpos($nom, 'vh') !== false) {
            $type = 'Hybride';
        } elseif (strpos($nom, 'filtre a huile') !== false || strpos($nom, 'bougie') !== false || strpos($nom, 'echappement') !== false) {
             // Ces pièces sont typiquement Thermiques, mais peuvent être Multitypes. Simplifions.
            $type = 'Thermique';
        }
        
        // Accumulation
        if (in_array($type, $labels_motorisation)) {
            $compteurs_stock[$type] += $piece['quantite_stock'];
            $compteurs_references[$type] += 1;
        } else {
            // Assurez-vous que les pièces non classées sont comptées
            $compteurs_stock['Non Classé'] += $piece['quantite_stock'];
            $compteurs_references['Non Classé'] += 1;
        }
    }
    
    // Préparation des données pour les graphiques
    $data_stock = array_values($compteurs_stock);
    $data_references = array_values($compteurs_references);
    
} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Erreur de base de données lors de l\'analyse des motorisations : ' . $e->getMessage() . '</div>';
}

?>

<h1><i class="fas fa-car-battery"></i> <?= $page_title ?></h1>
<p class="lead">Analyse la répartition de votre inventaire (stock et nombre de références) par type de motorisation.</p>
<hr>

<?= $message ?>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">Répartition du Stock (Quantités de Pièces)</div>
            <div class="card-body">
                <canvas id="stockChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">Répartition des Références (Diversité)</div>
            <div class="card-body">
                <canvas id="referencesChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<h3>Synthèse par Motorisation</h3>
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Type de Motorisation</th>
                <th class="text-end">Nombre de Références</th>
                <th class="text-end">Quantité Totale en Stock</th>
                <th class="text-end">Part du Stock Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_stock = array_sum($data_stock);
            foreach ($labels_motorisation as $index => $label): 
                $stock = $data_stock[$index];
                $references = $data_references[$index];
                $pourcentage = ($total_stock > 0) ? ($stock / $total_stock) * 100 : 0;
            ?>
            <tr>
                <td><?= $label ?></td>
                <td class="text-end"><?= number_format($references, 0, ',', ' ') ?></td>
                <td class="text-end fw-bold"><?= number_format($stock, 0, ',', ' ') ?></td>
                <td class="text-end"><?= number_format($pourcentage, 1, ',', ' ') ?> %</td>
            </tr>
            <?php endforeach; ?>
            <tr class="table-info">
                <td><strong>Total</strong></td>
                <td class="text-end"><strong><?= number_format(array_sum($data_references), 0, ',', ' ') ?></strong></td>
                <td class="text-end"><strong><?= number_format($total_stock, 0, ',', ' ') ?></strong></td>
                <td class="text-end"><strong>100.0 %</strong></td>
            </tr>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = <?= json_encode($labels_motorisation) ?>;
    
    // Graphique 1: Stock
    const ctxStock = document.getElementById('stockChart').getContext('2d');
    new Chart(ctxStock, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: <?= json_encode($data_stock) ?>,
                backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#6c757d', '#dc3545'], // Couleurs Bootstrap
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Répartition par Quantité de Stock' }
            }
        }
    });
    
    // Graphique 2: Références
    const ctxReferences = document.getElementById('referencesChart').getContext('2d');
    new Chart(ctxReferences, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: <?= json_encode($data_references) ?>,
                backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#6c757d', '#dc3545'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Répartition par Nombre de Références' }
            }
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
