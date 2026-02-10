<?php
// balance_comptable.php
// BALANCE COMPTABLE - Conforme OHADA SYSCOHADA
session_start();
require_once 'config/database.php';

try {
    $pdo = getPDOConnection();
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Erreur de connexion: " . $e->getMessage() . "</div>");
}

// Période pour la balance
$sql_exercice = "SELECT id_exercice, annee, date_debut, date_fin 
                 FROM exercices_comptables 
                 WHERE statut = 'ouvert' 
                 ORDER BY annee DESC 
                 LIMIT 1";
$exercice_courant = $pdo->query($sql_exercice)->fetch(PDO::FETCH_ASSOC);

// Calcul de la balance complète
$sql_balance = "
    SELECT 
        c.numero_compte,
        c.nom_compte,
        cl.numero_classe,
        cl.nom_classe,
        c.nature_compte,
        SUM(CASE WHEN o.compte_debit = c.numero_compte THEN o.montant ELSE 0 END) as total_debit,
        SUM(CASE WHEN o.compte_credit = c.numero_compte THEN o.montant ELSE 0 END) as total_credit,
        (SUM(CASE WHEN o.compte_debit = c.numero_compte THEN o.montant ELSE 0 END) - 
         SUM(CASE WHEN o.compte_credit = c.numero_compte THEN o.montant ELSE 0 END)) as solde,
        CASE 
            WHEN (SUM(CASE WHEN o.compte_debit = c.numero_compte THEN o.montant ELSE 0 END) - 
                  SUM(CASE WHEN o.compte_credit = c.numero_compte THEN o.montant ELSE 0 END)) > 0 THEN 'Débit'
            ELSE 'Crédit'
        END as sens_solde
    FROM comptes_ohada c
    JOIN classes_ohada cl ON c.id_classe_fk = cl.id_classe
    LEFT JOIN operations_comptables o ON c.numero_compte = o.compte_debit OR c.numero_compte = o.compte_credit
    " . ($exercice_courant ? "WHERE o.id_exercice_fk = " . intval($exercice_courant['id_exercice']) : "") . "
    GROUP BY c.numero_compte, c.nom_compte, cl.numero_classe, cl.nom_classe, c.nature_compte
    HAVING total_debit != 0 OR total_credit != 0
    ORDER BY c.numero_compte
";

$balance = $pdo->query($sql_balance)->fetchAll(PDO::FETCH_ASSOC);

// Calcul des totaux
$total_debit = array_sum(array_column($balance, 'total_debit'));
$total_credit = array_sum(array_column($balance, 'total_credit'));
$total_solde_debit = array_sum(array_map(function($item) {
    return $item['solde'] > 0 ? $item['solde'] : 0;
}, $balance));
$total_solde_credit = array_sum(array_map(function($item) {
    return $item['solde'] < 0 ? abs($item['solde']) : 0;
}, $balance));

