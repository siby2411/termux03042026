<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'caissier') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer le caissier connecté
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupérer tous les services
$query_services = "SELECT id, name FROM services ORDER BY name";
$stmt_services = $db->prepare($query_services);
$stmt_services->execute();
$services = $stmt_services->fetchAll();

// Récupérer la file d'attente
$query_queue = "SELECT f.*, p.nom, p.prenom, s.name as service_name,
                       TIMESTAMPDIFF(MINUTE, f.cree_a, NOW()) as attente
                FROM file_attente f
                JOIN patients p ON f.patient_id = p.id
                JOIN services s ON f.service_id = s.id
                WHERE f.statut = 'en_attente'
                ORDER BY FIELD(f.priorite, 'urgence', 'senior', 'normal'), f.cree_a ASC";
$stmt_queue = $db->prepare($query_queue);
$stmt_queue->execute();
$file_attente = $stmt_queue->fetchAll();

// STATISTIQUES PERSONNALISÉES PAR CAISSIER (SESSION)
$stmt_patients_crees = $db->prepare("SELECT COUNT(*) FROM patients WHERE created_by = ?");
$stmt_patients_crees->execute([$_SESSION['user_id']]);
$patients_crees = $stmt_patients_crees->fetchColumn();

$stmt_paiements_ajd = $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = CURDATE()");
$stmt_paiements_ajd->execute([$_SESSION['user_id']]);
$paiements_aujourdhui = $stmt_paiements_ajd->fetchColumn();

$stmt_montant_ajd = $db->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = CURDATE()");
$stmt_montant_ajd->execute([$_SESSION['user_id']]);
$montant_aujourdhui = $stmt_montant_ajd->fetchColumn();

$stmt_total_paiements = $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ?");
$stmt_total_paiements->execute([$_SESSION['user_id']]);
$total_paiements = $stmt_total_paiements->fetchColumn();

$stmt_total_montant = $db->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM paiements WHERE caissier_id = ?");
$stmt_total_montant->execute([$_SESSION['user_id']]);
$total_montant = $stmt_total_montant->fetchColumn();

