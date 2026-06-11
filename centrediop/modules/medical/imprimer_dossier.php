<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    header('Location: edition_dossier.php');
    exit();
}

// Récupérer les informations du patient
$stmt = $db->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: edition_dossier.php');
    exit();
}

// Récupérer les consultations
$consults = $db->prepare("
    SELECT c.*, u.prenom as med_prenom, u.nom as med_nom
    FROM consultations c
    LEFT JOIN users u ON c.medecin_id = u.id
    WHERE c.patient_id = ?
    ORDER BY c.date_consultation DESC
");
$consults->execute([$patient_id]);
$consultations = $consults->fetchAll();

// Récupérer les rendez-vous
$rdvs = $db->prepare("
    SELECT r.*, s.name as service_nom
    FROM rendez_vous r
    LEFT JOIN services s ON r.service_id = s.id
    WHERE r.patient_id = ?
    ORDER BY r.date_rdv DESC, r.heure_rdv DESC
");
$rdvs->execute([$patient_id]);
$rendezvous = $rdvs->fetchAll();

// Récupérer les traitements
$trait = $db->prepare("
    SELECT * FROM traitements 
    WHERE patient_id = ?
    ORDER BY date_prescription DESC
");
$trait->execute([$patient_id]);
$traitements = $trait->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dossier Patient - Impression</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { font-size: 12pt; }
        }
        .header-print {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }
        .section-title {
            background: #f0f0f0;
            padding: 10px;
            margin: 20px 0 10px;
            font-weight: bold;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="no-print text-end mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <a href="dossier.php?patient_id=<?= $patient_id ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
        
        <div class="header-print">
            <h1>OMEGA INFORMATIQUE</h1>
            <h3>JUMTO WER SOLUTION DESIGN</h3>
            <h2>DOSSIER MÉDICAL</h2>
        </div>
        
        <div class="section-title">INFORMATIONS PATIENT</div>
        <div class="info-grid">
            <div><strong>Code:</strong> <?= $patient['code_patient_unique'] ?></div>
            <div><strong>Nom:</strong> <?= $patient['nom'] ?></div>
            <div><strong>Prénom:</strong> <?= $patient['prenom'] ?></div>
            <div><strong>Téléphone:</strong> <?= $patient['telephone'] ?></div>
            <div><strong>Date naissance:</strong> <?= $patient['date_naissance'] ?></div>
            <div><strong>Groupe sanguin:</strong> <?= $patient['groupe_sanguin'] ?? 'N/A' ?></div>
        </div>
        
        <?php if (!empty($patient['allergie']) && $patient['allergie'] != 'Aucune'): ?>
        <div class="alert alert-danger">
            <strong>Allergie:</strong> <?= $patient['allergie'] ?>
        </div>
        <?php endif; ?>
        
        <div class="section-title">HISTORIQUE DES CONSULTATIONS</div>
        <?php if (empty($consultations)): ?>
            <p>Aucune consultation</p>
        <?php else: ?>
            <?php foreach ($consultations as $c): ?>
            <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                <strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($c['date_consultation'])) ?><br>
                <strong>Médecin:</strong> Dr. <?= $c['med_prenom'] ?? '' ?> <?= $c['med_nom'] ?? '' ?><br>
                <strong>Motif:</strong> <?= $c['motif'] ?><br>
                <strong>Diagnostic:</strong> <?= $c['diagnostic'] ?><br>
                <?php if (!empty($c['observations'])): ?>
                    <strong>Observations:</strong> <?= $c['observations'] ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="section-title">HISTORIQUE DES RENDEZ-VOUS</div>
        <?php if (empty($rendezvous)): ?>
            <p>Aucun rendez-vous</p>
        <?php else: ?>
            <?php foreach ($rendezvous as $r): ?>
            <div style="border: 1px solid #ddd; padding: 5px; margin-bottom: 5px;">
                <?= date('d/m/Y', strtotime($r['date_rdv'])) ?> à <?= substr($r['heure_rdv'], 0, 5) ?> - 
                <?= $r['service_nom'] ?? 'N/A' ?> (<?= $r['statut'] ?>)
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="section-title">HISTORIQUE DES TRAITEMENTS</div>
        <?php if (empty($traitements)): ?>
            <p>Aucun traitement</p>
        <?php else: ?>
            <?php foreach ($traitements as $t): ?>
            <div style="border: 1px solid #ddd; padding: 5px; margin-bottom: 5px;">
                <strong><?= $t['medicament'] ?></strong> - <?= $t['posologie'] ?> 
                (<?= $t['duree'] ?>)<br>
                <small>Prescrit le <?= date('d/m/Y', strtotime($t['date_prescription'])) ?></small>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="text-center mt-5">
            <small>Document généré le <?= date('d/m/Y à H:i') ?> par <?= $_SESSION['user_prenom'] ?> <?= $_SESSION['user_nom'] ?></small>
        </div>
    </div>
</body>
</html>
