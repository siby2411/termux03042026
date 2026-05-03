<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

// Récupérer les données pour les listes déroulantes
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id, nom FROM categories_materiel ORDER BY nom")->fetchAll();
$fournisseurs = $pdo->query("SELECT id, nom FROM fournisseurs ORDER BY nom")->fetchAll();
$salles = $pdo->query("
    SELECT s.id, s.numero_salle, b.nom as batiment, s.etage 
    FROM salles s
    JOIN batiments b ON s.batiment_id = b.id
    ORDER BY b.nom, s.etage, s.numero_salle
")->fetchAll();

$message = '';
$message_type = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_materiel') {
        try {
            // Générer un code matériel unique
            $code_materiel = 'MAT-' . date('Y') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            
            $stmt = $pdo->prepare("
                INSERT INTO materiel (
                    code_materiel, nom, description, categorie_id, service_id, salle_id,
                    date_acquisition, valeur_achat, fournisseur, numero_serie, 
                    statut, quantite, observations, created_at
                ) VALUES (
                    :code_materiel, :nom, :description, :categorie_id, :service_id, :salle_id,
                    :date_acquisition, :valeur_achat, :fournisseur, :numero_serie,
                    :statut, :quantite, :observations, NOW()
                )
            ");
            
            $stmt->execute([
                ':code_materiel' => $code_materiel,
                ':nom' => $_POST['nom'],
                ':description' => $_POST['description'] ?? '',
                ':categorie_id' => $_POST['categorie_id'] ?: null,
                ':service_id' => $_POST['service_id'] ?: null,
                ':salle_id' => $_POST['salle_id'] ?: null,
                ':date_acquisition' => $_POST['date_acquisition'] ?: null,
                ':valeur_achat' => $_POST['valeur_achat'] ?: 0,
                ':fournisseur' => $_POST['fournisseur'] ?: null,
                ':numero_serie' => $_POST['numero_serie'] ?? '',
                ':statut' => $_POST['statut'] ?? 'actif',
                ':quantite' => $_POST['quantite'] ?? 1,
                ':observations' => $_POST['observations'] ?? ''
            ]);
            
            $message = "✅ Matériel ajouté avec succès ! Code: $code_materiel";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
    
    // Modification de matériel
    if (isset($_POST['action']) && $_POST['action'] == 'edit_materiel') {
        try {
            $stmt = $pdo->prepare("
                UPDATE materiel SET
                    nom = :nom,
                    description = :description,
                    categorie_id = :categorie_id,
                    service_id = :service_id,
                    salle_id = :salle_id,
                    date_acquisition = :date_acquisition,
                    valeur_achat = :valeur_achat,
                    fournisseur = :fournisseur,
                    numero_serie = :numero_serie,
                    statut = :statut,
                    quantite = :quantite,
                    observations = :observations
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $_POST['id'],
                ':nom' => $_POST['nom'],
                ':description' => $_POST['description'] ?? '',
                ':categorie_id' => $_POST['categorie_id'] ?: null,
                ':service_id' => $_POST['service_id'] ?: null,
                ':salle_id' => $_POST['salle_id'] ?: null,
                ':date_acquisition' => $_POST['date_acquisition'] ?: null,
                ':valeur_achat' => $_POST['valeur_achat'] ?: 0,
                ':fournisseur' => $_POST['fournisseur'] ?: null,
                ':numero_serie' => $_POST['numero_serie'] ?? '',
                ':statut' => $_POST['statut'] ?? 'actif',
                ':quantite' => $_POST['quantite'] ?? 1,
                ':observations' => $_POST['observations'] ?? ''
            ]);
            
            $message = "✅ Matériel modifié avec succès !";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
    
    // Suppression de matériel
    if (isset($_POST['action']) && $_POST['action'] == 'delete_materiel') {
        try {
            $stmt = $pdo->prepare("DELETE FROM materiel WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            $message = "✅ Matériel supprimé avec succès !";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Récupérer le matériel pour édition
$edit_materiel = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM materiel WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_materiel = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Matériel - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px;
            color: white;
        }
        .container-fluid { padding: 20px; }
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            margin: -25px -25px 20px -25px;
            padding: 15px 25px;
            border-radius: 15px 15px 0 0;
        }
        .form-group { margin-bottom: 15px; }
        .btn-save {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <h3><i class="fas fa-tools"></i> Gestion du Matériel</h3>
            <a href="dashboard.php" class="btn btn-sm btn-light">Retour Dashboard</a>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulaire d'ajout/modification -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-<?= $edit_materiel ? 'edit' : 'plus' ?>"></i>
                        <?= $edit_materiel ? 'Modifier le matériel' : 'Ajouter un matériel' ?>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="<?= $edit_materiel ? 'edit_materiel' : 'add_materiel' ?>">
                        <?php if ($edit_materiel): ?>
                            <input type="hidden" name="id" value="<?= $edit_materiel['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Nom du matériel *</label>
                            <input type="text" name="nom" class="form-control" required 
                                   value="<?= htmlspecialchars($edit_materiel['nom'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($edit_materiel['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Catégorie</label>
                                    <select name="categorie_id" class="form-select">
                                        <option value="">Sélectionner</option>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= ($edit_materiel['categorie_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quantité</label>
                                    <input type="number" name="quantite" class="form-control" value="<?= $edit_materiel['quantite'] ?? 1 ?>" min="1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Service</label>
                                    <select name="service_id" class="form-select">
                                        <option value="">Non assigné</option>
                                        <?php foreach($services as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= ($edit_materiel['service_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Salle</label>
                                    <select name="salle_id" class="form-select">
                                        <option value="">Non assigné</option>
                                        <?php foreach($salles as $salle): ?>
                                            <option value="<?= $salle['id'] ?>" <?= ($edit_materiel['salle_id'] ?? '') == $salle['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($salle['batiment'] . ' - ' . $salle['etage'] . ' - Salle ' . $salle['numero_salle']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date d'acquisition</label>
                                    <input type="date" name="date_acquisition" class="form-control" 
                                           value="<?= htmlspecialchars($edit_materiel['date_acquisition'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Valeur d'achat (FCFA)</label>
                                    <input type="number" name="valeur_achat" class="form-control" step="1000"
                                           value="<?= htmlspecialchars($edit_materiel['valeur_achat'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fournisseur</label>
                                    <select name="fournisseur" class="form-select">
                                        <option value="">Sélectionner</option>
                                        <?php foreach($fournisseurs as $f): ?>
                                            <option value="<?= htmlspecialchars($f['nom']) ?>" <?= ($edit_materiel['fournisseur'] ?? '') == $f['nom'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($f['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Numéro de série</label>
                                    <input type="text" name="numero_serie" class="form-control" 
                                           value="<?= htmlspecialchars($edit_materiel['numero_serie'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="statut" class="form-select">
                                        <option value="actif" <?= ($edit_materiel['statut'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                                        <option value="maintenance" <?= ($edit_materiel['statut'] ?? '') == 'maintenance' ? 'selected' : '' ?>>En maintenance</option>
                                        <option value="hors_service" <?= ($edit_materiel['statut'] ?? '') == 'hors_service' ? 'selected' : '' ?>>Hors service</option>
                                        <option value="stock" <?= ($edit_materiel['statut'] ?? '') == 'stock' ? 'selected' : '' ?>>En stock</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Observations</label>
                            <textarea name="observations" class="form-control" rows="2"><?= htmlspecialchars($edit_materiel['observations'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-save w-100">
                            <i class="fas fa-save"></i> <?= $edit_materiel ? 'Mettre à jour' : 'Enregistrer' ?>
                        </button>
                        
                        <?php if ($edit_materiel): ?>
                            <a href="materiel_form.php" class="btn btn-secondary w-100 mt-2">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Liste du matériel -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Liste du matériel
                    </div>
                    
                    <?php
                    $materiels = $pdo->query("
                        SELECT m.*, c.nom as categorie_nom, s.name as service_nom,
                               sal.numero_salle, sal.etage, b.nom as batiment_nom
                        FROM materiel m
                        LEFT JOIN categories_materiel c ON m.categorie_id = c.id
                        LEFT JOIN services s ON m.service_id = s.id
                        LEFT JOIN salles sal ON m.salle_id = sal.id
                        LEFT JOIN batiments b ON sal.batiment_id = b.id
                        ORDER BY m.id DESC
                    ")->fetchAll();
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Service</th>
                                    <th>Localisation</th>
                                    <th>Valeur</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($materiels as $m): ?>
                                    <tr>
                                        <td><span class="font-monospace"><?= htmlspecialchars($m['code_materiel']) ?></span></td>
                                        <td>
                                            <strong><?= htmlspecialchars($m['nom']) ?></strong>
                                            <?php if($m['numero_serie']): ?>
                                                <br><small>S/N: <?= htmlspecialchars($m['numero_serie']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($m['categorie_nom'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($m['service_nom'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if($m['batiment_nom']): ?>
                                                <?= htmlspecialchars($m['batiment_nom']) ?><br>
                                                <small>Salle <?= $m['numero_salle'] ?> (Étage <?= $m['etage'] ?>)</small>
                                            <?php else: ?>
                                                <span class="text-muted">Non assigné</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-success"><?= number_format($m['valeur_achat'], 0, ',', ' ') ?> F</td>
                                        <td>
                                            <span class="badge bg-<?= $m['statut'] == 'actif' ? 'success' : ($m['statut'] == 'maintenance' ? 'warning' : 'secondary') ?>">
                                                <?= $m['statut'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?= $m['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce matériel ?')">
                                                <input type="hidden" name="action" value="delete_materiel">
                                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
