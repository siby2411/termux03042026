<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM equipements WHERE id = ?");
$stmt->execute([$id]);
$e = $stmt->fetch();

if (!$e) { die("Équipement introuvable."); }

$types = ['IRM', 'SCANNER', 'RADIO', 'MAMMO', 'ECHO', 'DENSITO'];
$statuts = ['OPERATIONNEL' => 'Opérationnel', 'MAINTENANCE' => 'En maintenance', 'PANNE' => 'En panne', 'HS' => 'Hors service'];

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $type = $_POST['type'] ?? '';
    $marque = trim($_POST['marque'] ?? '');
    $modele = trim($_POST['modele'] ?? '');
    $numero_serie = trim($_POST['numero_serie'] ?? '');
    $date_acquisition = $_POST['date_acquisition'] ?? '';
    $date_derniere_maintenance = $_POST['date_derniere_maintenance'] ?? null;
    $prochaine_maintenance = $_POST['prochaine_maintenance'] ?? null;
    $statut = $_POST['statut'] ?? 'OPERATIONNEL';
    $notes = trim($_POST['notes'] ?? '');

    if (!$nom || !$type || !$marque || !$modele || !$numero_serie || !$date_acquisition) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE equipements SET nom=?, type=?, marque=?, modele=?, numero_serie=?, date_acquisition=?, date_derniere_maintenance=?, prochaine_maintenance=?, statut=?, notes=? WHERE id=?");
            $stmt->execute([$nom, $type, $marque, $modele, $numero_serie, $date_acquisition, $date_derniere_maintenance, $prochaine_maintenance, $statut, $notes, $id]);
            $success = "Équipement modifié avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modifier équipement</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier équipement : <?= escape($e['nom']) ?></h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" value="<?= escape($e['nom']) ?>" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Type *</label><select name="type" class="form-control" required>
                    <?php foreach ($types as $t): ?>
                    <option value="<?= $t ?>" <?= $e['type'] == $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-4 mb-3"><label>Marque *</label><input type="text" name="marque" value="<?= escape($e['marque']) ?>" class="form-control" required></div>
                <div class="col-md-4 mb-3"><label>Modèle *</label><input type="text" name="modele" value="<?= escape($e['modele']) ?>" class="form-control" required></div>
                <div class="col-md-4 mb-3"><label>Numéro série *</label><input type="text" name="numero_serie" value="<?= escape($e['numero_serie']) ?>" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Date acquisition *</label><input type="date" name="date_acquisition" value="<?= $e['date_acquisition'] ?>" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Date dernière maintenance</label><input type="date" name="date_derniere_maintenance" value="<?= $e['date_derniere_maintenance'] ?>" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Prochaine maintenance</label><input type="date" name="prochaine_maintenance" value="<?= $e['prochaine_maintenance'] ?>" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Statut</label><select name="statut" class="form-control">
                    <?php foreach ($statuts as $key => $val): ?>
                    <option value="<?= $key ?>" <?= $e['statut'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-12 mb-3"><label>Notes</label><textarea name="notes" class="form-control" rows="2"><?= escape($e['notes']) ?></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
