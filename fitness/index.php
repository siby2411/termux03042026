<?php
require_once 'config/database.php';
include 'header.php';
$database = new Database();
$db = $database->getConnection();

// Statistiques
$stats = [];
$query = "SELECT COUNT(*) as total FROM adherents WHERE statut='actif'";
$stmt = $db->query($query);
$stats['adherents'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM formateurs WHERE statut='actif'";
$stmt = $db->query($query);
$stats['formateurs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM cours WHERE actif=1";
$stmt = $db->query($query);
$stats['cours'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COALESCE(SUM(montant),0) as total FROM paiements WHERE MONTH(date_paiement)=MONTH(CURRENT_DATE()) AND statut='valide'";
$stmt = $db->query($query);
$stats['revenus_mois'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer toutes les disciplines pour le carrousel
$disciplines = $db->query("SELECT * FROM disciplines WHERE actif=1 ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Message inspirant aléatoire
$messages = [
    "La discipline que vous pratiquez aujourd'hui construit le champion de demain.",
    "Votre seule limite est celle que vous vous imposez.",
    "Chaque séance vous rapproche de votre meilleure version.",
    "La douleur que vous ressentez aujourd'hui sera la force que vous ressentirez demain.",
    "Le succès n'est pas un hasard, c'est le résultat de la discipline quotidienne.",
    "Un corps sain dans un esprit sain - La devise d'Oméga Fitness"
];
$message_du_jour = $messages[array_rand($messages)];

// Cours du jour
$query = "SELECT c.*, d.nom as discipline, CONCAT(f.prenom,' ',f.nom) as formateur 
          FROM cours c 
          JOIN disciplines d ON c.discipline_id=d.id 
          JOIN formateurs f ON c.formateur_id=f.id 
          WHERE c.jour = UPPER(DAYNAME(CURRENT_DATE())) AND c.actif=1 
          ORDER BY c.heure_debut";
$cours_jour = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <!-- Bannière inspirante -->
    <div class="alert alert-info text-center mb-4" style="background: linear-gradient(135deg, #FF4B2B, #FF416C); color: white; border: none;">
        <i class="fas fa-quote-left fa-2x mb-2"></i>
        <h4>"<?= htmlspecialchars($message_du_jour) ?>"</h4>
        <p class="mb-0">🏆 Oméga Fitness - L'excellence en mouvement</p>
    </div>

    <!-- Carrousel des disciplines -->
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-dumbbell"></i> Nos disciplines à l'honneur</h3>
        </div>
        <div class="card-body">
            <div class="disciplines-slider">
                <div class="slider-track">
                    <?php foreach($disciplines as $d): ?>
                    <div class="discipline-card">
                        <i class="fas fa-fist-raised fa-2x" style="color: #FF4B2B"></i>
                        <h5><?= htmlspecialchars($d['nom']) ?></h5>
                        <p><?= number_format($d['tarif_mensuel'], 0, ',', ' ') ?> F/mois</p>
                        <small><?= $d['age_minimum'] ?> ans minimum</small>
                    </div>
                    <?php endforeach; ?>
                    <!-- Duplication pour effet infini -->
                    <?php foreach($disciplines as $d): ?>
                    <div class="discipline-card">
                        <i class="fas fa-fist-raised fa-2x" style="color: #FF4B2B"></i>
                        <h5><?= htmlspecialchars($d['nom']) ?></h5>
                        <p><?= number_format($d['tarif_mensuel'], 0, ',', ' ') ?> F/mois</p>
                        <small><?= $d['age_minimum'] ?> ans minimum</small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h3 class="stat-number"><?= $stats['adherents'] ?></h3>
                <p>Adhérents Actifs</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c)">
                <i class="fas fa-chalkboard-user fa-3x mb-3"></i>
                <h3 class="stat-number"><?= $stats['formateurs'] ?></h3>
                <p>Formateurs Experts</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe)">
                <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                <h3 class="stat-number"><?= count($disciplines) ?></h3>
                <p>Disciplines</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7)">
                <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                <h3 class="stat-number"><?= number_format($stats['revenus_mois'], 0, ',', ' ') ?> F</h3>
                <p>Revenus du Mois</p>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-day"></i> Cours du Jour - <?= date('l d/m/Y') ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>Horaire</th><th>Discipline</th><th>Formateur</th><th>Salle</th><th>Places</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($cours_jour as $cours): ?>
                                <tr>
                                    <td><?= date('H:i', strtotime($cours['heure_debut'])) ?> - <?= date('H:i', strtotime($cours['heure_fin'])) ?></td>
                                    <td><strong><?= htmlspecialchars($cours['discipline']) ?></strong></td>
                                    <td><?= htmlspecialchars($cours['formateur']) ?></td>
                                    <td><?= htmlspecialchars($cours['salle']) ?></td>
                                    <td><?= $cours['inscrits'] ?>/<?= $cours['capacite'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($cours_jour)): ?>
                                <tr><td colspan="5" class="text-center">Aucun cours programmé aujourd'hui</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Actions Rapides
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="adherents.php?action=add" class="btn btn-primary"><i class="fas fa-user-plus"></i> Nouvel Adhérent</a>
                        <a href="paiements.php" class="btn btn-success"><i class="fas fa-hand-holding-usd"></i> Encaisser Paiement</a>
                        <a href="performance.php" class="btn btn-info"><i class="fas fa-chart-line"></i> Suivi Performance</a>
                        <a href="challenges.php" class="btn btn-warning"><i class="fas fa-trophy"></i> Défis du Mois</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.disciplines-slider {
    overflow: hidden;
    position: relative;
    white-space: nowrap;
}

.slider-track {
    display: inline-block;
    animation: scroll 30s linear infinite;
}

.discipline-card {
    display: inline-block;
    width: 200px;
    margin: 0 10px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    text-align: center;
    transition: transform 0.3s ease;
    white-space: normal;
    vertical-align: top;
}

.discipline-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    background: linear-gradient(135deg, #fff, #f0f0f0);
}

@keyframes scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.disciplines-slider:hover .slider-track {
    animation-play-state: paused;
}

.stat-card {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 20px;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    margin: 10px 0;
}
</style>

<?php include 'footer.php'; ?>
