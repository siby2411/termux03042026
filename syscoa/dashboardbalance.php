<?php
// dashboard.php
// TABLEAU DE BORD PRINCIPAL - Système Comptable OHADA
session_start();
require_once 'config/database.php';

try {
    $pdo = getPDOConnection();
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Erreur de connexion: " . $e->getMessage() . "</div>");
}

// Statistiques générales
$stats = [
    'comptes' => $pdo->query("SELECT COUNT(*) as nb FROM comptes_ohada")->fetch(PDO::FETCH_ASSOC)['nb'],
    'operations' => $pdo->query("SELECT COUNT(*) as nb FROM operations_comptables")->fetch(PDO::FETCH_ASSOC)['nb'],
    'tiers' => $pdo->query("SELECT COUNT(*) as nb FROM tiers")->fetch(PDO::FETCH_ASSOC)['nb'],
    'exercices' => $pdo->query("SELECT COUNT(*) as nb FROM exercices_comptables")->fetch(PDO::FETCH_ASSOC)['nb']
];

// Dernières opérations
$dernieres_operations = $pdo->query("
    SELECT date_operation, journal_code, description, montant 
    FROM operations_comptables 
    ORDER BY date_operation DESC, id_operation DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Exercice courant
$exercice_courant = $pdo->query("
    SELECT annee, date_debut, date_fin 
    FROM exercices_comptables 
    WHERE statut = 'ouvert' 
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Système OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card { transition: transform 0.3s ease; border: none; border-radius: 10px; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .module-card { border-left: 4px solid; transition: all 0.3s ease; }
        .module-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card dashboard-header shadow">
                    <div class="card-body text-center py-4">
                        <h1 class="h2 mb-3"><i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord Comptable</h1>
                        <p class="lead mb-0">Système conforme OHADA SYSCOHADA - Gestion professionnelle</p>
                        <?php if ($exercice_courant): ?>
                            <small class="opacity-75">Exercice <?php echo $exercice_courant['annee']; ?> en cours</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                        <h3><?php echo $stats['comptes']; ?></h3>
                        <p class="mb-0">Comptes OHADA</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                        <h3><?php echo $stats['operations']; ?></h3>
                        <p class="mb-0">Opérations</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h3><?php echo $stats['tiers']; ?></h3>
                        <p class="mb-0">Tiers</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                        <h3><?php echo $stats['exercices']; ?></h3>
                        <p class="mb-0">Exercices</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules principaux -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card module-card h-100 border-left-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="fas fa-book me-2"></i>Saisie Comptable</h5>
                        <p class="card-text">Saisie des écritures comptables, validation et contrôle.</p>
                        <div class="btn-group w-100">
                            <a href="saisie_ecriture.php" class="btn btn-outline-primary btn-sm">Nouvelle écriture</a>
                            <a href="journal_comptable.php" class="btn btn-outline-primary btn-sm">Livre journal</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card module-card h-100 border-left-success">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="fas fa-chart-line me-2"></i>États Financiers</h5>
                        <p class="card-text">Génération des états financiers réglementaires.</p>
                        <div class="btn-group w-100">
                            <a href="balance_comptable.php" class="btn btn-outline-success btn-sm">Balance</a>
                            <a href="compte_resultat.php" class="btn btn-outline-success btn-sm">Compte résultat</a>
                            <a href="bilan_comptable.php" class="btn btn-outline-success btn-sm">Bilan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card module-card h-100 border-left-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="fas fa-calculator me-2"></i>Grand Livre</h5>
                        <p class="card-text">Consultation détaillée par compte et analytique.</p>
                        <div class="btn-group w-100">
                            <a href="grand_livre.php" class="btn btn-outline-warning btn-sm">Grand livre général</a>
                            <a href="grand_livre_analytique.php" class="btn btn-outline-warning btn-sm">Grand livre analytique</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card module-card h-100 border-left-info">
                    <div class="card-body">
                        <h5 class="card-title text-info"><i class="fas fa-cogs me-2"></i>Travaux de Clôture</h5>
                        <p class="card-text">Opérations de fin d'exercice et régularisations.</p>
                        <div class="btn-group w-100">
                            <a href="travaux_closure.php" class="btn btn-outline-info btn-sm">Clôture</a>
                            <a href="amortissements.php" class="btn btn-outline-info btn-sm">Amortissements</a>
                            <a href="provisions.php" class="btn btn-outline-info btn-sm">Provisions</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières opérations -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Dernières Opérations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($dernieres_operations) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Journal</th>
                                            <th>Description</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dernieres_operations as $op): ?>
                                        <tr>
                                            <td><?php echo $op['date_operation']; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $op['journal_code']; ?></span></td>
                                            <td><?php echo htmlspecialchars($op['description']); ?></td>
                                            <td class="text-end fw-bold"><?php echo number_format($op['montant'], 2, ',', ' '); ?> FCFA</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Aucune opération récente</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
