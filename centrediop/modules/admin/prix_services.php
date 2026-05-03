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
$error = '';

// Récupérer les services
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();

// Récupérer les actes médicaux avec leurs prix
$actes = $pdo->query("
    SELECT a.*, s.name as service_nom 
    FROM actes_medicaux a
    LEFT JOIN services s ON a.service_id = s.id
    ORDER BY a.categorie, a.libelle
")->fetchAll();

// Mise à jour des prix
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_acte'])) {
        try {
            $stmt = $pdo->prepare("
                UPDATE actes_medicaux 
                SET prix_consultation = ?, prix_traitement = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['prix_consultation'],
                $_POST['prix_traitement'],
                $_POST['acte_id']
            ]);
            $message = "Prix mis à jour avec succès";
        } catch (Exception $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['add_acte'])) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO actes_medicaux (code_acte, libelle, categorie, prix_consultation, prix_traitement, service_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['code_acte'],
                $_POST['libelle'],
                $_POST['categorie'],
                $_POST['prix_consultation'],
                $_POST['prix_traitement'],
                $_POST['service_id'] ?: null
            ]);
            $message = "Nouvel acte ajouté avec succès";
        } catch (Exception $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}

// Statistiques des prix
$prix_stats = $pdo->query("
    SELECT 
        AVG(prix_consultation) as moyenne_consultation,
        AVG(prix_traitement) as moyenne_traitement,
        MIN(prix_consultation) as min_consultation,
        MAX(prix_consultation) as max_consultation,
        COUNT(*) as total_actes
    FROM actes_medicaux
")->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des prix - Centre Mamadou Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .price-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .price-value {
            font-size: 2em;
            font-weight: bold;
        }
        .acte-row {
            transition: all 0.3s;
        }
        .acte-row:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Administrateur</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="patients.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="personnel.php"><i class="fas fa-user-md"></i> Personnel</a></li>
                        <li><a href="services.php"><i class="fas fa-building"></i> Services</a></li>
                        <li><a href="prix_services.php" class="active"><i class="fas fa-tag"></i> Prix</a></li>
                        <li><a href="consultations.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="paiements.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <li><a href="../statistiques/index.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-tag"></i> Gestion des prix des consultations</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= $error ?></div>
                <?php endif; ?>
                
                <!-- Statistiques des prix -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="price-card">
                            <div class="price-value"><?= number_format($prix_stats['moyenne_consultation'], 0, ',', ' ') ?> FCFA</div>
                            <div>Moyenne consultation</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="price-card" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <div class="price-value"><?= number_format($prix_stats['moyenne_traitement'], 0, ',', ' ') ?> FCFA</div>
                            <div>Moyenne traitement</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="price-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <div class="price-value"><?= number_format($prix_stats['min_consultation'], 0, ',', ' ') ?> FCFA</div>
                            <div>Prix minimum</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="price-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <div class="price-value"><?= number_format($prix_stats['max_consultation'], 0, ',', ' ') ?> FCFA</div>
                            <div>Prix maximum</div>
                        </div>
                    </div>
                </div>
                
                <!-- Bouton pour ajouter un acte -->
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addActeModal">
                    <i class="fas fa-plus"></i> Ajouter un acte
                </button>
                
                <!-- Liste des actes -->
                <div class="dashboard-card">
                    <ul class="nav nav-tabs" id="priceTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all">Tous</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="consultation-tab" data-bs-toggle="tab" data-bs-target="#consultation">Consultations</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="traitement-tab" data-bs-toggle="tab" data-bs-target="#traitement">Traitements</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="all">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Libellé</th>
                                        <th>Catégorie</th>
                                        <th>Service</th>
                                        <th>Prix consultation</th>
                                        <th>Prix traitement</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($actes as $a): ?>
                                    <tr class="acte-row">
                                        <td><?= $a['code_acte'] ?></td>
                                        <td><?= $a['libelle'] ?></td>
                                        <td><?= $a['categorie'] ?></td>
                                        <td><?= $a['service_nom'] ?? 'Tous services' ?></td>
                                        <td><?= number_format($a['prix_consultation'], 0, ',', ' ') ?> FCFA</td>
                                        <td><?= number_format($a['prix_traitement'], 0, ',', ' ') ?> FCFA</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editActe(<?= $a['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
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
    </div>
    
    <!-- Modal Ajout Acte -->
    <div class="modal fade" id="addActeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Nouvel acte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_acte" value="1">
                        
                        <div class="mb-3">
                            <label>Code acte</label>
                            <input type="text" name="code_acte" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Libellé</label>
                            <input type="text" name="libelle" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Catégorie</label>
                            <select name="categorie" class="form-control" required>
                                <option value="consultation">Consultation</option>
                                <option value="soin">Soin</option>
                                <option value="examen">Examen</option>
                                <option value="vaccination">Vaccination</option>
                                <option value="chirurgie">Chirurgie</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Service associé</label>
                            <select name="service_id" class="form-control">
                                <option value="">Tous services</option>
                                <?php foreach ($services as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label>Prix consultation (FCFA)</label>
                                <input type="number" name="prix_consultation" class="form-control" value="5000" step="500">
                            </div>
                            <div class="col-md-6">
                                <label>Prix traitement (FCFA)</label>
                                <input type="number" name="prix_traitement" class="form-control" value="3000" step="500">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
