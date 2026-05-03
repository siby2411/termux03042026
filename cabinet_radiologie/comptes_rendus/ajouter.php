<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$rendezvous_id = $_GET['rendezvous_id'] ?? 0;

if (!$rendezvous_id) {
    header('Location: ../rendezvous/liste.php');
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, CONCAT(u.last_name, ' ', u.first_name) as patient_nom, e.nom as examen_nom 
                       FROM rendezvous r
                       JOIN patients p ON r.patient_id = p.id
                       JOIN users u ON p.user_id = u.id
                       JOIN examens e ON r.examen_id = e.id
                       WHERE r.id = ?");
$stmt->execute([$rendezvous_id]);
$rdv = $stmt->fetch();

if (!$rdv) { die("Rendez-vous introuvable."); }

$existing = $pdo->prepare("SELECT id FROM comptes_rendus WHERE rendezvous_id = ?")->execute([$rendezvous_id])->fetch();
if ($existing) {
    header('Location: modifier.php?id=' . $existing['id']);
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $indication = trim($_POST['indication'] ?? '');
    $technique = trim($_POST['technique'] ?? '');
    $comparaison = trim($_POST['comparaison'] ?? '');
    $resultats = trim($_POST['resultats'] ?? '');
    $conclusion = trim($_POST['conclusion'] ?? '');
    $recommandations = trim($_POST['recommandations'] ?? '');
    $radiologue_id = $_SESSION['role'] == 'radiologue' ? currentUserId() : ($_POST['radiologue_id'] ?? 0);

    if (!$indication || !$technique || !$resultats || !$conclusion || !$radiologue_id) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO comptes_rendus (rendezvous_id, radiologue_id, indication, technique, comparaison, resultats, conclusion, recommandations) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$rendezvous_id, $radiologue_id, $indication, $technique, $comparaison, $resultats, $conclusion, $recommandations]);
            $success = "Compte rendu ajouté avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

$radiologues = $pdo->query("SELECT r.id, CONCAT(u.last_name, ' ', u.first_name) as nom FROM radiologues r JOIN users u ON r.user_id = u.id WHERE r.actif = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter compte rendu</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter compte rendu</h2>
        <p><strong>Patient :</strong> <?= escape($rdv['patient_nom']) ?></p>
        <p><strong>Examen :</strong> <?= escape($rdv['examen_nom']) ?></p>
        <p><strong>Date RDV :</strong> <?= formatDate($rdv['date']) ?></p>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label>Indication *</label><textarea name="indication" class="form-control" rows="2" required></textarea></div>
            <div class="mb-3"><label>Technique *</label><textarea name="technique" class="form-control" rows="2" required></textarea></div>
            <div class="mb-3"><label>Comparaison (examens précédents)</label><textarea name="comparaison" class="form-control" rows="2"></textarea></div>
            <div class="mb-3"><label>Résultats *</label><textarea name="resultats" class="form-control" rows="4" required></textarea></div>
            <div class="mb-3"><label>Conclusion *</label><textarea name="conclusion" class="form-control" rows="3" required></textarea></div>
            <div class="mb-3"><label>Recommandations</label><textarea name="recommandations" class="form-control" rows="2"></textarea></div>
            <?php if ($_SESSION['role'] != 'radiologue'): ?>
            <div class="mb-3"><label>Radiologue *</label><select name="radiologue_id" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($radiologues as $r): ?>
                <option value="<?= $r['id'] ?>"><?= escape($r['nom']) ?></option>
                <?php endforeach; ?>
            </select></div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="../rendezvous/fiche.php?id=<?= $rendezvous_id ?>" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
