<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

$id_fiche = $_GET['id'] ?? null;
if (!$id_fiche) die("Erreur : ID de fiche manquant.");

// Récupération des données avec jointures clients et véhicules
$sql = "SELECT f.*, v.marque, v.modele, v.immatriculation, v.dernier_km, 
               c.nom as client, c.telephone, c.email as email_client
        FROM fiches_intervention f
        JOIN vehicules v ON f.id_vehicule = v.id_vehicule
        JOIN clients c ON v.id_client = c.id_client
        WHERE f.id_fiche = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id_fiche]);
$d = $stmt->fetch();

if (!$d) die("Erreur : Devis introuvable.");

// Préparation des liens de communication
$message_wa = "Bonjour " . $d['client'] . ", voici votre devis OMEGA TECH pour le véhicule " . $d['immatriculation'] . ". Total : " . number_format($d['cout_main_doeuvre'], 0, ',', ' ') . " F. Lien : http://127.0.0.1:8080/scripts/factures/generer_devis.php?id=" . $id_fiche;
$wa_url = "https://wa.me/" . preg_replace('/[^0-9]/', '', $d['telephone']) . "?text=" . urlencode($message_wa);

$email_subject = "Devis OMEGA TECH - " . $d['immatriculation'];
$email_body = "Bonjour " . $d['client'] . ",\n\nVeuillez trouver ci-joint le lien vers votre devis pour l'intervention sur votre " . $d['marque'] . ".\nLien : http://127.0.0.1:8080/scripts/factures/generer_devis.php?id=" . $id_fiche . "\n\nCordialement,\nL'équipe OMEGA TECH.";
$mail_url = "mailto:" . ($d['email_client'] ?? '') . "?subject=" . urlencode($email_subject) . "&body=" . urlencode($email_body);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEVIS_OMEGA_<?= $d['immatriculation'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .devis-container { background: white; max-width: 900px; margin: 30px auto; padding: 50px; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header-logo { border-bottom: 4px solid #0d47a1; margin-bottom: 30px; padding-bottom: 20px; }
        .table-custom thead { background: #0d47a1; color: white; }
        .watermark { position: absolute; top: 40%; left: 25%; font-size: 8rem; opacity: 0.05; transform: rotate(-30deg); font-weight: bold; pointer-events: none; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .devis-container { box-shadow: none; margin: 0; max-width: 100%; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="container no-print mt-4 mb-4 text-center">
    <div class="btn-group shadow-lg">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="fas fa-print me-2"></i>PDF / Imprimer
        </button>
        <a href="<?= $wa_url ?>" target="_blank" class="btn btn-success btn-lg">
            <i class="fab fa-whatsapp me-2"></i>WhatsApp
        </a>
        <a href="<?= $mail_url ?>" class="btn btn-dark btn-lg">
            <i class="fas fa-envelope me-2"></i>Email Client
        </a>
        <a href="../interventions/dashboard_ingenieur.php" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
</div>

<div class="devis-container position-relative">
    <div class="watermark text-uppercase">Proforma</div>
    
    <div class="header-logo d-flex justify-content-between align-items-start">
        <div>
            <h1 class="text-primary fw-bold mb-0">OMEGA TECH <span class="text-warning">GARAGE</span></h1>
            <p class="text-muted small">
                Expertise Systèmes & Ingénierie Automobile<br>
                Dakar, Sacré-Cœur 3, Sénégal<br>
                Tél : +221 33 000 00 00 | Email : contact@omegatech.sn
            </p>
        </div>
        <div class="text-end">
            <h2 class="fw-bold text-uppercase">Devis Proforma</h2>
            <p class="mb-0 text-muted">Référence : <strong>#<?= date('Y') ?>-<?= str_pad($id_fiche, 4, '0', STR_PAD_LEFT) ?></strong></p>
            <p class="text-muted small">Émis le : <?= date('d/m/Y à H:i') ?></p>
        </div>
    </div>

    <div class="row mb-5 mt-4">
        <div class="col-6 p-3 bg-light rounded-3">
            <h6 class="text-primary fw-bold text-uppercase small border-bottom pb-2">Information Véhicule</h6>
            <div class="mt-2">
                <strong>Marque/Modèle :</strong> <?= $d['marque'] ?> <?= $d['modele'] ?><br>
                <strong>Immatriculation :</strong> <span class="badge bg-danger fs-6"><?= $d['immatriculation'] ?></span><br>
                <strong>Kilométrage :</strong> <?= number_format($d['dernier_km'], 0, ',', ' ') ?> KM
            </div>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-primary fw-bold text-uppercase small border-bottom pb-2">Destinataire</h6>
            <div class="mt-2 text-uppercase fw-bold fs-5"><?= $d['client'] ?></div>
            <p class="text-muted">Contact : <?= $d['telephone'] ?><br><?= $d['email_client'] ?></p>
        </div>
    </div>

    <table class="table table-bordered table-custom shadow-sm">
        <thead>
            <tr>
                <th class="py-3">Désignation des Travaux & Diagnostic Technique</th>
                <th class="text-end py-3" width="180">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="py-4">
                    <h6 class="fw-bold"><?= $d['description_panne'] ?></h6>
                    <p class="text-muted small mb-0"><?= nl2br($d['diagnostic_technique']) ?></p>
                </td>
                <td class="text-end align-middle fw-bold fs-5">
                    <?= number_format($d['cout_main_doeuvre'], 0, ',', ' ') ?>
                </td>
            </tr>
        </tbody>
        <tfoot class="bg-light">
            <tr>
                <th class="text-end py-3 fs-5">TOTAL NET À PAYER :</th>
                <th class="text-end py-3 fs-4 text-primary"><?= number_format($d['cout_main_doeuvre'], 0, ',', ' ') ?> F</th>
            </tr>
        </tfoot>
    </table>

    <div class="mt-5 p-3 border rounded shadow-sm bg-light">
        <h6 class="fw-bold small"><i class="fas fa-info-circle me-2"></i>Conditions de service :</h6>
        <ul class="small text-muted mb-0">
            <li>Ce devis est valable 15 jours à compter de sa date d'émission.</li>
            <li>Garantie de 90 jours sur la main d'œuvre effectuée selon ce diagnostic.</li>
            <li>Les pièces de rechange ne sont pas incluses dans ce montant (sauf mention contraire).</li>
        </ul>
    </div>

    <div class="row mt-5 pt-4 text-center">
        <div class="col-4">
            <div class="border-bottom mx-auto w-75" style="height: 60px;"></div>
            <p class="small mt-2 text-muted text-uppercase">Le Client</p>
        </div>
        <div class="col-4 text-center">
            <i class="fas fa-stamp fa-4x text-muted opacity-25"></i>
        </div>
        <div class="col-4">
            <div class="border-bottom mx-auto w-75" style="height: 60px;"></div>
            <p class="small mt-2 text-muted text-uppercase">L'Ingénieur Conseil</p>
        </div>
    </div>
</div>

<div class="container text-center text-muted small mt-4 no-print">
    <p>OMEGA TECH ERP v3.0 - Propulsé par le moteur d'ingénierie Dakar.</p>
</div>

</body>
</html>
