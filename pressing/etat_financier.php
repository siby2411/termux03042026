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
        COUNT(*) as nb_commandes
    FROM commandes 
    WHERE DATE(date_commande) BETWEEN ? AND ?
");
$query->execute([$debut, $fin]);
$stats = $query->fetch();

// Évolution du CA sur les 6 derniers mois
$query_evolution = $conn->query("
    SELECT 
        DATE_FORMAT(date_commande, '%Y-%m') as mois,
        SUM(total_ttc) as ca_mensuel
    FROM commandes 
    WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date_commande, '%Y-%m')
    ORDER BY mois
");
$evolution_data = $query_evolution->fetchAll();

// Produits les plus vendus
$query_produits = $conn->prepare("
    SELECT 
        s.nom,
        SUM(ca.quantite) as total_vendu,
        SUM(ca.sous_total) as chiffre_affaires
    FROM commande_articles ca
    LEFT JOIN services s ON ca.service_id = s.id
    LEFT JOIN commandes c ON ca.commande_id = c.id
    WHERE DATE(c.date_commande) BETWEEN ? AND ?
    GROUP BY s.id, s.nom
    ORDER BY total_vendu DESC
    LIMIT 10
");
$query_produits->execute([$debut, $fin]);
$produits_vendus = $query_produits->fetchAll();

// Paiements par mode
$query_paiements = $conn->prepare("
    SELECT mode_paiement, SUM(montant) as total 
    FROM paiements 
    WHERE DATE(date_paiement) BETWEEN ? AND ?
    GROUP BY mode_paiement
");
$query_paiements->execute([$debut, $fin]);
$paiements_par_mode = $query_paiements->fetchAll();
?>

<div class="card">
    <h2>État Financier - Analyse Avancée</h2>
    
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
            <button type="submit" name="periode" class="btn btn-primary">Filtrer</button>
            <a href="generer_rapport.php?debut=<?= $debut ?>&fin=<?= $fin ?>" class="btn btn-success">Générer PDF</a>
        </div>
    </form>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['ca_total'] ?? 0, 2, ',', ' ') ?> €</div>
        <div>Chiffre d'affaires TTC</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['ca_ht'] ?? 0, 2, ',', ' ') ?> €</div>
        <div>Chiffre d'affaires HT</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['total_tva'] ?? 0, 2, ',', ' ') ?> €</div>
        <div>TVA collectée</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['nb_commandes'] ?? 0 ?></div>
        <div>Nombre de commandes</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <div class="card">
        <h3>Évolution du CA sur 6 mois</h3>
        <canvas id="chartEvolution" height="250"></canvas>
    </div>
    
    <div class="card">
        <h3>Produits les plus vendus (période)</h3>
        <canvas id="chartProduits" height="250"></canvas>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <div class="card">
        <h3>Paiements par mode</h3>
        <table>
            <thead>
                <tr>
                    <th>Mode de paiement</th>
                    <th>Montant</th>
                    <th>Pourcentage</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_paiements = array_sum(array_column($paiements_par_mode, 'total'));
                foreach($paiements_par_mode as $paiement): 
                    $pourcentage = $total_paiements > 0 ? ($paiement['total'] / $total_paiements) * 100 : 0;
                ?>
                <tr>
                    <td><?= ucfirst($paiement['mode_paiement']) ?></td>
                    <td><?= number_format($paiement['total'], 2, ',', ' ') ?> €</td>
                    <td><?= number_format($pourcentage, 1, ',', ' ') ?> %</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="card">
        <h3>Top 10 des services vendus</h3>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Quantité</th>
                    <th>Chiffre d'affaires</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($produits_vendus as $produit): ?>
                <tr>
                    <td><?= $produit['nom'] ?></td>
                    <td><?= $produit['total_vendu'] ?></td>
                    <td><?= number_format($produit['chiffre_affaires'], 2, ',', ' ') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
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
        borderColor: '#3498db',
        backgroundColor: 'rgba(52, 152, 219, 0.1)',
        fill: true,
        tension: 0.4
    }]
};

// Données pour les produits vendus
const produitsData = {
    labels: [<?php 
        foreach($produits_vendus as $produit) {
            echo "'" . addslashes($produit['nom']) . "',";
        }
    ?>],
    datasets: [{
        label: 'Quantité vendue',
        data: [<?php 
            foreach($produits_vendus as $produit) {
                echo $produit['total_vendu'] . ',';
            }
        ?>],
        backgroundColor: [
            '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6',
            '#1abc9c', '#d35400', '#c0392b', '#16a085', '#2980b9'
        ]
    }]
};

// Initialisation des graphiques
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution
    const ctxEvolution = document.getElementById('chartEvolution').getContext('2d');
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

    // Graphique des produits
    const ctxProduits = document.getElementById('chartProduits').getContext('2d');
    new Chart(ctxProduits, {
        type: 'bar',
        data: produitsData,
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Services les plus vendus'
                }
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?>
