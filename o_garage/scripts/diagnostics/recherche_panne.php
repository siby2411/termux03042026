<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

// Analyse statistique des types de pannes
$stats = $db->query("SELECT description_panne, COUNT(*) as nb, complexite 
                    FROM fiches_intervention 
                    GROUP BY description_panne 
                    ORDER BY nb DESC LIMIT 10")->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm bg-primary text-white p-4">
                <h3><i class="fas fa-map-marked-alt me-3"></i>Intelligence Diagnostic : Cartographie des Risques</h3>
                <p>Analyse des pannes récurrentes basées sur l'historique de l'atelier Omega Tech.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Top 10 des Pannes Détectées</strong></div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Symptôme / Panne</th>
                                <th>Fréquence</th>
                                <th>Complexité Moy.</th>
                                <th>Risque</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($stats as $s): ?>
                            <tr>
                                <td><strong><?= $s['description_panne'] ?></strong></td>
                                <td><span class="badge bg-light text-dark"><?= $s['nb'] ?> cas</span></td>
                                <td><?= $s['complexite'] ?></td>
                                <td>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-danger" style="width: <?= ($s['nb']*20) ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="text-primary"><i class="fas fa-lightbulb me-2"></i>Conseil Préventif</h5>
                    <hr>
                    <div class="alert alert-warning small">
                        <strong>Note Ingénieur :</strong> Les pannes de type "Surchauffe" et "Injecteurs" sont en hausse de 15% ce mois-ci. 
                        Recommander systématiquement le <strong>nettoyage du circuit de refroidissement</strong> à chaque révision de 150 000 km.
                    </div>
                    <button class="btn btn-outline-primary w-100 mb-2">Imprimer Rapport Statistique</button>
                    <button class="btn btn-dark w-100">Envoyer Alerte Générale (SMS/WA)</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