function formatMontant($montant) {
    return number_format($montant, 2, ',', ' ') . ' FCFA';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Comptable - Système OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .balance-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .solde-debit { color: #198754; font-weight: bold; }
        .solde-credit { color: #dc3545; font-weight: bold; }
        .classe-header { background-color: #e9ecef; font-weight: bold; border-left: 4px solid #6c757d; }
        .total-balance { font-size: 1.1rem; font-weight: bold; background-color: #f8f9fa; }
        .montant { font-family: 'Courier New', monospace; font-weight: bold; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card balance-header shadow">
                    <div class="card-body text-center py-4">
                        <h1 class="h2 mb-3"><i class="fas fa-scale-balanced me-2"></i>Balance Comptable</h1>
                        <p class="lead mb-0">
                            <?php if ($exercice_courant): ?>
                                Exercice <?php echo htmlspecialchars($exercice_courant['annee']); ?> - 
                                Du <?php echo date('d/m/Y', strtotime($exercice_courant['date_debut'])); ?> 
                                au <?php echo date('d/m/Y', strtotime($exercice_courant['date_fin'])); ?>
                            <?php else: ?>
                                Tous exercices confondus
                            <?php endif; ?>
                        </p>
                        <small class="opacity-75">Système conforme OHADA SYSCOHADA</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de résumé -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i>
                        <h5><?php echo formatMontant($total_debit); ?></h5>
                        <small>Total Débit</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-file-invoice fa-2x mb-2"></i>
                        <h5><?php echo formatMontant($total_credit); ?></h5>
                        <small>Total Crédit</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-arrow-up fa-2x mb-2"></i>
                        <h5><?php echo formatMontant($total_solde_debit); ?></h5>
                        <small>Solde Débit</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-arrow-down fa-2x mb-2"></i>
                        <h5><?php echo formatMontant($total_solde_credit); ?></h5>
                        <small>Solde Crédit</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau de la balance -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Détail de la Balance</h5>
                <span class="badge bg-light text-dark"><?php echo count($balance); ?> comptes mouvementés</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="100">Compte</th>
                                <th>Libellé</th>
                                <th width="80">Classe</th>
                                <th width="120" class="text-end">Total Débit</th>
                                <th width="120" class="text-end">Total Crédit</th>
                                <th width="120" class="text-end">Solde</th>
                                <th width="100">Sens</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($balance) > 0): ?>
                                <?php 
                                $current_classe = null;
                                foreach ($balance as $compte): 
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
                                    <td><span class="badge bg-secondary"><?php echo $compte['numero_classe']; ?></span></td>
                                    <td class="text-end montant"><?php echo formatMontant($compte['total_debit']); ?></td>
                                    <td class="text-end montant"><?php echo formatMontant($compte['total_credit']); ?></td>
                                    <td class="text-end montant <?php echo $compte['solde'] > 0 ? 'solde-debit' : 'solde-credit'; ?>">
                                        <?php echo formatMontant(abs($compte['solde'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $compte['solde'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $compte['sens_solde']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <div>Aucun mouvement comptable trouvé</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr class="total-balance">
                                <td colspan="3" class="text-end fw-bold">TOTAUX :</td>
                                <td class="text-end fw-bold"><?php echo formatMontant($total_debit); ?></td>
                                <td class="text-end fw-bold"><?php echo formatMontant($total_credit); ?></td>
                                <td class="text-end fw-bold"><?php echo formatMontant($total_solde_debit - $total_solde_credit); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Équilibre de la balance -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card <?php echo abs($total_debit - $total_credit) < 0.01 ? 'bg-success' : 'bg-danger'; ?> text-white text-center">
                    <div class="card-body py-3">
                        <h4 class="mb-0">
                            <?php if (abs($total_debit - $total_credit) < 0.01): ?>
                                <i class="fas fa-check-circle me-2"></i>
                                BALANCE ÉQUILIBRÉE - Débit (<?php echo formatMontant($total_debit); ?>) = Crédit (<?php echo formatMontant($total_credit); ?>)
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                BALANCE DÉSÉQUILIBRÉE - Différence: <?php echo formatMontant(abs($total_debit - $total_credit)); ?>
                            <?php endif; ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6><i class="fas fa-sitemap me-2"></i>Navigation des états financiers</h6>
                                <div class="btn-group">
                                    <a href="journal_comptable.php" class="btn btn-outline-primary btn-sm">Livre Journal</a>
                                    <a href="grand_livre.php" class="btn btn-outline-success btn-sm">Grand Livre</a>
                                    <a href="compte_resultat.php" class="btn btn-outline-warning btn-sm">Compte de Résultat</a>
                                    <a href="bilan_comptable.php" class="btn btn-outline-info btn-sm">Bilan Comptable</a>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-primary btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Imprimer
                                </button>
                                <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToExcel() {
            alert('Fonction d\'export Excel à implémenter');
        }
        
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
