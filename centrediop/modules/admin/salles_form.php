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
$batiments = $pdo->query("SELECT id, nom FROM batiments ORDER BY nom")->fetchAll();
$niveaux = $pdo->query("SELECT n.*, b.nom as batiment_nom FROM niveaux n JOIN batiments b ON n.batiment_id = b.id ORDER BY b.nom, n.nom")->fetchAll();
$zones_attente = $pdo->query("SELECT * FROM zones_attente ORDER BY nom")->fetchAll();

$message = '';
$message_type = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_salle') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO salles (
                    service_id, batiment_id, niveau_id, zone_attente_id,
                    numero_salle, etage, capacite, statut, code_couleur,
                    instructions_acces, created_at
                ) VALUES (
                    :service_id, :batiment_id, :niveau_id, :zone_attente_id,
                    :numero_salle, :etage, :capacite, :statut, :code_couleur,
                    :instructions_acces, NOW()
                )
            ");
            
            $stmt->execute([
                ':service_id' => $_POST['service_id'],
                ':batiment_id' => $_POST['batiment_id'],
                ':niveau_id' => $_POST['niveau_id'] ?: null,
                ':zone_attente_id' => $_POST['zone_attente_id'] ?: null,
                ':numero_salle' => $_POST['numero_salle'],
                ':etage' => $_POST['etage'] ?? 'RDC',
                ':capacite' => $_POST['capacite'] ?? 10,
                ':statut' => $_POST['statut'] ?? 'ouverte',
                ':code_couleur' => $_POST['code_couleur'] ?? '#3498db',
                ':instructions_acces' => $_POST['instructions_acces'] ?? ''
            ]);
            
            $message = "✅ Salle ajoutée avec succès !";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'edit_salle') {
        try {
            $stmt = $pdo->prepare("
                UPDATE salles SET
                    service_id = :service_id,
                    batiment_id = :batiment_id,
                    niveau_id = :niveau_id,
                    zone_attente_id = :zone_attente_id,
                    numero_salle = :numero_salle,
                    etage = :etage,
                    capacite = :capacite,
                    statut = :statut,
                    code_couleur = :code_couleur,
                    instructions_acces = :instructions_acces
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $_POST['id'],
                ':service_id' => $_POST['service_id'],
                ':batiment_id' => $_POST['batiment_id'],
                ':niveau_id' => $_POST['niveau_id'] ?: null,
                ':zone_attente_id' => $_POST['zone_attente_id'] ?: null,
                ':numero_salle' => $_POST['numero_salle'],
                ':etage' => $_POST['etage'] ?? 'RDC',
                ':capacite' => $_POST['capacite'] ?? 10,
                ':statut' => $_POST['statut'] ?? 'ouverte',
                ':code_couleur' => $_POST['code_couleur'] ?? '#3498db',
                ':instructions_acces' => $_POST['instructions_acces'] ?? ''
            ]);
            
            $message = "✅ Salle modifiée avec succès !";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete_salle') {
        try {
            // Vérifier si la salle contient du matériel
            $check = $pdo->prepare("SELECT COUNT(*) FROM materiel WHERE salle_id = ?");
            $check->execute([$_POST['id']]);
            if ($check->fetchColumn() > 0) {
                throw new Exception("Impossible de supprimer : la salle contient du matériel");
            }
            
            $stmt = $pdo->prepare("DELETE FROM salles WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            $message = "✅ Salle supprimée avec succès !";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Récupérer la salle pour édition
$edit_salle = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM salles WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_salle = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Salles - Admin</title>
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
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <h3><i class="fas fa-door-open"></i> Gestion des Salles</h3>
            <a href="dashboard.php" class="btn btn-sm btn-light">Retour Dashboard</a>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulaire d'ajout/modification -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-<?= $edit_salle ? 'edit' : 'plus' ?>"></i>
                        <?= $edit_salle ? 'Modifier la salle' : 'Ajouter une salle' ?>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="<?= $edit_salle ? 'edit_salle' : 'add_salle' ?>">
                        <?php if ($edit_salle): ?>
                            <input type="hidden" name="id" value="<?= $edit_salle['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Service *</label>
                                    <select name="service_id" class="form-select" required>
                                        <option value="">Sélectionner</option>
                                        <?php foreach($services as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= ($edit_salle['service_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Numéro de salle *</label>
                                    <input type="text" name="numero_salle" class="form-control" required
                                           value="<?= htmlspecialchars($edit_salle['numero_salle'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Bâtiment *</label>
                                    <select name="batiment_id" class="form-select" required>
                                        <option value="">Sélectionner</option>
                                        <?php foreach($batiments as $b): ?>
                                            <option value="<?= $b['id'] ?>" <?= ($edit_salle['batiment_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($b['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Niveau</label>
                                    <select name="niveau_id" class="form-select">
                                        <option value="">Sélectionner</option>
                                        <?php foreach($niveaux as $n): ?>
                                            <option value="<?= $n['id'] ?>" <?= ($edit_salle['niveau_id'] ?? '') == $n['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($n['batiment_nom'] . ' - ' . $n['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Étage</label>
                                    <input type="text" name="etage" class="form-control" 
                                           value="<?= htmlspecialchars($edit_salle['etage'] ?? 'RDC') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Capacité</label>
                                    <input type="number" name="capacite" class="form-control" 
                                           value="<?= htmlspecialchars($edit_salle['capacite'] ?? 10) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Zone d'attente</label>
                                    <select name="zone_attente_id" class="form-select">
                                        <option value="">Aucune</option>
                                        <?php foreach($zones_attente as $z): ?>
                                            <option value="<?= $z['id'] ?>" <?= ($edit_salle['zone_attente_id'] ?? '') == $z['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($z['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Statut</label>
                                    <select name="statut" class="form-select">
                                        <option value="ouverte" <?= ($edit_salle['statut'] ?? '') == 'ouverte' ? 'selected' : '' ?>>Ouverte</option>
                                        <option value="fermee" <?= ($edit_salle['statut'] ?? '') == 'fermee' ? 'selected' : '' ?>>Fermée</option>
                                        <option value="maintenance" <?= ($edit_salle['statut'] ?? '') == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Code couleur</label>
                                    <div class="input-group">
                                        <input type="color" name="code_couleur" class="form-control form-control-color" 
                                               value="<?= htmlspecialchars($edit_salle['code_couleur'] ?? '#3498db') ?>">
                                        <span class="input-group-text"><?= htmlspecialchars($edit_salle['code_couleur'] ?? '#3498db') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label>Instructions d'accès</label>
                            <textarea name="instructions_acces" class="form-control" rows="2"><?= htmlspecialchars($edit_salle['instructions_acces'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save"></i> <?= $edit_salle ? 'Mettre à jour' : 'Enregistrer' ?>
                        </button>
                        
                        <?php if ($edit_salle): ?>
                            <a href="salles_form.php" class="btn btn-secondary w-100 mt-2">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Liste des salles -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Liste des salles
                    </div>
                    
                    <?php
                    $salles = $pdo->query("
                        SELECT s.*, serv.name as service_nom, b.nom as batiment_nom,
                               n.nom as niveau_nom, z.nom as zone_nom
                        FROM salles s
                        JOIN services serv ON s.service_id = serv.id
                        JOIN batiments b ON s.batiment_id = b.id
                        LEFT JOIN niveaux n ON s.niveau_id = n.id
                        LEFT JOIN zones_attente z ON s.zone_attente_id = z.id
                        ORDER BY b.nom, s.etage, s.numero_salle
                    ")->fetchAll();
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>N° Salle</th>
                                    <th>Service</th>
                                    <th>Localisation</th>
                                    <th>Étage</th>
                                    <th>Capacité</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($salles as $s): ?>
                                    <tr>
                                        <td><strong>Salle <?= htmlspecialchars($s['numero_salle']) ?></strong></td>
                                        <td><?= htmlspecialchars($s['service_nom']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($s['batiment_nom']) ?><br>
                                            <small><?= htmlspecialchars($s['niveau_nom'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($s['etage'] ?? 'RDC') ?></td>
                                        <td><?= $s['capacite'] ?> pers.</td>
                                        <td>
                                            <span class="badge bg-<?= $s['statut'] == 'ouverte' ? 'success' : 'secondary' ?>">
                                                <?= $s['statut'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?= $s['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette salle ?')">
                                                <input type="hidden" name="action" value="delete_salle">
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
