<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'caissier') {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$date = $_GET['date'] ?? date('Y-m-d');
$caissier_id = $_SESSION['user_id'];

// Récupérer les infos du caissier
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$caissier_id]);
$caissier = $stmt->fetch();

// Statistiques globales du caissier
$global_stats = [
    'total_patients' => $db->prepare("SELECT COUNT(*) FROM patients WHERE created_by = ?"),
    'total_paiements' => $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ?"),
    'total_montant' => $db->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM paiements WHERE caissier_id = ?")
];

$global_stats['total_patients']->execute([$caissier_id]);
$global_stats['total_paiements']->execute([$caissier_id]);
$global_stats['total_montant']->execute([$caissier_id]);

// Statistiques du jour sélectionné
$stats = [
    'patients_crees' => $db->prepare("SELECT COUNT(*) FROM patients WHERE created_by = ? AND DATE(created_at) = ?"),
    'paiements' => $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = ?"),
    'montant' => $db->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = ?"),
    'par_mode' => $db->prepare("
        SELECT mode_paiement, COUNT(*) as nombre, SUM(montant_total) as total
        FROM paiements 
        WHERE caissier_id = ? AND DATE(date_paiement) = ?
        GROUP BY mode_paiement
    ")
];

$stats['patients_crees']->execute([$caissier_id, $date]);
$stats['paiements']->execute([$caissier_id, $date]);
$stats['montant']->execute([$caissier_id, $date]);
$stats['par_mode']->execute([$caissier_id, $date]);
$paiements_par_mode = $stats['par_mode']->fetchAll();

// Liste des paiements du jour
$paiements = $db->prepare("
    SELECT p.*, pat.nom, pat.prenom, pat.code_patient_unique
    FROM paiements p
    JOIN patients pat ON p.patient_id = pat.id
    WHERE p.caissier_id = ? AND DATE(p.date_paiement) = ?
    ORDER BY p.date_paiement DESC
");
$paiements->execute([$caissier_id, $date]);
$liste_paiements = $paiements->fetchAll();

// Liste des patients créés le jour
$patients = $db->prepare("
    SELECT * FROM patients 
    WHERE created_by = ? AND DATE(created_at) = ?
    ORDER BY created_at DESC
");
$patients->execute([$caissier_id, $date]);
$liste_patients = $patients->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État Journalier - <?= $caissier['prenom'] ?> <?= $caissier['nom'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px;
        }
        .stats-global {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stats-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #1e3c72;
        }
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <div class="navbar no-print">
        <div class="container">
            <h4><i class="fas fa-chart-bar"></i> État Journalier - <?= $caissier['prenom'] ?> <?= $caissier['nom'] ?></h4>
            <div>
                <a href="dashboard.php" class="btn btn-sm btn-light me-2">Retour</a>
                <button onclick="window.print()" class="btn btn-sm btn-success">
                    <i class="fas fa-print"></i> Imprimer
                </button>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <!-- Sélecteur de date -->
        <div class="card no-print mb-4">
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-10">
                        <input type="date" name="date" class="form-control" value="<?= $date ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Voir</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- En-tête du rapport -->
        <div class="stats-global">
            <div class="row">
                <div class="col-md-6">
                    <h3>État journalier du <?= date('d/m/Y', strtotime($date)) ?></h3>
                    <p>Caissier: <?= $caissier['prenom'] ?> <?= $caissier['nom'] ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Généré le <?= date('d/m/Y à H:i') ?></p>
                </div>
            </div>
        </div>

        <!-- Statistiques globales du caissier -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $global_stats['total_patients']->fetchColumn() ?></div>
                    <div>Total patients créés</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $global_stats['total_paiements']->fetchColumn() ?></div>
                    <div>Total paiements</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($global_stats['total_montant']->fetchColumn(), 0, ',', ' ') ?> F</div>
                    <div>Total encaissé</div>
                </div>
            </div>
        </div>

        <!-- Résumé du jour -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?= $stats['patients_crees']->fetchColumn() ?></h3>
                    <p>Patients créés</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?= $stats['paiements']->fetchColumn() ?></h3>
                    <p>Paiements</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?= number_format($stats['montant']->fetchColumn(), 0, ',', ' ') ?> F</h3>
                    <p>Montant total</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?= count($liste_paiements) ?></h3>
                    <p>Transactions</p>
                </div>
            </div>
        </div>

        <!-- Détail par mode de paiement -->
        <?php if (!empty($paiements_par_mode)): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-chart-pie"></i> Répartition par mode de paiement
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mode de paiement</th>
                            <th>Nombre</th>
                            <th>Montant</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_jour = $stats['montant']->fetchColumn();
                        $stats['montant']->execute([$caissier_id, $date]); // Réinitialiser
                        foreach ($paiements_par_mode as $mode): 
                            $pourcentage = $total_jour > 0 ? round($mode['total'] * 100 / $total_jour, 1) : 0;
                        ?>
                        <tr>
                            <td><?= ucfirst($mode['mode_paiement']) ?></td>
                            <td><?= $mode['nombre'] ?></td>
                            <td class="fw-bold"><?= number_format($mode['total'], 0, ',', ' ') ?> F</td>
                            <td><?= $pourcentage ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste des paiements du jour -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-credit-card"></i> Paiements du <?= date('d/m/Y', strtotime($date)) ?>
            </div>
            <div class="card-body">
                <?php if (empty($liste_paiements)): ?>
                    <p class="text-muted">Aucun paiement ce jour</p>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Heure</th>
                                <th>Patient</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Mode</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($liste_paiements as $p): ?>
                            <tr>
                                <td><?= date('H:i', strtotime($p['date_paiement'])) ?></td>
                                <td><?= $p['prenom'] ?> <?= $p['nom'] ?></td>
                                <td><?= $p['code_patient_unique'] ?></td>
                                <td><?= $p['description'] ?></td>
                                <td><?= ucfirst($p['mode_paiement']) ?></td>
                                <td class="text-end fw-bold text-success"><?= number_format($p['montant_total'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="5" class="text-end">TOTAL:</td>
                                <td class="text-end"><?= number_format($stats['montant']->fetchColumn(), 0, ',', ' ') ?> F</td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Liste des patients créés le jour -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-user-plus"></i> Patients créés le <?= date('d/m/Y', strtotime($date)) ?>
            </div>
            <div class="card-body">
                <?php if (empty($liste_patients)): ?>
                    <p class="text-muted">Aucun patient créé ce jour</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Heure</th>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Téléphone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($liste_patients as $p): ?>
                            <tr>
                                <td><?= date('H:i', strtotime($p['created_at'])) ?></td>
                                <td><?= $p['code_patient_unique'] ?></td>
                                <td><?= $p['nom'] ?></td>
                                <td><?= $p['prenom'] ?></td>
                                <td><?= $p['telephone'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Résumé financier -->
        <div class="card">
            <div class="card-header bg-warning">
                <i class="fas fa-calculator"></i> Résumé financier
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td>Total paiements:</td>
                                <td class="fw-bold"><?= count($liste_paiements) ?></td>
                            </tr>
                            <tr>
                                <td>Montant total encaissé:</td>
                                <td class="fw-bold text-success"><?= number_format($stats['montant']->fetchColumn(), 0, ',', ' ') ?> F</td>
                            </tr>
                            <tr>
                                <td>Nombre de patients créés:</td>
                                <td class="fw-bold"><?= count($liste_patients) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-end">
                        <p>Cachet du caissier:</p>
                        <br><br>
                        <p>Signature: ____________________</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton d'impression flottant -->
    <div class="print-btn no-print">
        <button onclick="window.print()" class="btn btn-lg btn-success rounded-circle">
            <i class="fas fa-print"></i>
        </button>
    </div>
</body>
</html>
