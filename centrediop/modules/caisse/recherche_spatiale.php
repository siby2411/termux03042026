<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'caissier') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer tous les services
$query_services = "SELECT id, name FROM services ORDER BY name";
$stmt_services = $db->prepare($query_services);
$stmt_services->execute();
$services = $stmt_services->fetchAll();

// Récupérer tous les bâtiments
$query_batiments = "SELECT b.*, 
                           (SELECT COUNT(*) FROM salles WHERE batiment_id = b.id) as total_salles
                    FROM batiments b
                    ORDER BY b.nom";
$stmt_batiments = $db->prepare($query_batiments);
$stmt_batiments->execute();
$batiments = $stmt_batiments->fetchAll();

// Récupérer les catégories de matériel
$query_categories = "SELECT * FROM categories_materiel ORDER BY nom";
$stmt_categories = $db->prepare($query_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll();

// Récupérer les fournisseurs
$query_fournisseurs = "SELECT * FROM fournisseurs ORDER BY nom";
$stmt_fournisseurs = $db->prepare($query_fournisseurs);
$stmt_fournisseurs->execute();
$fournisseurs = $stmt_fournisseurs->fetchAll();

// Statistiques globales
$stats = [
    'total_materiel' => $db->query("SELECT COUNT(*) FROM materiel")->fetchColumn(),
    'valeur_totale' => $db->query("SELECT SUM(valeur_achat * quantite) FROM materiel")->fetchColumn(),
    'en_maintenance' => $db->query("SELECT COUNT(*) FROM materiel WHERE statut = 'maintenance'")->fetchColumn(),
    'actif' => $db->query("SELECT COUNT(*) FROM materiel WHERE statut = 'actif'")->fetchColumn()
];

// Recherche spatiale
$resultats_spatiaux = [];
$recherche_effectuee = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_spatial'])) {
    $recherche_effectuee = true;
    $service_id = $_POST['service_id'] ?? '';
    $batiment_id = $_POST['batiment_id'] ?? '';
    $etage = $_POST['etage'] ?? '';
    
    $query = "SELECT s.*, 
                     serv.name as service_nom,
                     b.nom as batiment_nom,
                     n.nom as niveau_nom,
                     (SELECT COUNT(*) FROM materiel WHERE salle_id = s.id) as nb_materiel,
                     (SELECT SUM(valeur_achat * quantite) FROM materiel WHERE salle_id = s.id) as valeur_materiel,
                     (SELECT GROUP_CONCAT(CONCAT(m.nom, ' (', m.quantite, ')') SEPARATOR '|') 
                      FROM materiel m WHERE m.salle_id = s.id) as liste_materiel
              FROM salles s
              JOIN services serv ON s.service_id = serv.id
              JOIN batiments b ON s.batiment_id = b.id
              LEFT JOIN niveaux n ON s.niveau_id = n.id
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($service_id)) {
        $query .= " AND s.service_id = :service_id";
        $params[':service_id'] = $service_id;
    }
    
    if (!empty($batiment_id)) {
        $query .= " AND s.batiment_id = :batiment_id";
        $params[':batiment_id'] = $batiment_id;
    }
    
    if (!empty($etage)) {
        $query .= " AND s.etage = :etage";
        $params[':etage'] = $etage;
    }
    
    $query .= " ORDER BY b.nom, s.etage, s.numero_salle";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $resultats_spatiaux = $stmt->fetchAll();
}

