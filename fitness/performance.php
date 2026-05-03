<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les adhérents pour le filtre
$adherents = $db->query("SELECT id, nom, prenom, discipline_principale FROM adherents WHERE statut='actif'")->fetchAll();

// Statistiques de progression
$stats = [
    'total_seances' => $db->query("SELECT COUNT(*) FROM presences WHERE statut='present'")->fetchColumn(),
    'total_adherents_actifs' => $db->query("SELECT COUNT(*) FROM adherents WHERE statut='actif'")->fetchColumn(),
    'assiduite_moyenne' => number_format($db->query("SELECT AVG(CASE WHEN statut='present' THEN 100 ELSE 0 END) FROM presences")->fetchColumn(), 1),
    'disciplines_populaires' => $db->query("SELECT discipline_principale, COUNT(*) as total FROM adherents GROUP BY discipline_principale ORDER BY total DESC LIMIT 3")->fetchAll()
];
?>

<div class="container">
    <div class="alert alert-info text-center mb-4" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none;">
        <i class="fas fa-heartbeat fa-2x mb-2"></i>
        <h3>"Le sport ne construit pas seulement le corps, il forge l'esprit et révèle le champion qui sommeille en vous."</h3>
        <p class="mb-0">- Oméga Fitness - Votre excellence, notre mission</p>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #11998e, #38ef7d)">
                <i class="fas fa-calendar-check fa-3x mb-3"></i>
                <h3 class="stat-number"><?= $stats['total_seances'] ?></h3>
                <p>Séances suivies</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c)">
                <i class="fas fa-trophy fa-3x mb-3"></i>
                <h3 class="stat-number"><?= $stats['assiduite_moyenne'] ?>%</h3>
                <p>Assiduité moyenne</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-simple"></i> Disciplines les plus populaires
                </div>
                <div class="card-body">
                    <?php foreach($stats['disciplines_populaires'] as $d): ?>
                    <div class="mb-2">
                        <strong><?= $d['discipline_principale'] ?></strong>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= ($d['total']/$stats['total_adherents_actifs'])*100 ?>%; background: linear-gradient(90deg, #FF4B2B, #FF416C);">
                                <?= $d['total'] ?> adhérents
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i> Progression mensuelle
                </div>
                <div class="card-body">
                    <canvas id="progressionChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-medal"></i> Top Performers
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead><tr><th>Adhérent</th><th>Discipline</th><th>Séances</th><th>Taux</th></tr></thead>
                        <tbody>
                            <?php
                            $top = $db->query("SELECT a.nom, a.prenom, a.discipline_principale, COUNT(p.id) as seances,
                                              ROUND(COUNT(p.id) * 100 / 30, 1) as taux
                                              FROM adherents a 
                                              JOIN presences p ON a.id = p.adherent_id 
                                              WHERE MONTH(p.date_seance) = MONTH(CURRENT_DATE())
                                              GROUP BY a.id ORDER BY seances DESC LIMIT 5")->fetchAll();
                            foreach($top as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['prenom'] . ' ' . $t['nom']) ?></td>
                                <td><span class="badge bg-info"><?= $t['discipline_principale'] ?></span></td>
                                <td><?= $t['seances'] ?></td>
                                <td><div class="progress"><div class="progress-bar bg-success" style="width: <?= $t['taux'] ?>%"><?= $t['taux'] ?>%</div></div></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
fetch('api_stats.php')
    .then(response => response.json())
    .then(data => {
        new Chart(document.getElementById('progressionChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                datasets: [{
                    label: 'Nouveaux adhérents',
                    data: data.nouveaux_mois,
                    borderColor: '#FF4B2B',
                    tension: 0.4
                }]
            }
        });
    });
</script>

<?php include 'footer.php'; ?>
