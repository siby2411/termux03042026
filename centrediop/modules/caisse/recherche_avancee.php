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

// Récupérer les services pour le filtre
$query_services = "SELECT id, name FROM services ORDER BY name";
$stmt_services = $db->prepare($query_services);
$stmt_services->execute();
$services = $stmt_services->fetchAll();

// Récupérer les médecins
$query_medecins = "SELECT id, prenom, nom, specialite FROM users WHERE role = 'medecin' ORDER BY nom";
$stmt_medecins = $db->prepare($query_medecins);
$stmt_medecins->execute();
$medecins = $stmt_medecins->fetchAll();

// Récupérer les actes médicaux (traitements)
$query_actes = "SELECT a.*, s.name as service_nom 
                FROM actes_medicaux a
                JOIN services s ON a.service_id = s.id
                WHERE a.prix_traitement > 0
                ORDER BY a.libelle";
$stmt_actes = $db->prepare($query_actes);
$stmt_actes->execute();
$actes = $stmt_actes->fetchAll();

// Traitement de la recherche
$search_results = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $search_performed = true;
    
    $date_rdv = $_POST['date_rdv'] ?? '';
    $service_id = $_POST['service_id'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $code_patient = $_POST['code_patient'] ?? '';
    $medecin_id = $_POST['medecin_id'] ?? '';
    $statut = $_POST['statut'] ?? 'programme';
    
    $query = "SELECT rv.*, 
                     p.id as patient_id, p.nom as patient_nom, p.prenom as patient_prenom, 
                     p.telephone as patient_telephone, p.code_patient_unique,
                     s.name as service_nom,
                     u.nom as medecin_nom, u.prenom as medecin_prenom,
                     a.libelle as traitement_libelle, a.prix_traitement,
                     CASE 
                         WHEN rv.statut = 'programme' THEN 'À venir'
                         WHEN rv.statut = 'confirme' THEN 'Confirmé'
                         WHEN rv.statut = 'honore' THEN 'Honoré'
                         WHEN rv.statut = 'annule' THEN 'Annulé'
                         ELSE rv.statut
                     END as statut_label
              FROM rendez_vous rv
              JOIN patients p ON rv.patient_id = p.id
              JOIN services s ON rv.service_id = s.id
              LEFT JOIN users u ON rv.medecin_id = u.id
              LEFT JOIN actes_medicaux a ON rv.id = a.id
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($date_rdv)) {
        $query .= " AND DATE(rv.date_rdv) = :date_rdv";
        $params[':date_rdv'] = $date_rdv;
    }
    
    if (!empty($service_id)) {
        $query .= " AND rv.service_id = :service_id";
        $params[':service_id'] = $service_id;
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
    
    if (!empty($code_patient)) {
        $query .= " AND (p.code_patient_unique LIKE :code OR p.numero_patient LIKE :code)";
        $params[':code'] = '%' . $code_patient . '%';
    }
    
    if (!empty($medecin_id)) {
        $query .= " AND rv.medecin_id = :medecin_id";
        $params[':medecin_id'] = $medecin_id;
    }
    
    if (!empty($statut)) {
        $query .= " AND rv.statut = :statut";
        $params[':statut'] = $statut;
    }
    
    $query .= " ORDER BY rv.date_rdv DESC, rv.heure_rdv ASC LIMIT 100";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $search_results = $stmt->fetchAll();
}

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    try {
        $db->beginTransaction();
        
        // Générer un numéro de facture unique
        $numero_facture = 'FAC-' . date('Ymd') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Insérer le paiement
        $query = "INSERT INTO paiements (
            numero_facture, patient_id, caissier_id, montant_total, 
            montant_paye, montant_restant, mode_paiement, statut, 
            observations, date_paiement
        ) VALUES (
            :numero_facture, :patient_id, :caissier_id, :montant,
            :montant, 0, :mode_paiement, 'paye',
            :observations, NOW()
        )";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':numero_facture' => $numero_facture,
            ':patient_id' => $_POST['patient_id'],
            ':caissier_id' => $_SESSION['user_id'],
            ':montant' => $_POST['montant'],
            ':mode_paiement' => $_POST['mode_paiement'],
            ':observations' => 'Paiement traitement - RDV: ' . $_POST['rdv_id']
        ]);
        
        // Mettre à jour le statut du rendez-vous
        $update_rdv = "UPDATE rendez_vous SET statut = 'honore' WHERE id = ?";
        $stmt_rdv = $db->prepare($update_rdv);
        $stmt_rdv->execute([$_POST['rdv_id']]);
        
        // Ajouter à la file d'attente si nécessaire
        $token = 'TK' . date('ymd') . strtoupper(substr(uniqid(), -5));
        $insert_queue = "INSERT INTO file_attente (token, patient_id, service_id, priorite, statut, cree_a) 
                        VALUES (?, ?, ?, 'normal', 'en_attente', NOW())";
        $stmt_queue = $db->prepare($insert_queue);
        $stmt_queue->execute([$token, $_POST['patient_id'], $_POST['service_id']]);
        
        $db->commit();
        
        $success_message = "Paiement effectué avec succès ! Facture: " . $numero_facture . " - Token: " . $token;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error_message = "Erreur: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Avancée - Caisse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px;
            color: white;
        }
        .navbar a { color: white; text-decoration: none; }
        .container-fluid { padding: 20px; }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: -20px -20px 20px -20px;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
        }
        
        .search-section {
            background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-control, .form-select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .rdv-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .rdv-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        .rdv-card.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .rdv-card.selected .text-muted { color: rgba(255,255,255,0.8) !important; }
        
        .badge-statut {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-programme { background: #ffc107; color: #333; }
        .badge-confirme { background: #17a2b8; color: white; }
        .badge-honore { background: #28a745; color: white; }
        .badge-annule { background: #dc3545; color: white; }
        
        .payment-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
        
        .montant-display {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .patient-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .stats-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .stats-number {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <h3><i class="fas fa-search"></i> Recherche Avancée des Rendez-vous</h3>
                <div>
                    <a href="index.php" class="btn btn-sm btn-light me-2">
                        <i class="fas fa-home"></i> Accueil Caisse
                    </a>
                    <a href="../auth/logout.php" class="btn btn-sm btn-light">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulaire de recherche avancée -->
        <div class="search-section">
            <h4 class="mb-3"><i class="fas fa-filter"></i> Filtres de recherche</h4>
            <form method="POST" action="">
                <div class="filter-row">
                    <div>
                        <label class="form-label">Date du rendez-vous</label>
                        <input type="date" name="date_rdv" class="form-control" value="<?= htmlspecialchars($_POST['date_rdv'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div>
                        <label class="form-label">Service/Département</label>
                        <select name="service_id" class="form-select">
                            <option value="">Tous les services</option>
                            <?php foreach($services as $service): ?>
                                <option value="<?= $service['id'] ?>" <?= ($_POST['service_id'] ?? '') == $service['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($service['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Médecin</label>
                        <select name="medecin_id" class="form-select">
                            <option value="">Tous les médecins</option>
                            <?php foreach($medecins as $medecin): ?>
                                <option value="<?= $medecin['id'] ?>" <?= ($_POST['medecin_id'] ?? '') == $medecin['id'] ? 'selected' : '' ?>>
                                    Dr. <?= htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-row">
                    <div>
                        <label class="form-label">Nom du patient</label>
                        <input type="text" name="nom" class="form-control" placeholder="Nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" placeholder="Prénom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" placeholder="Téléphone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                    </div>
                </div>

                <div class="filter-row">
                    <div>
                        <label class="form-label">Code patient</label>
                        <input type="text" name="code_patient" class="form-control" placeholder="Code patient" value="<?= htmlspecialchars($_POST['code_patient'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="programme" <?= ($_POST['statut'] ?? 'programme') == 'programme' ? 'selected' : '' ?>>À venir</option>
                            <option value="confirme" <?= ($_POST['statut'] ?? '') == 'confirme' ? 'selected' : '' ?>>Confirmé</option>
                            <option value="honore" <?= ($_POST['statut'] ?? '') == 'honore' ? 'selected' : '' ?>>Honoré</option>
                            <option value="annule" <?= ($_POST['statut'] ?? '') == 'annule' ? 'selected' : '' ?>>Annulé</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-end">
                        <button type="submit" name="search" class="btn-search w-100">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Statistiques rapides -->
        <?php if ($search_performed): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= count($search_results) ?></div>
                        <div class="text-muted">Rendez-vous trouvés</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number">
                            <?= count(array_filter($search_results, fn($r) => $r['statut'] == 'programme' || $r['statut'] == 'confirme')) ?>
                        </div>
                        <div class="text-muted">En attente</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number">
                            <?= count(array_filter($search_results, fn($r) => $r['statut'] == 'honore')) ?>
                        </div>
                        <div class="text-muted">Honorés</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= date('d/m/Y') ?></div>
                        <div class="text-muted">Date du jour</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Liste des rendez-vous -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-check"></i> 
                        Résultats de la recherche 
                        <?php if ($search_performed): ?>
                            <span class="badge bg-light text-dark ms-2"><?= count($search_results) ?> rendez-vous</span>
                        <?php endif; ?>
                    </div>
                    <div style="max-height: 600px; overflow-y: auto;">
                        <?php if ($search_performed && empty($search_results)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i> Aucun rendez-vous trouvé avec ces critères
                            </div>
                        <?php elseif (!empty($search_results)): ?>
                            <?php foreach($search_results as $rdv): ?>
                                <div class="rdv-card" onclick="selectRendezVous(<?= htmlspecialchars(json_encode($rdv)) ?>)">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge-statut badge-<?= $rdv['statut'] ?> me-2">
                                                    <?= $rdv['statut_label'] ?>
                                                </span>
                                                <strong><?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?></strong>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($rdv['date_rdv'])) ?> à <?= substr($rdv['heure_rdv'], 0, 5) ?><br>
                                                <i class="fas fa-stethoscope"></i> <?= htmlspecialchars($rdv['service_nom']) ?><br>
                                                <?php if (!empty($rdv['medecin_nom'])): ?>
                                                    <i class="fas fa-user-md"></i> Dr. <?= htmlspecialchars($rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']) ?><br>
                                                <?php endif; ?>
                                                <i class="fas fa-phone"></i> <?= htmlspecialchars($rdv['patient_telephone'] ?? 'Non renseigné') ?>
                                                <?php if (!empty($rdv['code_patient_unique'])): ?>
                                                    <br><i class="fas fa-id-card"></i> Code: <?= htmlspecialchars($rdv['code_patient_unique']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <?php if ($rdv['statut'] == 'programme' || $rdv['statut'] == 'confirme'): ?>
                                                <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); preparePaiement(<?= htmlspecialchars(json_encode($rdv)) ?>)">
                                                    <i class="fas fa-credit-card"></i> Payer
                                                </button>
                                            <?php elseif ($rdv['statut'] == 'honore'): ?>
                                                <span class="badge bg-success">Payé</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Formulaire de paiement -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-credit-card"></i> Paiement du traitement
                    </div>
                    <div class="payment-form" id="paymentForm" style="display: none;">
                        <form method="POST" action="" onsubmit="return validatePayment()">
                            <input type="hidden" name="process_payment" value="1">
                            <input type="hidden" name="rdv_id" id="rdv_id">
                            <input type="hidden" name="patient_id" id="patient_id">
                            <input type="hidden" name="service_id" id="service_id">
                            
                            <div class="patient-info" id="patientInfo">
                                <!-- Rempli par JavaScript -->
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Traitement/Acte</label>
                                <select name="acte_id" id="acte_id" class="form-select" required onchange="updateMontant()">
                                    <option value="">Sélectionner un traitement</option>
                                    <?php foreach($actes as $acte): ?>
                                        <option value="<?= $acte['id'] ?>" 
                                                data-prix="<?= $acte['prix_traitement'] ?>"
                                                data-service="<?= $acte['service_id'] ?>">
                                            <?= htmlspecialchars($acte['libelle']) ?> - 
                                            <?= htmlspecialchars($acte['service_nom']) ?> - 
                                            <?= number_format($acte['prix_traitement'], 0, ',', ' ') ?> FCFA
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Médecin traitant</label>
                                <input type="text" class="form-control" id="medecin_info" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Date du rendez-vous</label>
                                <input type="text" class="form-control" id="date_rdv_info" readonly>
                            </div>
                            
                            <div class="montant-display" id="montantDisplay">
                                0 FCFA
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mode de paiement</label>
                                <select name="mode_paiement" class="form-select" required>
                                    <option value="especes">Espèces</option>
                                    <option value="carte">Carte bancaire</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="assurance">Assurance</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observations</label>
                                <textarea name="observations" class="form-control" rows="2" placeholder="Notes sur le paiement..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100" style="padding: 12px; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Confirmer le paiement
                            </button>
                        </form>
                    </div>
                    
                    <div id="noSelectionMessage" class="text-center p-4 text-muted">
                        <i class="fas fa-arrow-left fa-2x mb-3"></i>
                        <p>Sélectionnez un rendez-vous pour effectuer le paiement</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentRdv = null;
        
        function selectRendezVous(rdv) {
            currentRdv = rdv;
            
            // Mettre en évidence la carte sélectionnée
            document.querySelectorAll('.rdv-card').forEach(c => c.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            
            // Préparer le formulaire de paiement
            preparePaiement(rdv);
        }
        
        function preparePaiement(rdv) {
            document.getElementById('paymentForm').style.display = 'block';
            document.getElementById('noSelectionMessage').style.display = 'none';
            
            document.getElementById('rdv_id').value = rdv.id;
            document.getElementById('patient_id').value = rdv.patient_id;
            document.getElementById('service_id').value = rdv.service_id;
            
            // Afficher les informations du patient
            const patientInfo = document.getElementById('patientInfo');
            patientInfo.innerHTML = `
                <strong>Patient:</strong> ${rdv.patient_prenom} ${rdv.patient_nom}<br>
                <strong>Téléphone:</strong> ${rdv.patient_telephone || 'Non renseigné'}<br>
                <strong>Code patient:</strong> ${rdv.code_patient_unique || 'N/A'}<br>
                <strong>Service:</strong> ${rdv.service_nom}
            `;
            
            document.getElementById('medecin_info').value = rdv.medecin_nom ? `Dr. ${rdv.medecin_prenom} ${rdv.medecin_nom}` : 'Non assigné';
            document.getElementById('date_rdv_info').value = new Date(rdv.date_rdv).toLocaleDateString('fr-FR') + ' à ' + rdv.heure_rdv.substr(0,5);
            
            // Réinitialiser la sélection du traitement
            document.getElementById('acte_id').value = '';
            document.getElementById('montantDisplay').textContent = '0 FCFA';
        }
        
        function updateMontant() {
            const select = document.getElementById('acte_id');
            const selected = select.options[select.selectedIndex];
            
            if (selected && selected.value) {
                const prix = selected.getAttribute('data-prix');
                document.getElementById('montantDisplay').textContent = formatMoney(prix) + ' FCFA';
            } else {
                document.getElementById('montantDisplay').textContent = '0 FCFA';
            }
        }
        
        function validatePayment() {
            if (!document.getElementById('acte_id').value) {
                alert('Veuillez sélectionner un traitement');
                return false;
            }
            
            const montant = parseInt(document.getElementById('montantDisplay').textContent.replace(/[^0-9]/g, '')) || 0;
            if (montant <= 0) {
                alert('Montant invalide');
                return false;
            }
            
            return confirm('Confirmez-vous le paiement de ' + formatMoney(montant) + ' FCFA ?');
        }
        
        function formatMoney(montant) {
            return new Intl.NumberFormat('fr-FR').format(montant);
        }
    </script>
</body>
</html>
