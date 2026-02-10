<?php
// grand_livre.php
// GRAND LIVRE COMPTABLE - Conforme OHADA
session_start();

require_once 'config/database.php';
$pdo = getPDOConnection();

// Récupération des comptes avec leurs mouvements
$sql = "
    SELECT 
        c.numero_compte,
        c.nom_compte,
        cl.numero_classe,
        cl.nom_classe,
        c.nature_compte,
        SUM(CASE WHEN o.compte_debit = c.numero_compte THEN o.montant ELSE 0 END) as total_debit,
        SUM(CASE WHEN o.compte_credit = c.numero_compte THEN o.montant ELSE 0 END) as total_credit,
        (SUM(CASE WHEN o.compte_debit = c.numero_compte THEN o.montant ELSE 0 END) - 
         SUM(CASE WHEN o.compte_credit = c.numero_compte THEN o.montant ELSE 0 END)) as solde
    FROM comptes_ohada c
    LEFT JOIN classes_ohada cl ON c.id_classe_fk = cl.id_classe
    LEFT JOIN operations_comptables o ON c.numero_compte = o.compte_debit OR c.numero_compte = o.compte_credit
    GROUP BY c.numero_compte, c.nom_compte, cl.numero_classe, cl.nom_classe, c.nature_compte
    HAVING total_debit != 0 OR total_credit != 0
    ORDER BY c.numero_compte
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des totaux par classe
$sql_totaux = "
    SELECT 
        cl.numero_classe,
        cl.nom_classe,
        SUM(CASE WHEN o.compte_debit = c.numero_compte THEN o.montant ELSE 0 END) as total_debit_classe,
        SUM(CASE WHEN o.compte_credit = c.numero_compte THEN o.montant ELSE 0 END) as total_credit_classe
    FROM classes_ohada cl
    LEFT JOIN comptes_ohada c ON cl.id_classe = c.id_classe_fk
    LEFT JOIN operations_comptables o ON c.numero_compte = o.compte_debit OR c.numero_compte = o.compte_credit
    GROUP BY cl.numero_classe, cl.nom_classe
    HAVING total_debit_classe != 0 OR total_credit_classe != 0
    ORDER BY cl.numero_classe
";

$totaux_classes = $pdo->query($sql_totaux)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Livre - Système OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .solde-positif { color: #198754; font-weight: bold; }
        .solde-negatif { color: #dc3545; font-weight: bold; }
        .classe-header { 
            background-color: #e9ecef; 
            font-weight: bold; 
            border-left: 4px solid #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white shadow">
                    <div class="card-body text-center py-4">
                        <h1 class="h2 mb-3">
                            <i class="fas fa-book-open me-2"></i>
                            Grand Livre Comptable
                        </h1>
                        <p class="lead mb-0">Balance des comptes - Conforme OHADA SYSCOHADA</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totaux par classe -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Synthèse par Classe OHADA
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Classe</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Total Débit</th>
                                        <th class="text-end">Total Crédit</th>
                                        <th class="text-end">Solde</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($totaux_classes as $classe): 
                                        $solde_classe = $classe['total_debit_classe'] - $classe['total_credit_classe'];
                                    ?>
                                    <tr>
                                        <td class="fw-bold">Classe <?php echo $classe['numero_classe']; ?></td>
                                        <td><?php echo htmlspecialchars($classe['nom_classe']); ?></td>
                                        <td class="text-end"><?php echo number_format($classe['total_debit_classe'], 2, ',', ' '); ?></td>
                                        <td class="text-end"><?php echo number_format($classe['total_credit_classe'], 2, ',', ' '); ?></td>
                                        <td class="text-end <?php echo $solde_classe >= 0 ? 'solde-positif' : 'solde-negatif'; ?>">
                                            <?php echo number_format($solde_classe, 2, ',', ' '); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détail des comptes -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Détail des Comptes
                </h5>
                <span class="badge bg-light text-dark"><?php echo count($comptes); ?> comptes mouvementés</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="100">N° Compte</th>
                                <th>Libellé du Compte</th>
                                <th width="80">Classe</th>
                                <th width="100" class="text-end">Total Débit</th>
                                <th width="100" class="text-end">Total Crédit</th>
                                <th width="120" class="text-end">Solde</th>
                                <th width="100">Nature</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_classe = null;
                            foreach ($comptes as $compte): 
                                if ($current_classe != $compte['numero_classe']):
                                    $current_classe = $compte['numero_classe'];
                            ?>
                            <tr class="classe-header">
                                <td colspan="7">
                                    <i class="fas fa-folder me-2"></i>
                                    CLASSE <?php echo $compte['numero_classe']; ?> - 
                                    <?php echo htmlspecialchars($compte['nom_classe']); ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="fw-bold text-primary"><?php echo $compte['numero_compte']; ?></td>
                                <td><?php echo htmlspecialchars($compte['nom_compte']); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $compte['numero_classe']; ?></span>
                                </td>
                                <td class="text-end"><?php echo number_format($compte['total_debit'], 2, ',', ' '); ?></td>
                                <td class="text-end"><?php echo number_format($compte['total_credit'], 2, ',', ' '); ?></td>
                                <td class="text-end <?php echo $compte['solde'] >= 0 ? 'solde-positif' : 'solde-negatif'; ?>">
                                    <?php echo number_format($compte['solde'], 2, ',', ' '); ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($compte['nature_compte']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
