<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once dirname(__DIR__) . '/config/config.php';

// Récupérer les données selon le type demandé
$type = $_GET['type'] ?? 'bilan';
$fichier = $type . '_' . date('Ymd') . '.pdf';

// Rediriger temporairement - Dans un vrai environnement, utiliser une librairie PDF
// Pour l'instant, afficher une version imprimable
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export <?= strtoupper($type) ?> - OMEGA ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 20px; }
        }
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print text-end mb-3">
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Imprimer / PDF</button>
            <a href="dashboard_expert.php" class="btn btn-secondary">Retour</a>
        </div>
        
        <?php if($type == 'bilan'): ?>
            <?php 
            $actif_total = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 200 AND 599")->fetchColumn();
            $passif_total = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 100 AND 199 OR compte_credite_id BETWEEN 600 AND 899")->fetchColumn();
            ?>
            <div class="print-header">
                <h2>BILAN SYNTHÉTIQUE</h2>
                <p>Arrêté au <?= date('d/m/Y') ?> - Exercice <?= date('Y') ?></p>
                <p><strong>OMEGA INFORMATIQUE CONSULTING ERP</strong></p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4>ACTIF</h4>
                    <table class="table table-bordered">
                        <tr><th>Poste</th><th class="text-end">Montant (FCFA)</th></tr>
                        <tr><td>Immobilisations (Classe 2)</td><td class="text-end">-</td></tr>
                        <tr><td>Stocks (Classe 3)</td><td class="text-end">-</td></tr>
                        <tr><td>Créances (Classe 4)</td><td class="text-end">-</td></tr>
                        <tr><td>Trésorerie (Classe 5)</td><td class="text-end"><?= number_format($actif_total, 0, ',', ' ') ?></td></tr>
                        <tr class="table-primary fw-bold"><td>TOTAL ACTIF</td><td class="text-end"><?= number_format($actif_total, 0, ',', ' ') ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h4>PASSIF</h4>
                    <table class="table table-bordered">
                        <tr><th>Poste</th><th class="text-end">Montant (FCFA)</th></tr>
                        <tr><td>Capitaux Propres (Classe 1)</td><td class="text-end">-</td></tr>
                        <tr><td>Dettes (Classe 6-8)</td><td class="text-end">-</td></tr>
                        <tr class="table-success fw-bold"><td>TOTAL PASSIF</td><td class="text-end"><?= number_format($passif_total, 0, ',', ' ') ?></td></tr>
                    </table>
                </div>
            </div>
        <?php elseif($type == 'compte_resultat'): ?>
            <?php
            $produits = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
            $charges = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
            $resultat = $produits - $charges;
            ?>
            <div class="print-header">
                <h2>COMPTE DE RÉSULTAT</h2>
                <p>Exercice du 1er janvier au <?= date('d/m/Y') ?></p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4>PRODUITS</h4>
                    <h2 class="text-success"><?= number_format($produits, 0, ',', ' ') ?> F</h2>
                </div>
                <div class="col-md-6">
                    <h4>CHARGES</h4>
                    <h2 class="text-danger"><?= number_format($charges, 0, ',', ' ') ?> F</h2>
                </div>
            </div>
            <div class="mt-4 p-3 bg-light">
                <h3 class="text-center">RÉSULTAT NET : <?= number_format(abs($resultat), 0, ',', ' ') ?> FCFA <?= $resultat >= 0 ? '(BÉNÉFICE)' : '(PERTE)' ?></h3>
            </div>
        <?php endif; ?>
        
        <div class="mt-5 text-center">
            <small>Document généré par OMEGA INFORMATIQUE CONSULTING ERP - Conforme SYSCOHADA UEMOA</small><br>
            <small>© <?= date('Y') ?> Mohamet Siby - Tous droits réservés</small>
        </div>
    </div>
</body>
</html>