// Recherche d'inventaire
$inventaire = [];
$recherche_inventaire = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_inventaire'])) {
    $recherche_inventaire = true;
    
    $service_id = $_POST['inv_service_id'] ?? '';
    $categorie_id = $_POST['categorie_id'] ?? '';
    $statut = $_POST['statut'] ?? '';
    $fournisseur_id = $_POST['fournisseur_id'] ?? '';
    $recherche = $_POST['recherche'] ?? '';
    
    $query = "SELECT m.*, 
                     c.nom as categorie_nom,
                     s.name as service_nom,
                     sal.numero_salle,
                     sal.etage,
                     b.nom as batiment_nom,
                     f.nom as fournisseur_nom,
                     f.telephone as fournisseur_tel
              FROM materiel m
              LEFT JOIN categories_materiel c ON m.categorie_id = c.id
              LEFT JOIN services s ON m.service_id = s.id
              LEFT JOIN salles sal ON m.salle_id = sal.id
              LEFT JOIN batiments b ON sal.batiment_id = b.id
              LEFT JOIN fournisseurs f ON m.fournisseur = f.nom
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($service_id)) {
        $query .= " AND m.service_id = :service_id";
        $params[':service_id'] = $service_id;
    }
    
    if (!empty($categorie_id)) {
        $query .= " AND m.categorie_id = :categorie_id";
        $params[':categorie_id'] = $categorie_id;
    }
    
    if (!empty($statut)) {
        $query .= " AND m.statut = :statut";
        $params[':statut'] = $statut;
    }
    
    if (!empty($fournisseur_id)) {
        $query .= " AND m.fournisseur = (SELECT nom FROM fournisseurs WHERE id = :fournisseur_id)";
        $params[':fournisseur_id'] = $fournisseur_id;
    }
    
    if (!empty($recherche)) {
        $query .= " AND (m.nom LIKE :recherche OR m.code_materiel LIKE :recherche OR m.numero_serie LIKE :recherche)";
        $params[':recherche'] = '%' . $recherche . '%';
    }
    
    $query .= " ORDER BY m.service_id, m.nom";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $inventaire = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Centre - Spatial & Inventaire</title>
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
        .navbar a { color: white; text-decoration: none; }
        .container-fluid { padding: 20px; }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .stats-card.primary { border-left-color: #1e3c72; }
        .stats-card.warning { border-left-color: #ffc107; }
        
        .stats-number {
            font-size: 28px;
            font-weight: bold;
            color: #1e3c72;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 25px;
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            margin: -25px -25px 20px -25px;
            padding: 15px 25px;
            border-radius: 15px 15px 0 0;
            font-weight: 600;
        }
        
        .search-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .btn-recherche {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-recherche:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
        }
        
        .salle-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
            background: white;
        }
        .salle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #1e3c72;
        }
        
        .badge-salle {
            background: #1e3c72;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .materiel-item {
            border-left: 4px solid #28a745;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .materiel-item.maintenance { border-left-color: #ffc107; }
        .materiel-item.hors_service { border-left-color: #dc3545; }
        
        .statut-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        .statut-actif { background: #28a745; color: white; }
        .statut-maintenance { background: #ffc107; color: #333; }
        .statut-hors_service { background: #dc3545; color: white; }
        
        .fournisseur-info {
            font-size: 12px;
            color: #666;
        }
        
        .prix-achat {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
        }
        
        .nav-tabs .nav-link {
            color: #1e3c72;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3><i class="fas fa-hospital"></i> Gestion du Centre - Spatial & Inventaire</h3>
                <div>
                    <a href="index.php" class="btn btn-sm btn-light me-2">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                    <a href="../auth/logout.php" class="btn btn-sm btn-light">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Statistiques globales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card primary">
                    <div class="stats-number"><?= number_format($stats['total_materiel'], 0, ',', ' ') ?></div>
                    <div>Équipements totaux</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($stats['valeur_totale'], 0, ',', ' ') ?> FCFA</div>
                    <div>Valeur du parc</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning">
                    <div class="stats-number"><?= $stats['en_maintenance'] ?></div>
                    <div>En maintenance</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['actif'] ?></div>
                    <div>Équipements actifs</div>
                </div>
            </div>
        </div>

        <!-- Navigation par onglets -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="spatial-tab" data-bs-toggle="tab" data-bs-target="#spatial" type="button" role="tab">
                    <i class="fas fa-map-marker-alt"></i> Localisation des services
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventaire-tab" data-bs-toggle="tab" data-bs-target="#inventaire" type="button" role="tab">
                    <i class="fas fa-boxes"></i> Inventaire détaillé
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="fournisseurs-tab" data-bs-toggle="tab" data-bs-target="#fournisseurs" type="button" role="tab">
                    <i class="fas fa-truck"></i> Fournisseurs
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Onglet Localisation -->
            <div class="tab-pane fade show active" id="spatial" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-search-location"></i> Localiser un service
                    </div>
                    
                    <div class="search-section">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Service</label>
                                    <select name="service_id" class="form-select">
                                        <option value="">Tous les services</option>
                                        <?php foreach($services as $service): ?>
                                            <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Bâtiment</label>
                                    <select name="batiment_id" class="form-select">
                                        <option value="">Tous les bâtiments</option>
                                        <?php foreach($batiments as $batiment): ?>
                                            <option value="<?= $batiment['id'] ?>"><?= htmlspecialchars($batiment['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Étage</label>
                                    <select name="etage" class="form-select">
                                        <option value="">Tous</option>
                                        <option value="RDC">Rez-de-chaussée</option>
                                        <option value="1er">1er étage</option>
                                        <option value="2ème">2ème étage</option>
                                        <option value="3ème">3ème étage</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" name="search_spatial" class="btn-recherche w-100">
                                        <i class="fas fa-search"></i> Localiser
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php if ($recherche_effectuee): ?>
                        <h5 class="mb-3">
                            <i class="fas fa-building"></i> Salles trouvées : <?= count($resultats_spatiaux) ?>
                        </h5>
                        
                        <?php if (empty($resultats_spatiaux)): ?>
                            <div class="alert alert-info">Aucune salle trouvée</div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach($resultats_spatiaux as $salle): ?>
                                    <div class="col-md-6">
                                        <div class="salle-card" onclick="afficherDetailsSalle(<?= htmlspecialchars(json_encode($salle)) ?>)">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <span class="badge-salle">Salle <?= $salle['numero_salle'] ?></span>
                                                    <h6 class="mt-2"><?= htmlspecialchars($salle['service_nom']) ?></h6>
                                                    <p class="mb-1">
                                                        <i class="fas fa-building"></i> <?= htmlspecialchars($salle['batiment_nom'] ?? 'Principal') ?><br>
                                                        <i class="fas fa-level-up-alt"></i> Étage: <?= htmlspecialchars($salle['etage'] ?? 'RDC') ?><br>
                                                        <i class="fas fa-boxes"></i> <?= $salle['nb_materiel'] ?> équipements
                                                        <br><small class="text-success">Valeur: <?= number_format($salle['valeur_materiel'] ?? 0, 0, ',', ' ') ?> FCFA</small>
                                                    </p>
                                                </div>
                                                <div>
                                                    <span class="badge bg-info"><?= $salle['statut'] ?? 'Ouverte' ?></span>
                                                </div>
                                            </div>
                                            <?php if (!empty($salle['liste_materiel'])): ?>
                                                <div class="mt-2 small">
                                                    <strong>Équipements:</strong><br>
                                                    <?php 
                                                    $materiels = explode('|', $salle['liste_materiel']);
                                                    foreach(array_slice($materiels, 0, 3) as $m): ?>
                                                        <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($m) ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if(count($materiels) > 3): ?>
                                                        <span class="badge bg-dark">+<?= count($materiels)-3 ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Onglet Inventaire détaillé -->
            <div class="tab-pane fade" id="inventaire" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-boxes"></i> Inventaire du matériel
                    </div>
                    
                    <div class="search-section">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Service</label>
                                    <select name="inv_service_id" class="form-select">
                                        <option value="">Tous</option>
                                        <?php foreach($services as $service): ?>
                                            <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Catégorie</label>
                                    <select name="categorie_id" class="form-select">
                                        <option value="">Toutes</option>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Statut</label>
                                    <select name="statut" class="form-select">
                                        <option value="">Tous</option>
                                        <option value="actif">Actif</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="hors_service">Hors service</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Recherche</label>
                                    <input type="text" name="recherche" class="form-control" placeholder="Nom, code, série...">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12 text-end">
                                    <button type="submit" name="search_inventaire" class="btn-recherche">
                                        <i class="fas fa-search"></i> Rechercher
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php if ($recherche_inventaire): ?>
                        <h5 class="mb-3">
                            <i class="fas fa-cube"></i> Équipements trouvés : <?= count($inventaire) ?>
                        </h5>
                        
                        <?php if (empty($inventaire)): ?>
                            <div class="alert alert-info">Aucun équipement trouvé</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Équipement</th>
                                            <th>Catégorie</th>
                                            <th>Service</th>
                                            <th>Localisation</th>
                                            <th>Prix achat</th>
                                            <th>Fournisseur</th>
                                            <th>Statut</th>
                                            <th>Qté</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($inventaire as $item): ?>
                                            <tr>
                                                <td><span class="font-monospace"><?= htmlspecialchars($item['code_materiel']) ?></span></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($item['nom']) ?></strong>
                                                    <?php if(!empty($item['numero_serie'])): ?>
                                                        <br><small>S/N: <?= htmlspecialchars($item['numero_serie']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($item['categorie_nom'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($item['service_nom'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php if(!empty($item['batiment_nom'])): ?>
                                                        <?= htmlspecialchars($item['batiment_nom']) ?><br>
                                                        <small>Salle <?= $item['numero_salle'] ?? '' ?> (Étage <?= $item['etage'] ?? 'RDC' ?>)</small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non assigné</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="prix-achat"><?= number_format($item['valeur_achat'], 0, ',', ' ') ?> FCFA</td>
                                                <td>
                                                    <?= htmlspecialchars($item['fournisseur_nom'] ?? 'N/A') ?>
                                                    <?php if(!empty($item['fournisseur_tel'])): ?>
                                                        <br><small><?= $item['fournisseur_tel'] ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="statut-badge statut-<?= $item['statut'] ?>">
                                                        <?= $item['statut'] == 'actif' ? 'Actif' : ($item['statut'] == 'maintenance' ? 'Maintenance' : 'Hors service') ?>
                                                    </span>
                                                </td>
                                                <td class="text-center"><?= $item['quantite'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Onglet Fournisseurs -->
            <div class="tab-pane fade" id="fournisseurs" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-truck"></i> Liste des fournisseurs
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Contact</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Adresse</th>
                                    <th>Spécialité</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($fournisseurs as $f): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($f['nom']) ?></strong></td>
                                        <td><?= htmlspecialchars($f['contact'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($f['telephone']) ?></td>
                                        <td><?= htmlspecialchars($f['email']) ?></td>
                                        <td><?= htmlspecialchars($f['adresse']) ?></td>
                                        <td><small><?= htmlspecialchars($f['specialite']) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal détails salle -->
    <div class="modal fade" id="salleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Détails de la salle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="salleModalBody">
                    <!-- Rempli par JS -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function afficherDetailsSalle(salle) {
            let materiels = salle.liste_materiel ? salle.liste_materiel.split('|') : [];
            let html = `
                <div class="text-center mb-3">
                    <h4>Salle ${salle.numero_salle} - ${salle.service_nom}</h4>
                </div>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">Service</th>
                        <td>${salle.service_nom}</td>
                    </tr>
                    <tr>
                        <th>Bâtiment</th>
                        <td>${salle.batiment_nom || 'Principal'}</td>
                    </tr>
                    <tr>
                        <th>Étage</th>
                        <td>${salle.etage || 'RDC'}</td>
                    </tr>
                    <tr>
                        <th>Numéro salle</th>
                        <td>${salle.numero_salle}</td>
                    </tr>
                    <tr>
                        <th>Capacité</th>
                        <td>${salle.capacite || 'N/A'} personnes</td>
                    </tr>
                    <tr>
                        <th>Statut</th>
                        <td><span class="badge bg-success">${salle.statut || 'Ouverte'}</span></td>
                    </tr>
                    <tr>
                        <th>Nombre d'équipements</th>
                        <td>${salle.nb_materiel || 0}</td>
                    </tr>
                    <tr>
                        <th>Valeur du matériel</th>
                        <td class="text-success fw-bold">${formatMoney(salle.valeur_materiel || 0)} FCFA</td>
                    </tr>
                </table>
            `;
            
            if (materiels.length > 0) {
                html += '<h6 class="mt-3">Équipements dans cette salle:</h6><div class="row">';
                materiels.forEach(m => {
                    html += `<div class="col-md-6 mb-2">
                        <div class="materiel-item p-2">
                            <i class="fas fa-cube"></i> ${m}
                        </div>
                    </div>`;
                });
                html += '</div>';
            } else {
                html += '<div class="alert alert-info mt-3">Aucun équipement dans cette salle</div>';
            }
            
            document.getElementById('salleModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('salleModal')).show();
        }
        
        function formatMoney(montant) {
            return new Intl.NumberFormat('fr-FR').format(montant);
        }
    </script>
</body>
</html>
