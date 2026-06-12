<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Correction : Autorise 'secretariat' ET 'admin'
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'secretariat' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$user_id = $_SESSION['user_id'];

// [Le reste de votre logique PHP reste identique...]
// Assurez-vous que le dossier existe : mkdir -p ../../uploads/rapports
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();
$message = ''; $message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generer_rapport'])) {
    // ... (votre logique de génération de rapport ici)
    $message = "Rapport généré avec succès !";
    $message_type = "success";
}

$rapports = $pdo->query("SELECT r.*, u.prenom, u.nom, s.name as service_nom FROM rapports r LEFT JOIN users u ON r.genere_par = u.id LEFT JOIN services s ON r.service_id = s.id ORDER BY r.date_generation DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Rapports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid p-4">
        <h3><i class="fas fa-chart-bar"></i> Gestion des Rapports</h3>
        <?php if ($message): ?><div class="alert alert-<?= $message_type ?>"><?= $message ?></div><?php endif; ?>
        </div>
</body>
</html>
