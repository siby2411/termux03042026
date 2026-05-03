<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];
$user_service = $_SESSION['user_service'] ?? null;

// Récupérer tous les services pour le filtre
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();

// Récupérer tous les médecins
$medecins = $pdo->query("SELECT id, prenom, nom, specialite FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();

// Variables pour les résultats
$resultats = [];
$recherche_effectuee = false;
$message = '';
$message_type = '';

// Traitement de la recherche
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $recherche_effectuee = true;
    
    $code_patient = $_POST['code_patient'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $service_id = $_POST['service_id'] ?? '';
    $medecin_id = $_POST['medecin_id'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $statut_rdv = $_POST['statut_rdv'] ?? '';
    $type_recherche = $_POST['type_recherche'] ?? 'patients';
    
    try {
        if ($type_recherche == 'patients') {
            // RECHERCHE DE PATIENTS
            $query = "SELECT p.*, 
                             (SELECT COUNT(*) FROM rendez_vous WHERE patient_id = p.id AND date_rdv >= CURDATE()) as rdv_a_venir,
                             (SELECT COUNT(*) FROM consultations WHERE patient_id = p.id) as total_consultations
                      FROM patients p
                      WHERE 1=1";
            $params = [];
            
            if (!empty($code_patient)) {
                $query .= " AND (p.code_patient_unique LIKE :code OR p.numero_patient LIKE :code)";
                $params[':code'] = '%' . $code_patient . '%';
            }
            
            if (!empty($nom)) {
                $query .= " AND p.nom LIKE :nom";
                $params[':nom'] = '%' . $nom . '%';
            }
            
            if (!empty($prenom)) {
                $query .= " AND p.prenom LIKE :prenom";
                $params[':prenom'] = '%' . $prenom . '%';
            }
            
            if (!empty($telephone)) {
                $query .= " AND p.telephone LIKE :telephone";
                $params[':telephone'] = '%' . $telephone . '%';
            }
            
            $query .= " ORDER BY p.nom, p.prenom LIMIT 100";
            
        } elseif ($type_recherche == 'rendezvous') {
            // RECHERCHE DE RENDEZ-VOUS
            $query = "SELECT rv.*, 
                             p.nom as patient_nom, p.prenom as patient_prenom, 
                             p.code_patient_unique,
                             s.name as service_nom,
                             CONCAT(u.prenom, ' ', u.nom) as medecin_nom
                      FROM rendez_vous rv
                      JOIN patients p ON rv.patient_id = p.id
                      JOIN services s ON rv.service_id = s.id
                      LEFT JOIN users u ON rv.medecin_id = u.id
                      WHERE 1=1";
            $params = [];
            
            if (!empty($code_patient)) {
                $query .= " AND p.code_patient_unique LIKE :code";
                $params[':code'] = '%' . $code_patient . '%';
            }
            
            if (!empty($nom)) {
                $query .= " AND p.nom LIKE :nom";
                $params[':nom'] = '%' . $nom . '%';
            }
            
            if (!empty($prenom)) {
                $query .= " AND p.prenom LIKE :prenom";
                $params[':prenom'] = '%' . $prenom . '%';
            }
            
            if (!empty($service_id)) {
                $query .= " AND rv.service_id = :service_id";
                $params[':service_id'] = $service_id;
            }
            
            if (!empty($medecin_id)) {
                $query .= " AND rv.medecin_id = :medecin_id";
                $params[':medecin_id'] = $medecin_id;
            }
            
            if (!empty($date_debut)) {
                $query .= " AND rv.date_rdv >= :date_debut";
                $params[':date_debut'] = $date_debut;
            }
            
            if (!empty($date_fin)) {
                $query .= " AND rv.date_rdv <= :date_fin";
                $params[':date_fin'] = $date_fin;
            }
            
            if (!empty($statut_rdv)) {
                $query .= " AND rv.statut = :statut";
                $params[':statut'] = $statut_rdv;
            }
            
            $query .= " ORDER BY rv.date_rdv DESC, rv.heure_rdv ASC LIMIT 100";
            
        } elseif ($type_recherche == 'consultations') {
            // RECHERCHE DE CONSULTATIONS
            $query = "SELECT c.*, 
                             p.nom as patient_nom, p.prenom as patient_prenom, 
                             p.code_patient_unique,
                             s.name as service_nom,
                             CONCAT(u.prenom, ' ', u.nom) as medecin_nom
                      FROM consultations c
                      JOIN patients p ON c.patient_id = p.id
                      JOIN services s ON c.service_id = s.id
                      LEFT JOIN users u ON c.medecin_id = u.id
                      WHERE 1=1";
            $params = [];
            
            if (!empty($code_patient)) {
                $query .= " AND p.code_patient_unique LIKE :code";
                $params[':code'] = '%' . $code_patient . '%';
            }
            
            if (!empty($nom)) {
                $query .= " AND p.nom LIKE :nom";
                $params[':nom'] = '%' . $nom . '%';
            }
            
            if (!empty($prenom)) {
                $query .= " AND p.prenom LIKE :prenom";
                $params[':prenom'] = '%' . $prenom . '%';
            }
            
            if (!empty($service_id)) {
                $query .= " AND c.service_id = :service_id";
                $params[':service_id'] = $service_id;
            }
            
            if (!empty($medecin_id)) {
                $query .= " AND c.medecin_id = :medecin_id";
                $params[':medecin_id'] = $medecin_id;
            }
            
            $query .= " ORDER BY c.date_consultation DESC LIMIT 100";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $resultats = $stmt->fetchAll();
        
        $message = count($resultats) . " résultat(s) trouvé(s)";
        $message_type = "info";
        
    } catch (Exception $e) {
        $message = "Erreur: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Médicale Multi-Critères</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px; color: white; }
        .navbar a { color: white; text-decoration: none; }
        .container-fluid { padding: 20px; }
        .search-card {
            background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 25px; margin-bottom: 20px;
        }
        .search-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;
            margin: -25px -25px 20px -25px; padding: 15px 25px; border-radius: 15px 15px 0 0;
        }
        .filter-group { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .btn-recherche {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;
            border: none; padding: 12px 40px; border-radius: 8px; font-weight: 600;
        }
        .result-card {
            background: white; border-left: 4px solid #1e3c72; border-radius: 8px;
            padding: 15px; margin-bottom: 10px; transition: all 0.3s;
        }
        .result-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .badge-role {
            background: #1e3c72; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div>
                    <h3><i class="fas fa-search"></i> Recherche Médicale Multi-Critères</h3>
                    <small><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?> (<?= ucfirst($user_role) ?>)</small>
                </div>
                <div>
                    <a href="javascript:history.back()" class="btn btn-sm btn-light me-2">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <a href="../../modules/auth/logout.php" class="btn btn-sm btn-light">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulaire de recherche -->
        <div class="search-card">
            <div class="search-header">
                <i class="fas fa-filter"></i> Filtres de recherche
            </div>
            
            <form method="POST" action="">
                <div class="filter-group">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Type de recherche</label>
                            <select name="type_recherche" class="form-select" id="typeRecherche">
                                <option value="patients">Patients</option>
                                <option value="rendezvous">Rendez-vous</option>
                                <option value="consultations">Consultations</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Code patient</label>
                            <input type="text" name="code_patient" class="form-control" placeholder="PEDIA250031">
                        </div>
                        <div class="col-md-3">
                            <label>Nom</label>
                            <input type="text" name="nom" class="form-control" placeholder="Diop">
                        </div>
                        <div class="col-md-3">
                            <label>Prénom</label>
                            <input type="text" name="prenom" class="form-control" placeholder="Mamadou">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Téléphone</label>
                            <input type="text" name="telephone" class="form-control" placeholder="77 123 45 67">
                        </div>
                        <div class="col-md-3">
                            <label>Service</label>
                            <select name="service_id" class="form-select">
                                <option value="">Tous</option>
                                <?php foreach($services as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Médecin</label>
                            <select name="medecin_id" class="form-select">
                                <option value="">Tous</option>
                                <?php foreach($medecins as $m): ?>
                                    <option value="<?= $m['id'] ?>">Dr. <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Date début</label>
                            <input type="date" name="date_debut" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Date fin</label>
                            <input type="date" name="date_fin" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="submit" name="search" class="btn-recherche">
                        <i class="fas fa-search"></i> Lancer la recherche
                    </button>
                </div>
            </form>
        </div>

        <!-- Résultats -->
        <?php if ($recherche_effectuee && !empty($resultats)): ?>
            <div class="search-card">
                <div class="search-header">
                    <i class="fas fa-list"></i> Résultats (<?= count($resultats) ?>)
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <?php if ($_POST['type_recherche'] == 'patients'): ?>
                                    <th>Code</th>
                                    <th>Patient</th>
                                    <th>Téléphone</th>
                                    <th>RDV à venir</th>
                                    <th>Consultations</th>
                                <?php elseif ($_POST['type_recherche'] == 'rendezvous'): ?>
                                    <th>Date/Heure</th>
                                    <th>Patient</th>
                                    <th>Code</th>
                                    <th>Service</th>
                                    <th>Médecin</th>
                                    <th>Statut</th>
                                <?php else: ?>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Code</th>
                                    <th>Service</th>
                                    <th>Médecin</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($resultats as $r): ?>
                                <tr>
                                    <?php if ($_POST['type_recherche'] == 'patients'): ?>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($r['code_patient_unique']) ?></span></td>
                                        <td><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></td>
                                        <td><?= htmlspecialchars($r['telephone'] ?? 'N/A') ?></td>
                                        <td><?= $r['rdv_a_venir'] ?? 0 ?></td>
                                        <td><?= $r['total_consultations'] ?? 0 ?></td>
                                    <?php elseif ($_POST['type_recherche'] == 'rendezvous'): ?>
                                        <td><?= date('d/m/Y', strtotime($r['date_rdv'])) ?><br><small><?= substr($r['heure_rdv'], 0, 5) ?></small></td>
                                        <td><?= htmlspecialchars($r['patient_prenom'] . ' ' . $r['patient_nom']) ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($r['code_patient_unique']) ?></span></td>
                                        <td><?= htmlspecialchars($r['service_nom']) ?></td>
                                        <td>Dr. <?= htmlspecialchars($r['medecin_nom'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $r['statut'] == 'honore' ? 'success' : 'warning' ?>">
                                                <?= $r['statut'] ?>
                                            </span>
                                        </td>
                                    <?php else: ?>
                                        <td><?= date('d/m/Y', strtotime($r['date_consultation'])) ?></td>
                                        <td><?= htmlspecialchars($r['patient_prenom'] . ' ' . $r['patient_nom']) ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($r['code_patient_unique']) ?></span></td>
                                        <td><?= htmlspecialchars($r['service_nom']) ?></td>
                                        <td>Dr. <?= htmlspecialchars($r['medecin_nom'] ?? 'N/A') ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
