<?php
require_once 'config/database.php';
include 'header.php';
$database = new Database();
$db = $database->getConnection();

// Statistiques
$stats = [];
$query = "SELECT COUNT(*) as total FROM adherents WHERE statut='actif'";
$stats['adherents'] = $db->query($query)->fetchColumn();

$query = "SELECT COUNT(*) as total FROM formateurs WHERE statut='actif'";
$stats['formateurs'] = $db->query($query)->fetchColumn();

$query = "SELECT COALESCE(SUM(montant),0) as total FROM paiements WHERE MONTH(date_paiement)=MONTH(CURRENT_DATE()) AND statut='valide'";
$stats['revenus_mois'] = $db->query($query)->fetchColumn();

// Toutes les disciplines
$disciplines = $db->query("SELECT * FROM disciplines WHERE actif=1 ORDER BY nom")->fetchAll();

// Message inspirant aléatoire
$messages = [
    "La discipline que vous pratiquez aujourd'hui construit le champion de demain.",
    "Votre seule limite est celle que vous vous imposez.",
    "Chaque séance vous rapproche de votre meilleure version.",
    "La douleur que vous ressentez aujourd'hui sera la force que vous ressentirez demain.",
    "Le succès n'est pas un hasard, c'est le résultat de la discipline quotidienne."
];
$message_du_jour = $messages[array_rand($messages)];
?>

<div class="container">
    <!-- Bannière inspirante -->
    <div class="alert alert-info text-center mb-4" style="background: linear-gradient(135deg, #FF4B2B, #FF416C); color: white; border: none; animation: pulse 2s infinite;">
        <i class="fas fa-quote-left fa-2x mb-2"></i>
        <h4>"<?= $message_du_jour ?>"</h4>
        <p class="mb-0">🏆 Oméga Fitness - L'excellence en mouvement</p>
    </div>

    <!-- Carrousel des disciplines -->
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-dumbbell"></i> Nos disciplines - Défilement continu</h3>
        </div>
        <div class="card-body">
            <div class="disciplines-slider">
                <div class="slider-track">
                    <?php foreach($disciplines as $d): ?>
                    <div class="discipline-card">
                        <i class="fas fa-fist-raised fa-2x" style="color: #FF4B2B"></i>
                        <h5><?= $d['nom'] ?></h5>
                        <p><?= number_format($d['tarif_mensuel'], 0, ',', ' ') ?> F/mois</p>
                        <small><?= $d['age_minimum'] ?> ans minimum</small>
                    </div>
                    <?php endforeach; ?>
                    <!-- Dupliquer pour effet infini -->
                    <?php foreach($disciplines as $d): ?>
                    <div class="discipline-card">
                        <i class="fas fa-fist-raised fa-2x" style="color: #FF4B2B"></i>
                        <h5><?= $d['nom'] ?></h5>
                        <p><?= number_format($d['tarif_mensuel'], 0, ',', ' ') ?> F/mois</p>
                        <small><?= $d['age_minimum'] ?> ans minimum</small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques et actions rapides -->
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
}

.discipline-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

@keyframes scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.disciplines-slider:hover .slider-track {
    animation-play-state: paused;
}
</style>

<?php include 'footer.php'; ?>
