<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: liste.php');
    exit;
}

$pdo = getPDO();

$stmt = $pdo->prepare("SELECT ar.*, a.nom as analyse_nom, p.code_barre, 
                              CONCAT(u.last_name, ' ', u.first_name) as patient_nom
                       FROM analyses_realisees ar
                       JOIN prelevements p ON ar.prelevement_id = p.id
                       JOIN patients pat ON p.patient_id = pat.id
                       JOIN users u ON pat.user_id = u.id
                       JOIN analyses a ON ar.analyse_id = a.id
                       WHERE ar.id = ?");
$stmt->execute([$id]);
$analyse = $stmt->fetch();

if (!$analyse) {
    die("Analyse non trouvée.");
}

// Récupérer les résultats
$stmt = $pdo->prepare("SELECT r.*, pa.nom as param_nom, pa.unite, pa.valeur_min, pa.valeur_max, pa.valeur_normale
                       FROM resultats_analyse r
                       JOIN parametres_analyse pa ON r.parametre_id = pa.id
                       WHERE r.analyse_realisee_id = ?
                       ORDER BY pa.ordre");
$stmt->execute([$id]);
$resultats = $stmt->fetchAll();

// Récupérer le compte rendu éventuel
$stmt = $pdo->prepare("SELECT * FROM comptes_rendus WHERE analyse_realisee_id = ?");
$stmt->execute([$id]);
$compte_rendu = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails analyse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Détails de l'analyse : <?= escape($analyse['analyse_nom']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>Patient</th><td><?= escape($analyse['patient_nom']) ?></td></tr>
                    <tr><th>Code barre</th><td><?= escape($analyse['code_barre']) ?></td></tr>
                    <tr><th>Date début</th><td><?= formatDateTime($analyse['date_debut']) ?></td></tr>
                    <tr><th>Date fin</th><td><?= formatDateTime($analyse['date_fin']) ?></td></tr>
                    <tr><th>Statut</th><td><?= escape($analyse['statut']) ?></td></tr>
                    <tr><th>Technicien</th><td><?= $analyse['technicien_id'] ? getUsernameById($analyse['technicien_id']) : '-' ?></td></tr>
                    <tr><th>Biologiste validateur</th><td><?= $analyse['biologiste_validateur_id'] ? getUsernameById($analyse['biologiste_validateur_id']) : '-' ?></td></tr>
                </table>
            </div>
        </div>

        <h3>Résultats</h3>
        <table class="table table-striped">
            <thead>
                <tr><th>Paramètre</th><th>Valeur</th><th>Unité</th><th>Norme</th><th>Interprétation</th><th>Commentaire</th>
            </thead>
            <tbody>
                <?php foreach ($resultats as $r): ?>
                <tr>
                    <td><?= escape($r['param_nom']) ?></td>
                    <td><?= escape($r['valeur']) ?></td>
                    <td><?= escape($r['unite']) ?></td>
                    <td>
                        <?php if ($r['valeur_min'] && $r['valeur_max']): ?>
                            <?= $r['valeur_min'] . ' - ' . $r['valeur_max'] ?>
                        <?php elseif ($r['valeur_normale']): ?>
                            <?= escape($r['valeur_normale']) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= escape($r['interpretation']) ?></td>
                    <td><?= escape($r['commentaire']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($compte_rendu): ?>
        <h3>Compte rendu</h3>
        <div class="card">
            <div class="card-body">
                <p><strong>Conclusion :</strong> <?= nl2br(escape($compte_rendu['conclusion'])) ?></p>
                <p><strong>Recommandations :</strong> <?= nl2br(escape($compte_rendu['recommandations'])) ?></p>
                <p><strong>Observations :</strong> <?= nl2br(escape($compte_rendu['observations'])) ?></p>
                <p><strong>Signé :</strong> <?= $compte_rendu['signe'] ? 'Oui' : 'Non' ?></p>
            </div>
        </div>
        <?php endif; ?>

        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <?php if ($analyse['statut'] == 'en_attente'): ?>
        <a href="resultats.php?id=<?= $id ?>" class="btn btn-primary">Saisir les résultats</a>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
