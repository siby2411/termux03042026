<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$id = intval($_GET['id'] ?? 0);

$query = "SELECT e.nom, e.prenom, e.code_etudiant, c.nom_class, e.adresse, ce.photo_path
          FROM etudiants e
          JOIN classes c ON e.classe_id = c.id
          LEFT JOIN cartes_etudiants ce ON e.id = ce.etudiant_id
          WHERE e.id = $id";
$result = $conn->query($query);
$e = $result ? $result->fetch_assoc() : null;

if (!$e) { die("<h1>Étudiant introuvable</h1>"); }

// Gestion image profil
$photo = (!empty($e['photo_path']) && file_exists($e['photo_path'])) ? $e['photo_path'] : 'assets/default_avatar.png';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier Étudiant - OMEGA ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <style>
        body { background: #f4f7f6; }
        .main-container { max-width: 900px; margin: 30px auto; }
        .profile-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 30px; }
        .profile-img { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #0d6efd; }
    </style>
</head>
<body>
<div class="main-container">
    <div class="profile-card mb-5">
        <img src="<?= htmlspecialchars($photo) ?>" class="profile-img" alt="Photo Étudiant">
        <div>
            <h2 class="text-primary"><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></h2>
            <p class="mb-1"><strong>Code:</strong> <?= htmlspecialchars($e['code_etudiant']) ?></p>
            <p class="mb-1"><strong>Classe:</strong> <?= htmlspecialchars($e['nom_class']) ?></p>
        </div>
        <div class="ms-auto text-center">
            <?php
            $url_verif = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/verification.php?id=" . $id;
            $qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($url_verif);
            ?>
            <img src="<?= $qr ?>" style="width: 100px;">
        </div>
    </div>

    <div class="card p-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-primary"><i class="bi bi-calendar-week"></i> Emploi du Temps</h4>
            <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('vue-tableau').classList.toggle('d-none')">Tableau / Calendrier</button>
        </div>
        <div id='calendar'></div>
        <div id="vue-tableau" class="d-none mt-4">
            <?php include 'tableau_emploi.php'; ?>
        </div>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    new FullCalendar.Calendar(document.getElementById('calendar'), {
      initialView: 'timeGridWeek',
      events: 'api_emploi.php?id=<?= $id ?>',
      height: 'auto'
    }).render();
  });
</script>
</body>
</html>
