<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Période par défaut (mois en cours)
$debut = date('Y-m-01');
$fin = date('Y-m-t');

if(isset($_POST['periode'])) {
    $debut = $_POST['debut'];
    $fin = $_POST['fin'];
}

// Chiffre d'affaires
$query = $conn->prepare("
    SELECT 
        SUM(total_ttc) as ca_total,
        SUM(total_ht) as ca_ht,
        SUM(tva) as total_tva,
        COUNT(*) as nb_commandes,
        AVG(total_ttc) as panier_moyen
    FROM commandes 
    WHERE DATE(date_commande) BETWEEN ? AND ?
    AND statut != 'annule'
");
$query->execute([$debut, $fin]);
$stats = $query->fetch();

// CA par type de commande
$query_type = $conn->prepare("
    SELECT 
        type_commande,
        COUNT(*) as nb_commandes,
        SUM(total_ttc) as ca
    FROM commandes 
    WHERE DATE(date_commande) BETWEEN ? AND ?
    AND statut != 'annule'
    GROUP BY type_commande
    ORDER BY ca DESC
");
$query_type->execute([$debut, $fin]);
$ca_par_type = $query_type->fetchAll();

// Évolution du CA sur les 6 derniers mois
$query_evolution = $conn->query("
    SELECT 
        DATE_FORMAT(date_commande, '%Y-%m') as mois,
        SUM(total_ttc) as ca_mensuel,
        COUNT(*) as nb_commandes
    FROM commandes 
    WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    AND statut != 'annule'
    GROUP BY DATE_FORMAT(date_commande, '%Y-%m')
    ORDER BY mois
");
$evolution_data = $query_evolution->fetchAll();

// Plats les plus vendus
$query_plats = $conn->prepare("
    SELECT 
        p.nom,
        SUM(ca.quantite) as total_vendu,
        SUM(ca.sous_total) as chiffre_affaires
    FROM commande_articles ca
    LEFT JOIN plats p ON ca.plat_id = p.id
    LEFT JOIN commandes c ON ca.commande_id = c.id
    WHERE DATE(c.date_commande) BETWEEN ? AND ?
    AND c.statut != 'annule'
    GROUP BY p.id, p.nom
    ORDER BY total_vendu DESC
    LIMIT 10
");
$query_plats->execute([$debut, $fin]);
$plats_vendus = $query_plats->fetchAll();
?>

<div class="card">
    <h2>📊 État Financier</h2>
    
    <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
        <div class="form-group">
            <label>Date de début</label>
            <input type="date" name="debut" value="<?= $debut ?>" required>
        </div>
        <div class="form-group">
            <label>Date de fin</label>
            <input type="date" name="fin" value="<?= $fin ?>" required>
        </div>
        <div class="form-group">
            <button type="submit" name="periode" class="btn btn-primary">📅 Filtrer</button>
        </div>
    </form>
</div>

<!-- Statistiques principales -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['ca_total'] ?? 0, 0, ',', ' ') ?> €</div>
        <div>Chiffre d'affaires</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['ca_ht'] ?? 0, 0, ',', ' ') ?> €</div>
        <div>CA HT</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['total_tva'] ?? 0, 0, ',', ' ') ?> €</div>
        <div>TVA collectée</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['nb_commandes'] ?? 0 ?></div>
        <div>Commandes</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['panier_moyen'] ?? 0, 2, ',', ' ') ?> €</div>
        <div>Panier moyen</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Évolution du CA -->
    <div class="card">
        <h3>📈 Évolution du CA</h3>
        <canvas id="chartEvolution" height="250"></canvas>
    </div>
    
    <!-- CA par type -->
    <div class="card">
        <h3>🍽️ CA par Type</h3>
        <canvas id="chartType" height="250"></canvas>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Plats les plus vendus -->
    <div class="card">
        <h3>⭐ Top 10 des Plats</h3>
        <table>
            <thead>
                <tr>
                    <th>Plat</th>
                    <th>Quantité</th>
                    <th>CA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($plats_vendus as $plat): ?>
                <tr>
                    <td><?= $plat['nom'] ?></td>
                    <td><?= $plat['total_vendu'] ?></td>
                    <td><?= number_format($plat['chiffre_affaires'], 2, ',', ' ') ?> €</td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($plats_vendus)): ?>
                <tr>
                    <td colspan="3" style="text-align: center; color: #7f8c8d; padding: 2rem;">
                        Aucune donnée pour cette période
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Répartition par type -->
    <div class="card">
        <h3>📊 Répartition par Type</h3>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Commandes</th>
                    <th>CA</th>
                    <th>Part</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_ca = array_sum(array_column($ca_par_type, 'ca'));
                foreach($ca_par_type as $type): 
                    $pourcentage = $total_ca > 0 ? ($type['ca'] / $total_ca) * 100 : 0;
                ?>
                <tr>
                    <td>
                        <?= match($type['type_commande']) {
                            'sur_place' => '🍽️ Sur place',
                            'a_emporter' => '🥡 À emporter',
                            'livraison' => '🚗 Livraison'
                        } ?>
                    </td>
                    <td><?= $type['nb_commandes'] ?></td>
                    <td><?= number_format($type['ca'], 2, ',', ' ') ?> €</td>
                    <td><?= number_format($pourcentage, 1, ',', ' ') ?> %</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Données pour l'évolution du CA
const evolutionData = {
    labels: [<?php 
        foreach($evolution_data as $data) {
            echo "'" . date('m/Y', strtotime($data['mois'] . '-01')) . "',";
        }
    ?>],
    datasets: [{
        label: 'Chiffre d\'affaires (€)',
        data: [<?php 
            foreach($evolution_data as $data) {
                echo $data['ca_mensuel'] . ',';
            }
        ?>],
        borderColor: '#e74c3c',
        backgroundColor: 'rgba(231, 76, 60, 0.1)',
        fill: true,
        tension: 0.4
    }]
};

// Données pour les types de commande
const typeData = {
    labels: [<?php 
        foreach($ca_par_type as $type) {
            echo "'" . match($type['type_commande']) {
                'sur_place' => 'Sur place',
                'a_emporter' => 'À emporter',
                'livraison' => 'Livraison'
            } . "',";
        }
    ?>],
    datasets: [{
        data: [<?php 
            foreach($ca_par_type as $type) {
                echo $type['ca'] . ',';
            }
        ?>],
        backgroundColor: ['#e74c3c', '#3498db', '#27ae60']
    }]
};

// Initialisation des graphiques
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution
    const ctxEvolution = document.getElementById('chartEvolution')?.getContext('2d');
    if (ctxEvolution) {
        new Chart(ctxEvolution, {
            type: 'line',
            data: evolutionData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Évolution mensuelle du CA'
                    }
                }
            }
        });
    }

    // Graphique des types
    const ctxType = document.getElementById('chartType')?.getContext('2d');
    if (ctxType) {
        new Chart(ctxType, {
            type: 'doughnut',
            data: typeData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
});
</script>


