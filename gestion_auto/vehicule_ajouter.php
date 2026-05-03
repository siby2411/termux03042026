<?php
/**
 * OMEGA AUTO - VEHICULE_AJOUTER.PHP
 * Version Finale : SQL Robuste + Gestion Image
 */
require_once 'config.php';
$db = Database::getInstance();
$error_message = null;
$success_message = null;

try {
    $stmt_modeles = $db->getConnection()->query("SELECT * FROM modeles ORDER BY nom");
    $modeles = $stmt_modeles->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $immatriculation = trim($_POST['immatriculation'] ?? '');
        $modele_id = (int)($_POST['modele_id'] ?? 0);
        
        if (empty($immatriculation) || $modele_id === 0) {
            throw new Exception("L'immatriculation et le modèle sont obligatoires.");
        }

        // 1. Insertion du Véhicule
        $sql = "INSERT INTO vehicules (immatriculation, modele_id, prix_vente, type_vehicule, options, statut) 
                VALUES (:immat, :mod_id, :prix, :type, :opts, 'disponible')";

        $stmt_ins = $db->getConnection()->prepare($sql);
        $stmt_ins->execute([
            'immat' => $immatriculation,
            'mod_id' => $modele_id,
            'prix' => (float)($_POST['prix_vente'] ?? 0),
            'type' => $_POST['type_vehicule'] ?? 'vente',
            'opts' => $_POST['options'] ?? ''
        ]);

        $vehicule_id = $db->getConnection()->lastInsertId();

        // 2. GESTION DE L'IMAGE (Si présente)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/vehicules/';
            
            // Création du dossier si inexistant sur Termux
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $filename = "auto_" . $vehicule_id . "_" . time() . "." . $extension;
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                // Enregistrement dans la table vehicule_images pour l'affichage en liste
                $stmt_img = $db->getConnection()->prepare("INSERT INTO vehicule_images (vehicule_id, nom_fichier, est_principale) VALUES (?, ?, 1)");
                $stmt_img->execute([$vehicule_id, $filename]);
            }
        }

        $success_message = "Véhicule $immatriculation ajouté avec succès !";
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Véhicule - OMEGA AUTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .form-section { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle me-2"></i>Nouveau Véhicule</h2>
            <a href="vehicules.php" class="btn btn-outline-secondary">Retour au Parc</a>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-section">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Immatriculation *</label>
                    <input type="text" name="immatriculation" class="form-control" placeholder="DK-XXXX-X" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Modèle *</label>
                    <select name="modele_id" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($modeles as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Prix Vente (FCFA)</label>
                    <input type="number" name="prix_vente" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Photo du véhicule</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <label class="form-label">Options / Description</label>
                    <textarea name="options" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-lg w-100 mt-4">Enregistrer et Publier</button>
        </form>
    </div>
</body>
</html>
