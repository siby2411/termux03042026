<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

// Statistiques financières
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Revenus par mois
$query = "SELECT MONTH(date_paiement) as mois, SUM(montant) as total 
          FROM paiements WHERE YEAR(date_paiement)=:year AND statut='valide' 
          GROUP BY MONTH(date_paiement) ORDER BY mois";
$stmt = $db->prepare($query);
$stmt->execute([':year' => $year]);
$revenus_mensuels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Charges
$query = "SELECT MONTH(date_charge) as mois, SUM(montant) as total 
          FROM charges WHERE YEAR(date_charge)=:year 
          GROUP BY MONTH(date_charge) ORDER BY mois";
$stmt = $db->prepare($query);
$stmt->execute([':year' => $year]);
$charges_mensuelles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total revenus année
$query = "SELECT SUM(montant) as total FROM paiements WHERE YEAR(date_paiement)=:year AND statut='valide'";
$stmt = $db->prepare($query);
$stmt->execute([':year' => $year]);
$total_revenus = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$query = "SELECT SUM(montant) as total FROM charges WHERE YEAR(date_charge)=:year";
$stmt = $db->prepare($query);
$stmt->execute([':year' => $year]);
$total_charges = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$benefice = $total_revenus - $total_charges;
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> États Financiers - Oméga Fitness</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card" style="background: linear-gradient(135deg, #11998e, #38ef7d)">
                        <h3 class="stat-number"><?= number_format($total_revenus, 0, ',', ' ') ?> F</h3>
                        <p>Revenus Totaux <?= $year ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card" style="background: linear-gradient(135deg, #eb3349, #f45c43)">
                        <h3 class="stat-number"><?= number_format($total_charges, 0, ',', ' ') ?> F</h3>
                        <p>Charges Totales</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe)">
                        <h3 class="stat-number"><?= number_format($benefice, 0, ',', ' ') ?> F</h3>
                        <p>Bénéfice Net</p>
                    </div>
                </div>
            </div>

            <canvas id="financesChart" height="100"></canvas>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h5>Détail des Revenus Mensuels</h5>
                    <table class="table">
                        <thead><tr><th>Mois</th><th>Montant</th></tr></thead>
                        <tbody>
                        <?php foreach($revenus_mensuels as $r): ?>
                        <tr><td><?= date('F', mktime(0,0,0,$r['mois'],1)) ?></td>
                        <td><?= number_format($r['total'], 0, ',', ' ') ?> F</td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Ajouter une Charge</h5>
                    <form method="POST" action="add_charge.php">
                        <div class="mb-2"><input type="text" name="description" class="form-control" placeholder="Description" required></div>
                        <div class="mb-2"><input type="number" name="montant" class="form-control" placeholder="Montant" required></div>
                        <div class="mb-2">
                            <select name="categorie" class="form-control">
                                <option>materiel</option><option>entretien</option><option>eau_electricite</option>
                                <option>loyer</option><option>salaires</option><option>marketing</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter Charge</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('financesChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'],
        datasets: [{
            label: 'Revenus',
            data: <?php 
                $rev_data = array_fill(0,12,0);
                foreach($revenus_mensuels as $r) $rev_data[$r['mois']-1] = $r['total'];
                echo json_encode($rev_data);
            ?>,
            borderColor: '#38ef7d',
            backgroundColor: 'rgba(56,239,125,0.1)',
            tension: 0.4
        },{
            label: 'Charges',
            data: <?php 
                $charge_data = array_fill(0,12,0);
                foreach($charges_mensuelles as $c) $charge_data[$c['mois']-1] = $c['total'];
                echo json_encode($charge_data);
            ?>,
            borderColor: '#f45c43',
            backgroundColor: 'rgba(244,92,67,0.1)',
            tension: 0.4
        }]
    }
});
</script>

<?php include 'footer.php'; ?>
