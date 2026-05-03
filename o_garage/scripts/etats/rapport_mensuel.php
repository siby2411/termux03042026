<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

$mois = $_GET['mois'] ?? date('Y-m');
$titre_mois = date('F Y', strtotime($mois . '-01'));

// --- LOGIQUE DE CALCUL ---
// 1. CA par département
$ca_mo = $db->query("SELECT SUM(cout_main_doeuvre) FROM fiches_intervention WHERE statut='Terminé' AND date_entree LIKE '$mois%'")->fetchColumn() ?? 0;
$ca_pieces = $db->query("SELECT SUM(total_vente) FROM factures_pieces WHERE date_facture LIKE '$mois%'")->fetchColumn() ?? 0;
$ca_lavage = $db->query("SELECT SUM(montant) FROM lavage_transactions WHERE date_lavage LIKE '$mois%'")->fetchColumn() ?? 0;
$total_general = $ca_mo + $ca_pieces + $ca_lavage;

// 2. Top Pannes (Cartographie)
$pannes = $db->query("SELECT description_panne, COUNT(*) as nb FROM fiches_intervention WHERE date_entree LIKE '$mois%' GROUP BY description_panne ORDER BY nb DESC LIMIT 5")->fetchAll();

// 3. Performance Mécaniciens
$equipe = $db->query("SELECT p.nom_complet, COUNT(f.id_fiche) as interventions, SUM(f.cout_main_doeuvre) as ca 
                       FROM personnel p 
                       LEFT JOIN fiches_intervention f ON p.id_personnel = f.id_mec_1 
                       WHERE f.date_entree LIKE '$mois%' OR f.id_fiche IS NULL 
                       GROUP BY p.id_personnel")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport_Mensuel_<?= $mois ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .report-paper { background: white; max-width: 1000px; margin: 30px auto; padding: 50px; border: 1px solid #ddd; position: relative; }
        .stat-card { border: 1px solid #eee; padding: 15px; border-radius: 10px; text-align: center; }
        .section-title { border-left: 5px solid #ff6d00; padding-left: 15px; margin-bottom: 25px; font-weight: bold; color: #0d47a1; }
        @media print { .no-print { display: none; } .report-paper { border: none; margin: 0; width: 100%; } }
    </style>
</head>
<body>

<div class="container no-print mt-4 text-center">
    <form class="d-inline-block me-3">
        <input type="month" name="mois" value="<?= $mois ?>" onchange="this.form.submit()">
    </form>
    <button onclick="window.print()" class="btn btn-dark shadow-sm"><i class="fas fa-print"></i> Exporter en PDF / Imprimer</button>
    <a href="../../index.php" class="btn btn-outline-secondary">Retour au Dashboard</a>
</div>

<div class="report-paper shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-4">
        <div>
            <h1 class="fw-bold text-primary mb-0">OMEGA TECH</h1>
            <p class="text-muted">Système Intégré de Gestion Automobile | Dakar</p>
        </div>
        <div class="text-end">
            <h2 class="text-uppercase text-muted h4">Rapport d'Activité Mensuel</h2>
            <p class="badge bg-warning text-dark fs-5"><?= $titre_mois ?></p>
        </div>
    </div>

    <h4 class="section-title">I. SYNTHÈSE FINANCIÈRE</h4>
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <small class="text-muted">Atelier (MO)</small>
                <h4 class="fw-bold"><?= number_format($ca_mo, 0, ',', ' ') ?> F</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <small class="text-muted">Magasin (Pièces)</small>
                <h4 class="fw-bold"><?= number_format($ca_pieces, 0, ',', ' ') ?> F</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <small class="text-muted">Service Lavage</small>
                <h4 class="fw-bold"><?= number_format($ca_lavage, 0, ',', ' ') ?> F</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-primary text-white">
                <small>TOTAL GÉNÉRAL</small>
                <h4 class="fw-bold"><?= number_format($total_general, 0, ',', ' ') ?> F</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h4 class="section-title">II. TOP PANNES IDENTIFIÉES</h4>
            <table class="table table-sm">
                <thead class="table-light"><tr><th>Désignation de la Panne</th><th class="text-end">Fréquence</th></tr></thead>
                <tbody>
                    <?php foreach($pannes as $p): ?>
                    <tr><td><?= $p['description_panne'] ?></td><td class="text-end fw-bold"><?= $p['nb'] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h4 class="section-title">III. PERFORMANCE ÉQUIPE</h4>
            <table class="table table-sm">
                <thead class="table-light"><tr><th>Technicien</th><th class="text-end">Interv.</th><th class="text-end">CA Généré</th></tr></thead>
                <tbody>
                    <?php foreach($equipe as $e): ?>
                    <tr><td><?= $e['nom_complet'] ?></td><td class="text-end"><?= $e['interventions'] ?></td><td class="text-end fw-bold"><?= number_format($e['ca'], 0, ',', ' ') ?> F</td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5 pt-5 border-top text-center small text-muted">
        Document généré par OMEGA ERP - Direction Technique - Mohamed Siby<br>
        Date d'extraction : <?= date('d/m/Y H:i') ?>
    </div>
</div>

</body>
</html>
