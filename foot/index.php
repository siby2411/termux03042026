<?php
require_once 'includes/config.php';
redirectIfNotLoggedIn();

// Récupération de quelques stats pour le dashboard
$totalTrainees = $pdo->query("SELECT COUNT(*) FROM trainees")->fetchColumn();
$activeFields = $pdo->query("SELECT COUNT(*) FROM fields WHERE available=1")->fetchColumn();
$upcomingAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date > NOW()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Foot School Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="banner">
        OMEGA INFORMATIQUE CONSULTING | Génération Foot Sénégal
    </div>
    <div class="marquee-container">
        <div class="marquee">
            <img src="assets/images/sponsor1.png" alt="Sponsor 1">
            <img src="assets/images/sponsor2.png" alt="Sponsor 2">
            <img src="assets/images/pub1.png" alt="Pub 1">
            <img src="assets/images/sponsor1.png" alt="Sponsor 1">
            <img src="assets/images/sponsor2.png" alt="Sponsor 2">
            <img src="assets/images/pub1.png" alt="Pub 1">
        </div>
    </div>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="calendar.php">Calendrier</a></li>
            <li><a href="recruitment.php">Recrutement</a></li>
            <li><a href="fields_rooms.php">Terrains & Salles</a></li>
            <li><a href="assets.php">Immobilisations</a></li>
            <li><a href="appointments.php">Rendez-vous Manager</a></li>
            <li><a href="secretariat.php">Secrétariat</a></li>
            <li><a href="trainee_monitoring.php">Suivi Stagiaires</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    <main>
        <h1>Tableau de bord</h1>
        <div class="stats">
            <div class="card">
                <h3>Stagiaires</h3>
                <p><?= $totalTrainees ?></p>
            </div>
            <div class="card">
                <h3>Terrains disponibles</h3>
                <p><?= $activeFields ?></p>
            </div>
            <div class="card">
                <h3>Rendez-vous à venir</h3>
                <p><?= $upcomingAppointments ?></p>
            </div>
        </div>
        <div class="welcome">
            <p>Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)</p>
        </div>
        <!-- Ici on pourrait intégrer un graphique ou des actualités -->
    </main>
    <script src="assets/js/script.js"></script>
</body>
</html>
