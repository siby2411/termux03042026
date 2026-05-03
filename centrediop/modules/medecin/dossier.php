<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer l'ID du patient depuis l'URL ou le token
$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    // Si pas d'ID dans l'URL, essayer de prendre celui du token
    $patient_token = getPatientFromToken();
    $patient_id = $patient_token['id'] ?? null;
}

if (!$patient_id) {
    $_SESSION['error'] = "Aucun patient sélectionné";
    header('Location: dashboard.php');
    exit();
}

// Récupérer les informations du patient
$stmt = $db->prepare("
    SELECT p.*, 
           (SELECT COUNT(*) FROM consultations WHERE patient_id = p.id) as nb_consultations,
           (SELECT MAX(date_consultation) FROM consultations WHERE patient_id = p.id) as derniere_consultation
    FROM patients p
    WHERE p.id = ?
");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    $_SESSION['error'] = "Patient non trouvé";
    header('Location: dashboard.php');
    exit();
}

// Récupérer l'historique des consultations
$consultations = $db->prepare("
    SELECT c.*, 
           u.prenom as medecin_prenom, 
           u.nom as medecin_nom,
           DATE_FORMAT(c.date_consultation, '%d/%m/%Y %H:%i') as date_consultation_format
    FROM consultations c
    LEFT JOIN users u ON c.medecin_id = u.id
    WHERE c.patient_id = ?
    ORDER BY c.date_consultation DESC
");
$consultations->execute([$patient_id]);
$historique_consultations = $consultations->fetchAll();

// Récupérer l'historique des rendez-vous
$rendezvous = $db->prepare("
    SELECT r.*,
           s.name as service_nom,
           DATE_FORMAT(r.date_rdv, '%d/%m/%Y') as date_rdv_format
    FROM rendez_vous r
    LEFT JOIN services s ON r.service_id = s.id
    WHERE r.patient_id = ?
    ORDER BY r.date_rdv DESC, r.heure_rdv DESC
");
$rendezvous->execute([$patient_id]);
$historique_rdv = $rendezvous->fetchAll();

// Récupérer les traitements
$traitements = $db->prepare("
    SELECT t.*,
           DATE_FORMAT(t.date_prescription, '%d/%m/%Y') as date_prescription_format
    FROM traitements t
    WHERE t.patient_id = ?
    ORDER BY t.date_prescription DESC
");
$traitements->execute([$patient_id]);
$historique_traitements = $traitements->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossier Patient - <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .patient-header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
        }
        .info-label {
            font-size: 0.85em;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1.1em;
            font-weight: 500;
        }
        .section-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .section-title {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #1e3c72;
        }
        .timeline-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }
        .badge-statut {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .badge-confirme { background: #d4edda; color: #155724; }
        .badge-programme { background: #fff3cd; color: #856404; }
        .badge-annule { background: #f8d7da; color: #721c24; }
        .badge-termine { background: #cce5ff; color: #004085; }
        .btn-retour {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-retour:hover {
            background: #5a6268;
            color: white;
        }
        .btn-consultation {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn-consultation:hover {
            background: #218838;
            color: white;
        }
        .allergie-badge {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
                    <a href="imprimer_dossier.php?patient_id=<?= $patient_id ?>" class="btn btn-sm btn-warning me-2" target="_blank">
                        <i class="fas fa-print"></i> Imprimer
                    </a>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h3><i class="fas fa-folder-open me-2"></i>Dossier Patient</h3>
                <div>
                    <span class="me-3">
                        <i class="fas fa-user-md"></i> Dr. <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?>
                    </span>
                    <a href="dashboard.php" class="btn btn-sm btn-light me-2">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                    <a href="consultation.php?patient_id=<?= $patient_id ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-stethoscope"></i> Consultation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- En-tête patient -->
        <div class="patient-header">
            <div class="row">
                <div class="col-md-8">
                    <h2>
                        <i class="fas fa-user-injured text-primary me-2"></i>
                        <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?>
                    </h2>
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="info-label">Code Patient</div>
                            <div class="info-value"><?= htmlspecialchars($patient['code_patient_unique']) ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Téléphone</div>
                            <div class="info-value"><?= htmlspecialchars($patient['telephone'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?= htmlspecialchars($patient['email'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Date naissance</div>
                            <div class="info-value"><?= date('d/m/Y', strtotime($patient['date_naissance'])) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="mb-2">
                        <span class="badge bg-primary p-2">Total consultations: <?= $patient['nb_consultations'] ?></span>
                    </div>
                    <?php if ($patient['derniere_consultation']): ?>
                        <small class="text-muted">
                            Dernière consultation: <?= date('d/m/Y', strtotime($patient['derniere_consultation'])) ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($patient['allergie']) && $patient['allergie'] != 'X' && $patient['allergie'] != 'Aucune'): ?>
            <div class="mt-3">
                <span class="allergie-badge">
                    <i class="fas fa-exclamation-triangle me-1"></i> Allergie: <?= htmlspecialchars($patient['allergie']) ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($patient['antecedent_medicaux']) && $patient['antecedent_medicaux'] != 'X'): ?>
            <div class="mt-2">
                <strong>Antécédents:</strong> <?= htmlspecialchars($patient['antecedent_medicaux']) ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Colonne gauche: Consultations et Traitements -->
            <div class="col-md-6">
                <!-- Historique des consultations -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-stethoscope me-2"></i>Historique des consultations
                    </div>
                    
                    <?php if (empty($historique_consultations)): ?>
                        <p class="text-muted text-center py-3">Aucune consultation enregistrée</p>
                    <?php else: ?>
                        <?php foreach ($historique_consultations as $c): ?>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between">
                                <strong><?= $c['date_consultation_format'] ?></strong>
                                <small>Dr. <?= htmlspecialchars($c['medecin_prenom'] . ' ' . $c['medecin_nom']) ?></small>
                            </div>
                            <div class="mt-2">
                                <strong>Motif:</strong> <?= htmlspecialchars($c['motif'] ?? 'Non spécifié') ?>
                            </div>
                            <?php if (!empty($c['diagnostic'])): ?>
                            <div class="mt-1">
                                <strong>Diagnostic:</strong> <?= htmlspecialchars($c['diagnostic']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($c['observations'])): ?>
                            <div class="mt-1 text-muted">
                                <small><?= htmlspecialchars($c['observations']) ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Traitements en cours -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-prescription me-2"></i>Historique des traitements
                    </div>
                    
                    <?php if (empty($historique_traitements)): ?>
                        <p class="text-muted text-center py-3">Aucun traitement enregistré</p>
                    <?php else: ?>
                        <?php foreach ($historique_traitements as $t): ?>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between">
                                <strong><?= $t['date_prescription_format'] ?></strong>
                                <span class="badge bg-info"><?= htmlspecialchars($t['medecin_prescripteur'] ?? 'Dr.') ?></span>
                            </div>
                            <div class="mt-2">
                                <strong><?= htmlspecialchars($t['medicament']) ?></strong>
                            </div>
                            <div class="mt-1">
                                <small>Posologie: <?= htmlspecialchars($t['posologie'] ?? 'N/A') ?></small>
                                <?php if (!empty($t['duree'])): ?>
                                <br><small>Durée: <?= htmlspecialchars($t['duree']) ?></small>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($t['instructions'])): ?>
                            <div class="mt-1 text-muted">
                                <small>⚠️ <?= htmlspecialchars($t['instructions']) ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Colonne droite: Rendez-vous et Informations -->
            <div class="col-md-6">
                <!-- Historique des rendez-vous -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt me-2"></i>Historique des rendez-vous
                    </div>
                    
                    <?php if (empty($historique_rdv)): ?>
                        <p class="text-muted text-center py-3">Aucun rendez-vous enregistré</p>
                    <?php else: ?>
                        <?php foreach ($historique_rdv as $r): ?>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between">
                                <strong><?= $r['date_rdv_format'] ?> <?= substr($r['heure_rdv'], 0, 5) ?></strong>
                                <span class="badge-statut badge-<?= $r['statut'] ?>">
                                    <?= ucfirst($r['statut']) ?>
                                </span>
                            </div>
                            <div class="mt-1">
                                <i class="fas fa-stethoscope me-1"></i> <?= htmlspecialchars($r['service_nom'] ?? 'N/A') ?>
                            </div>
                            <?php if (!empty($r['motif'])): ?>
                            <div class="mt-1 text-muted">
                                <small>Motif: <?= htmlspecialchars($r['motif']) ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Informations complémentaires -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-info-circle me-2"></i>Informations complémentaires
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <th>Lieu de naissance:</th>
                            <td><?= htmlspecialchars($patient['lieu_naissance'] ?? 'Non renseigné') ?></td>
                        </tr>
                        <tr>
                            <th>Sexe:</th>
                            <td><?= $patient['sexe'] == 'M' ? 'Masculin' : 'Féminin' ?></td>
                        </tr>
                        <tr>
                            <th>Groupe sanguin:</th>
                            <td><?= htmlspecialchars($patient['groupe_sanguin'] ?? 'Non renseigné') ?></td>
                        </tr>
                        <tr>
                            <th>Adresse:</th>
                            <td><?= htmlspecialchars($patient['adresse'] ?? 'Non renseignée') ?></td>
                        </tr>
                        <tr>
                            <th>Personne à contacter:</th>
                            <td>
                                <?= htmlspecialchars($patient['personne_contact'] ?? 'Non renseigné') ?>
                                <?php if (!empty($patient['telephone_contact'])): ?>
                                <br><small><?= htmlspecialchars($patient['telephone_contact']) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Créé le:</th>
                            <td><?= date('d/m/Y H:i', strtotime($patient['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
