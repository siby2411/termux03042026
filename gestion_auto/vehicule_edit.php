<?php
/**
 * OMEGA AUTO - VEHICULE_EDIT.PHP
 * Edition complète du véhicule avec protection PHP 8.1+
 */
require_once 'config.php';
$db = Database::getInstance();
$error = null;
$success = null;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // 1. Récupération des données du véhicule
    $stmt = $db->getConnection()->prepare("SELECT * FROM vehicules WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $v = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$v) die("Véhicule introuvable.");

    // 2. Listes pour les dropdowns
    $marques = $db->getConnection()->query("SELECT * FROM marques ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    $modeles = $db->getConnection()->query("SELECT * FROM modeles ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Traitement du formulaire de mise à jour
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sql = "UPDATE vehicules SET 
                immatriculation = ?, modele_id = ?, annee_circulation = ?, 
                kilometrage = ?, couleur = ?, prix_vente = ?, 
                carburant = ?, boite_vitesse = ?, statut = ?,
                places = ?, portes = ?, puissance = ?, 
                options = ?, description = ?
                WHERE id = ?";
        
        $params = [
            $_POST['immatriculation'] ?? '', (int)$_POST['modele_id'], (int)$_POST['annee_circulation'],
            (int)$_POST['kilometrage'], $_POST['couleur'] ?? '', (float)$_POST['prix_vente'],
            $_POST['carburant'] ?? '', $_POST['boite_vitesse'] ?? '', $_POST['statut'] ?? 'disponible',
            (int)$_POST['places'], (int)$_POST['portes'], (int)$_POST['puissance'],
            $_POST['options'] ?? '', $_POST['description'] ?? '', $id
        ];

        $stmt_upd = $db->getConnection()->prepare($sql);
        $stmt_upd->execute($params);
        
        $success = "La fiche du véhicule " . htmlspecialchars($_POST['immatriculation']) . " a été mise à jour.";
        // Recharger les données fraîches
        header("Refresh:2; url=vehicule_details.php?id=$id");
    }

} catch (PDOException $e) {
    $error = "Erreur SQL : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le véhicule - OMEGA AUTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .edit-card { background: white; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-pencil-square me-2"></i>Modification Véhicule</h3>
            <a href="vehicule_details.php?id=<?= $id ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>

        <?php if ($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
        <?php if ($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>

        <form method="POST" class="edit-card p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Immatriculation</label>
                    <input type="text" name="immatriculation" class="form-control" value="<?= htmlspecialchars($v['immatriculation'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Modèle</label>
                    <select name="modele_id" class="form-select">
                        <?php foreach($modeles as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= ($v['modele_id'] == $m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Statut</label>
                    <select name="statut" class="form-select bg-light fw-bold">
                        <option value="disponible" <?= ($v['statut'] == 'disponible') ? 'selected' : '' ?>>Disponible</option>
                        <option value="vendu" <?= ($v['statut'] == 'vendu') ? 'selected' : '' ?>>Vendu</option>
                        <option value="loué" <?= ($v['statut'] == 'loué') ? 'selected' : '' ?>>Loué</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Prix Vente (FCFA)</label>
                    <input type="number" name="prix_vente" class="form-control" value="<?= (float)($v['prix_vente'] ?? 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kilométrage</label>
                    <input type="number" name="kilometrage" class="form-control" value="<?= (int)($v['kilometrage'] ?? 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Énergie</label>
                    <select name="carburant" class="form-select">
                        <option value="essence" <?= ($v['carburant'] == 'essence') ? 'selected' : '' ?>>Essence</option>
                        <option value="diesel" <?= ($v['carburant'] == 'diesel') ? 'selected' : '' ?>>Diesel</option>
                        <option value="hybride" <?= ($v['carburant'] == 'hybride') ? 'selected' : '' ?>>Hybride</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Boîte</label>
                    <select name="boite_vitesse" class="form-select">
                        <option value="manuelle" <?= ($v['boite_vitesse'] == 'manuelle') ? 'selected' : '' ?>>Manuelle</option>
                        <option value="automatique" <?= ($v['boite_vitesse'] == 'automatique') ? 'selected' : '' ?>>Automatique</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <label class="form-label">Options (Clim, GPS, Jantes...)</label>
                    <textarea name="options" class="form-control" rows="2"><?= htmlspecialchars($v['options'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Description commerciale</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($v['description'] ?? '') ?></textarea>
                </div>
            </div>

            <hr class="my-4">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-save me-2"></i>Enregistrer les modifications
            </button>
        </form>
    </div>
</body>
</html>