// Derniers paiements du caissier connecté
$derniers_paiements = $db->prepare("
    SELECT p.*, pat.prenom, pat.nom, pat.code_patient_unique
    FROM paiements p
    JOIN patients pat ON p.patient_id = pat.id
    WHERE p.caissier_id = ?
    ORDER BY p.date_paiement DESC
    LIMIT 5
");
$derniers_paiements->execute([$_SESSION['user_id']]);
$paiements_recents = $derniers_paiements->fetchAll();

// Derniers patients créés par le caissier connecté
$derniers_patients = $db->prepare("
    SELECT * FROM patients 
    WHERE created_by = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$derniers_patients->execute([$_SESSION['user_id']]);
$patients_recents = $derniers_patients->fetchAll();

// Statistiques globales
$stats = [
    'file_attente' => count($file_attente),
    'total_patients' => $db->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    'total_materiel' => $db->query("SELECT COUNT(*) FROM materiel")->fetchColumn(),
    'en_maintenance' => $db->query("SELECT COUNT(*) FROM materiel WHERE statut = 'maintenance'")->fetchColumn(),
    'valeur_totale' => $db->query("SELECT COALESCE(SUM(valeur_achat * quantite), 0) FROM materiel")->fetchColumn()
];

// Récupérer les rendez-vous du jour
$date_jour = date('Y-m-d');
$query_rdv_jour = "SELECT rv.*, 
                          p.nom as patient_nom, p.prenom as patient_prenom, 
                          p.code_patient_unique,
                          s.name as service_nom
                   FROM rendez_vous rv
                   JOIN patients p ON rv.patient_id = p.id
                   JOIN services s ON rv.service_id = s.id
                   WHERE rv.date_rdv = :date_jour
                   AND rv.statut IN ('programme', 'confirme')
                   ORDER BY rv.heure_rdv ASC
                   LIMIT 10";
$stmt_rdv_jour = $db->prepare($query_rdv_jour);
$stmt_rdv_jour->execute([':date_jour' => $date_jour]);
$rdv_jour = $stmt_rdv_jour->fetchAll();

// Récupérer les bâtiments pour la localisation rapide
$query_batiments = "SELECT b.*, 
                           COUNT(DISTINCT s.id) as total_salles,
                           GROUP_CONCAT(DISTINCT s.etage) as etages
                    FROM batiments b
                    LEFT JOIN salles s ON b.id = s.batiment_id
                    GROUP BY b.id";
$stmt_batiments = $db->prepare($query_batiments);
$stmt_batiments->execute();
$batiments = $stmt_batiments->fetchAll();

// Traitement de la recherche patient
$patients_trouves = [];
$recherche_patient = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_patient'])) {
    $recherche_patient = true;
    
    $code = $_POST['code_patient'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    
    $query = "SELECT p.*, 
                     COUNT(rv.id) as nb_rdv,
                     GROUP_CONCAT(DISTINCT s.name) as services_rdv
              FROM patients p
              LEFT JOIN rendez_vous rv ON p.id = rv.patient_id AND rv.date_rdv >= CURDATE()
              LEFT JOIN services s ON rv.service_id = s.id
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($code)) {
        $query .= " AND (p.code_patient_unique LIKE :code OR p.numero_patient LIKE :code)";
        $params[':code'] = '%' . $code . '%';
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
    
    $query .= " GROUP BY p.id ORDER BY p.nom, p.prenom LIMIT 20";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $patients_trouves = $stmt->fetchAll();
}

// Matériel récent pour l'aperçu
$materiel_recent = $db->query("
    SELECT m.nom, s.name as service_nom, m.statut 
    FROM materiel m
    JOIN services s ON m.service_id = s.id
    ORDER BY m.id DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Caissier - <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar a { color: white; text-decoration: none; }
        .container-fluid { padding: 20px; }
        
        /* En-tête personnalisé par caissier */
        .caissier-header {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border-left: 4px solid #1e3c72;
        }
        
        /* Cartes de statistiques personnelles */
        .stats-perso {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 4px solid #28a745;
        }
        .stats-card.blue { border-left-color: #1e3c72; }
        .stats-number {
            font-size: 28px;
            font-weight: bold;
            color: #1e3c72;
        }
        
        /* Menu d'actions */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .action-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .action-card:hover {
            transform: translateY(-5px);
            border-color: #1e3c72;
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.15);
        }
        .action-icon {
            font-size: 40px;
            color: #1e3c72;
            margin-bottom: 10px;
        }
        .action-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .action-desc {
            font-size: 12px;
            color: #666;
        }
        
        /* Sections de contenu */
        .content-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .section-title {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1e3c72;
            color: #1e3c72;
        }
        
        /* File d'attente */
        .queue-item {
            background: #f8f9fa;
            border-left: 4px solid #1e3c72;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .queue-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .queue-item.urgence { border-left-color: #dc3545; }
        .queue-item.senior { border-left-color: #ffc107; }
        
        /* Formulaires de recherche */
        .search-form {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .btn-recherche {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }
        .btn-recherche:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
        }
        
        /* Tableaux */
        .table-container {
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
        }
        .table th {
            background: #1e3c72;
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        /* Badges */
        .badge-rdv {
            background: #17a2b8;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
        }
        .badge-salle {
            background: #28a745;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        /* Badge caissier */
        .badge-caissier {
            background: #1e3c72;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <h3><i class="fas fa-cash-register"></i> Dashboard Caissier - <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h3>
                    <small><i class="fas fa-clock"></i> <?= date('d/m/Y H:i') ?></small>
                </div>
                <div>
                    <span class="badge-caissier me-3">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($user['username']) ?>
                    </span>
                    <a href="../auth/logout.php" class="btn btn-sm btn-light">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- STATISTIQUES PERSONNELLES DU CAISSIER -->
        <div class="caissier-header">
            <h5><i class="fas fa-chart-line"></i> Vos statistiques - <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h5>
            <div class="stats-perso">
                <div class="stats-card">
                    <div class="stats-number"><?= $patients_crees ?></div>
                    <div>Patients créés</div>
                </div>
                <div class="stats-card blue">
                    <div class="stats-number"><?= $paiements_aujourdhui ?></div>
                    <div>Paiements aujourd'hui</div>
                </div>
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($montant_aujourdhui, 0, ',', ' ') ?> F</div>
                    <div>Montant du jour</div>
                </div>
                <div class="stats-card">
                    <div class="stats-number"><?= $total_paiements ?></div>
                    <div>Total paiements</div>
                </div>
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($total_montant, 0, ',', ' ') ?> F</div>
                    <div>Total encaissé</div>
                </div>
            </div>
        </div>

        <!-- STATISTIQUES GLOBALES -->
        <div class="stats-grid">
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-users"></i></div>
                <div class="stats-number"><?= $stats['file_attente'] ?></div>
                <div class="stats-label">Patients en file d'attente</div>
            </div>
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stats-number"><?= count($rdv_jour) ?></div>
                <div class="stats-label">Rendez-vous aujourd'hui</div>
            </div>
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-boxes"></i></div>
                <div class="stats-number"><?= $stats['total_materiel'] ?></div>
                <div class="stats-label">Équipements</div>
            </div>
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-coins"></i></div>
                <div class="stats-number"><?= number_format($stats['valeur_totale'], 0, ',', ' ') ?> F</div>
                <div class="stats-label">Valeur du parc</div>
            </div>
        </div>

        <!-- MENU D'ACTIONS RAPIDES -->
        <div class="actions-grid">
            <div class="action-card" onclick="document.getElementById('searchPatient').scrollIntoView({behavior: 'smooth'})">
                <div class="action-icon"><i class="fas fa-search"></i></div>
                <div class="action-title">Rechercher Patient</div>
                <div class="action-desc">Par code, nom, prénom, téléphone</div>
            </div>
            <div class="action-card" onclick="document.getElementById('rdvSection').scrollIntoView({behavior: 'smooth'})">
                <div class="action-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="action-title">Rendez-vous</div>
                <div class="action-desc">Liste et recherche par service</div>
            </div>
            <div class="action-card" onclick="document.getElementById('localisationSection').scrollIntoView({behavior: 'smooth'})">
                <div class="action-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div class="action-title">Localisation</div>
                <div class="action-desc">Bâtiments, étages, salles</div>
            </div>
            <div class="action-card" onclick="window.location.href='gestion_materiel.php'">
                <div class="action-icon"><i class="fas fa-tools"></i></div>
                <div class="action-title">Gestion Matériel</div>
                <div class="action-desc">Inventaire, ajout, maintenance</div>
            </div>
            <div class="action-card" onclick="window.location.href='ajout_patient.php'">
                <div class="action-icon"><i class="fas fa-user-plus"></i></div>
                <div class="action-title">Nouveau Patient</div>
                <div class="action-desc">Enregistrer un patient</div>
            </div>
            <div class="action-card" onclick="window.location.href='paiement_traitement.php'">
                <div class="action-icon"><i class="fas fa-credit-card"></i></div>
                <div class="action-title">Paiement</div>
                <div class="action-desc">Payer un traitement</div>
            </div>
        </div>

        <div class="row">
            <!-- Colonne de gauche : Recherche et résultats -->
            <div class="col-md-7">
                <!-- SECTION RECHERCHE PATIENT MULTI-CRITÈRES -->
                <div id="searchPatient" class="content-section">
                    <div class="section-title">
                        <i class="fas fa-search"></i> Recherche patient multi-critères
                    </div>
                    
                    <div class="search-form">
                        <form method="POST" action="">
                            <div class="search-grid">
                                <input type="text" name="code_patient" class="form-control" placeholder="Code patient">
                                <input type="text" name="nom" class="form-control" placeholder="Nom">
                                <input type="text" name="prenom" class="form-control" placeholder="Prénom">
                                <input type="text" name="telephone" class="form-control" placeholder="Téléphone">
                                <button type="submit" name="search_patient" class="btn-recherche">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php if ($recherche_patient): ?>
                        <h6 class="mb-3">Résultats : <?= count($patients_trouves) ?> patient(s)</h6>
                        
                        <?php if (empty($patients_trouves)): ?>
                            <div class="alert alert-info">Aucun patient trouvé</div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Nom & Prénom</th>
                                            <th>Téléphone</th>
                                            <th>RDV à venir</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($patients_trouves as $p): ?>
                                            <tr>
                                                <td><span class="font-monospace"><?= htmlspecialchars($p['code_patient_unique']) ?></span></td>
                                                <td><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></td>
                                                <td><?= htmlspecialchars($p['telephone'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php if ($p['nb_rdv'] > 0): ?>
                                                        <span class="badge-rdv"><?= $p['nb_rdv'] ?> RDV</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Aucun</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-success" onclick="payerPatient(<?= $p['id'] ?>, '<?= $p['code_patient_unique'] ?>')">
                                                        <i class="fas fa-credit-card"></i> Payer
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- FILE D'ATTENTE -->
                <div class="content-section">
                    <div class="section-title">
                        <i class="fas fa-clock"></i> File d'attente (<?= count($file_attente) ?>)
                    </div>
                    
                    <?php if (empty($file_attente)): ?>
                        <div class="alert alert-info text-center">Aucun patient en attente</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach($file_attente as $item): ?>
                                <div class="queue-item <?= $item['priorite'] ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-primary"><?= $item['token'] ?></span>
                                            <strong class="ms-2"><?= htmlspecialchars($item['prenom'] . ' ' . $item['nom']) ?></strong>
                                            <span class="text-muted ms-2"><?= htmlspecialchars($item['service_name']) ?></span>
                                        </div>
                                        <div>
                                            <small class="text-muted"><?= $item['attente'] ?> min</small>
                                            <span class="badge <?= $item['priorite'] == 'urgence' ? 'bg-danger' : ($item['priorite'] == 'senior' ? 'bg-warning' : 'bg-success') ?> ms-2">
                                                <?= ucfirst($item['priorite']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- VOS DERNIERS PAIEMENTS -->
                <div class="content-section">
                    <div class="section-title">
                        <i class="fas fa-credit-card"></i> Vos derniers paiements
                    </div>
                    
                    <?php if (empty($paiements_recents)): ?>
                        <p class="text-muted">Aucun paiement effectué</p>
                    <?php else: ?>
                        <?php foreach($paiements_recents as $p): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background: #f8f9fa; border-radius: 5px;">
                                <div>
                                    <strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong>
                                    <br><small><?= htmlspecialchars($p['code_patient_unique']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success"><?= number_format($p['montant_total'], 0, ',', ' ') ?> F</span>
                                    <br><small class="text-muted"><?= date('H:i', strtotime($p['date_paiement'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Colonne de droite : Rendez-vous et Localisation -->
            <div class="col-md-5">
                <!-- SECTION RENDEZ-VOUS DU JOUR -->
                <div id="rdvSection" class="content-section">
                    <div class="section-title">
                        <i class="fas fa-calendar-day"></i> Rendez-vous du jour
                    </div>
                    
                    <?php if (empty($rdv_jour)): ?>
                        <div class="alert alert-info">Aucun rendez-vous aujourd'hui</div>
                    <?php else: ?>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <?php foreach($rdv_jour as $rdv): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background: #f8f9fa; border-radius: 5px;">
                                    <div>
                                        <small><?= substr($rdv['heure_rdv'], 0, 5) ?></small>
                                        <strong class="ms-2"><?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($rdv['service_nom']) ?></small>
                                    </div>
                                    <div>
                                        <span class="badge bg-info"><?= htmlspecialchars($rdv['code_patient_unique']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-3 text-end">
                        <a href="recherche_rdv.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search"></i> Recherche avancée
                        </a>
                    </div>
                </div>

                <!-- SECTION LOCALISATION RAPIDE -->
                <div id="localisationSection" class="content-section">
                    <div class="section-title">
                        <i class="fas fa-map-marked-alt"></i> Localisation des services
                    </div>
                    
                    <div class="mb-3">
                        <select id="serviceLocSelect" class="form-select" onchange="localiserService()">
                            <option value="">Sélectionner un service</option>
                            <?php foreach($services as $service): ?>
                                <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="localisationResult" style="min-height: 150px; background: #f8f9fa; border-radius: 8px; padding: 15px;">
                        <p class="text-muted text-center mt-4">Choisissez un service pour voir sa localisation</p>
                    </div>
                    
                    <div class="mt-3 text-end">
                        <a href="recherche_spatiale.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-building"></i> Vue détaillée
                        </a>
                        <a href="gestion_batiments.php" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-plus-circle"></i> Ajouter
                        </a>
                    </div>
                </div>

                <!-- APERÇU DU MATÉRIEL -->
                <div class="content-section">
                    <div class="section-title">
                        <i class="fas fa-tools"></i> Aperçu du matériel
                    </div>
                    
                    <?php foreach($materiel_recent as $m): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background: #f8f9fa; border-radius: 5px;">
                            <div>
                                <strong><?= htmlspecialchars($m['nom']) ?></strong>
                                <br><small><?= htmlspecialchars($m['service_nom']) ?></small>
                            </div>
                            <span class="badge <?= $m['statut'] == 'actif' ? 'bg-success' : 'bg-warning' ?>">
                                <?= $m['statut'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-3 text-end">
                        <a href="gestion_materiel.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-boxes"></i> Voir tout
                        </a>
                    </div>
                </div>

                <!-- VOS DERNIERS PATIENTS CRÉÉS -->
                <div class="content-section">
                    <div class="section-title">
                        <i class="fas fa-user-plus"></i> Vos derniers patients
                    </div>
                    
                    <?php if (empty($patients_recents)): ?>
                        <p class="text-muted">Aucun patient créé</p>
                    <?php else: ?>
                        <?php foreach($patients_recents as $p): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background: #f8f9fa; border-radius: 5px;">
                                <div>
                                    <strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong>
                                    <br><small><?= htmlspecialchars($p['code_patient_unique']) ?></small>
                                </div>
                                <small class="text-muted"><?= date('H:i', strtotime($p['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function localiserService() {
            const serviceId = document.getElementById('serviceLocSelect').value;
            const resultDiv = document.getElementById('localisationResult');
            
            if (!serviceId) {
                resultDiv.innerHTML = '<p class="text-muted text-center mt-4">Choisissez un service pour voir sa localisation</p>';
                return;
            }
            
            fetch('get_localisation.php?service_id=' + serviceId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div class="small">';
                        data.salles.forEach(s => {
                            html += `
                                <div class="mb-2 p-2" style="border-bottom: 1px solid #dee2e6;">
                                    <i class="fas fa-door-open"></i> Salle ${s.numero_salle}<br>
                                    <span class="text-muted ms-3">
                                        <i class="fas fa-building"></i> ${s.batiment_nom} - 
                                        <i class="fas fa-level-up-alt"></i> Étage ${s.etage || 'RDC'}
                                    </span>
                                    <span class="badge bg-success float-end">${s.nb_materiel} équip.</span>
                                </div>
                            `;
                        });
                        html += '</div>';
                        resultDiv.innerHTML = html;
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-warning">Aucune salle trouvée</div>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="alert alert-danger">Erreur de chargement</div>';
                });
        }
        
        function payerPatient(patientId, codePatient) {
            window.location.href = 'paiement_traitement.php?patient_id=' + patientId + '&code=' + codePatient;
        }
    </script>
</body>
</html>
