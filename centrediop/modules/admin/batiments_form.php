<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$message = '';
$message_type = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_batiment') {
        try {
            $stmt = $pdo->prepare("INSERT INTO batiments (nom, adresse, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$_POST['nom'], $_POST['adresse'] ?? '']);
            
            $message = "✅ Bâtiment ajouté avec succès !";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'edit_batiment') {
        try {
            $stmt = $pdo->prepare("UPDATE batiments SET nom = ?, adresse = ? WHERE id = ?");
            $stmt->execute([$_POST['nom'], $_POST['adresse'] ?? '', $_POST['id']]);
            
            $message = "✅ Bâtiment modifié avec succès !";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete_batiment') {
        try {
            // Vérifier si le bâtiment contient des salles
            $check = $pdo->prepare("SELECT COUNT(*) FROM salles WHERE batiment_id = ?");
            $check->execute([$_POST['id']]);
            if ($check->fetchColumn() > 0) {
                throw new Exception("Impossible de supprimer : le bâtiment contient des salles");
            }
            
            $stmt = $pdo->prepare("DELETE FROM batiments WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            $message = "✅ Bâtiment supprimé avec succès !";
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "❌ Erreur: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Récupérer la liste des bâtiments
$batiments = $pdo->query("
    SELECT b.*, 
           (SELECT COUNT(*) FROM salles WHERE batiment_id = b.id) as nb_salles,
           (SELECT COUNT(DISTINCT etage) FROM salles WHERE batiment_id = b.id) as nb_etages
    FROM batiments b
    ORDER BY b.nom
")->fetchAll();

// Récupérer un bâtiment pour édition
$edit_batiment = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM batiments WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_batiment = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Bâtiments - Admin</title>
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
        .batiment-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .batiment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <h3><i class="fas fa-building"></i> Gestion des Bâtiments</h3>
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
                        <i class="fas fa-<?= $edit_batiment ? 'edit' : 'plus' ?>"></i>
                        <?= $edit_batiment ? 'Modifier le bâtiment' : 'Ajouter un bâtiment' ?>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="<?= $edit_batiment ? 'edit_batiment' : 'add_batiment' ?>">
                        <?php if ($edit_batiment): ?>
                            <input type="hidden" name="id" value="<?= $edit_batiment['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label>Nom du bâtiment *</label>
                            <input type="text" name="nom" class="form-control" required
                                   value="<?= htmlspecialchars($edit_batiment['nom'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label>Adresse</label>
                            <textarea name="adresse" class="form-control" rows="2"><?= htmlspecialchars($edit_batiment['adresse'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save"></i> <?= $edit_batiment ? 'Mettre à jour' : 'Enregistrer' ?>
                        </button>
                        
                        <?php if ($edit_batiment): ?>
                            <a href="batiments_form.php" class="btn btn-secondary w-100 mt-2">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Liste des bâtiments -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Liste des bâtiments
                    </div>
                    
                    <?php foreach($batiments as $b): ?>
                        <div class="batiment-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5><i class="fas fa-building"></i> <?= htmlspecialchars($b['nom']) ?></h5>
                                    <?php if($b['adresse']): ?>
                                        <p class="text-muted mb-2"><?= htmlspecialchars($b['adresse']) ?></p>
                                    <?php endif; ?>
                                    <div>
                                        <span class="badge bg-primary me-2"><?= $b['nb_salles'] ?> salles</span>
                                        <span class="badge bg-info"><?= $b['nb_etages'] ?> étages</span>
                                    </div>
                                </div>
                                <div>
                                    <a href="?edit=<?= $b['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce bâtiment ?')">
                                        <input type="hidden" name="action" value="delete_batiment">
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
