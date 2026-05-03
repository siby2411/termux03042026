<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

$categories = [
    'IRM_CEREBRALE' => 'IRM Cérébrale', 'IRM_RACHIS' => 'IRM Rachis', 'IRM_ARTICULAIRE' => 'IRM Articulaire',
    'SCANNER_THORAX' => 'Scanner Thorax', 'SCANNER_ABDOMEN' => 'Scanner Abdomen', 'SCANNER_CRANE' => 'Scanner Crâne',
    'RADIO_THORAX' => 'Radiographie Thorax', 'RADIO_OS' => 'Radiographie Os',
    'MAMMOGRAPHIE' => 'Mammographie', 'ECHO_ABDOMEN' => 'Échographie Abdomen', 'ECHO_PELVIS' => 'Échographie Pelvis',
    'DENSITOMETRIE' => 'Densitométrie'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $categorie = $_POST['categorie'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $duree_estimee = (int)($_POST['duree_estimee'] ?? 0);
    $tarif = (float)($_POST['tarif'] ?? 0);
    $preparation = trim($_POST['preparation'] ?? '');
    $contre_indications = trim($_POST['contre_indications'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;

    if (!$nom || !$categorie || !$duree_estimee || !$tarif) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO examens (nom, categorie, description, duree_estimee, tarif, preparation, contre_indications, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $categorie, $description, $duree_estimee, $tarif, $preparation, $contre_indications, $actif]);
            $success = "Examen ajouté avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter examen</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un examen</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Catégorie *</label><select name="categorie" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($categories as $key => $val): ?>
                    <option value="<?= $key ?>"><?= $val ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-12 mb-3"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="col-md-4 mb-3"><label>Durée (minutes) *</label><input type="number" name="duree_estimee" class="form-control" required></div>
                <div class="col-md-4 mb-3"><label>Tarif (FCFA) *</label><input type="number" name="tarif" class="form-control" step="1000" required></div>
                <div class="col-md-4 mb-3"><div class="form-check mt-4"><input type="checkbox" name="actif" class="form-check-input" checked> <label>Actif</label></div></div>
                <div class="col-12 mb-3"><label>Préparation</label><textarea name="preparation" class="form-control" rows="2"></textarea></div>
                <div class="col-12 mb-3"><label>Contre-indications</label><textarea name="contre_indications" class="form-control" rows="2"></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
