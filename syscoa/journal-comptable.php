<?php
// journal_comptable.php
// LIVRE JOURNAL COMPTABLE - Conforme OHADA
session_start();

// Connexion sécurisée
require_once 'config/database.php';
$pdo = getPDOConnection();

// Récupération des écritures avec jointures correctes
$sql = "
    SELECT 
        oc.date_operation,
        oc.journal_code,
        j.intitule as journal_libelle,
        oc.piece_ref,
        oc.description,
        oc.compte_debit,
        cd.nom_compte as compte_debit_libelle,
        oc.compte_credit,
        cc.nom_compte as compte_credit_libelle,
        oc.montant,
        t.nom_raison_sociale as tiers,
        ex.annee as exercice
    FROM operations_comptables oc
    LEFT JOIN journaux j ON oc.journal_code = j.journal_code
    LEFT JOIN comptes_ohada cd ON oc.compte_debit = cd.numero_compte
    LEFT JOIN comptes_ohada cc ON oc.compte_credit = cc.numero_compte
    LEFT JOIN tiers t ON oc.code_tiers_fk = t.code_tiers
    LEFT JOIN exercices_comptables ex ON oc.id_exercice_fk = ex.id_exercice
    ORDER BY oc.date_operation DESC, oc.id_operation DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$operations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des totaux
$total_debit = $pdo->query("SELECT SUM(montant) as total FROM operations_comptables")->fetch(PDO::FETCH_ASSOC)['total'];
$total_credit = $total_debit; // Système équilibré
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre Journal - Système OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .journal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }
        .table-journal {
            font-size: 0.9rem;
        }
        .badge-journal {
            font-size: 0.7rem;
        }
        .montant {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card journal-header shadow">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="h3 mb-1">
                                    <i class="fas fa-book me-2"></i>
                                    Livre Journal Comptable
                                </h1>
                                <p class="mb-0">Système conforme OHADA - SYSCOHADA UEMOA</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group">
                                    <button class="btn btn-light btn-sm" onclick="window.print()">
                                        <i class="fas fa-print me-1"></i> Imprimer
                                    </button>
                                    <button class="btn btn-light btn-sm" onclick="exportToExcel()">
                                        <i class="fas fa-file-excel me-1"></i> Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center p-3">
                        <h5 class="mb-1"><?php echo number_format($total_debit, 2, ',', ' '); ?> FCFA</h5>
                        <small>Total Mouvements</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center p-3">
                        <h5 class="mb-1"><?php echo count($operations); ?></h5>
                        <small>Nombre d'écritures</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center p-3">
                        <h5 class="mb-1"><?php echo date('d/m/Y'); ?></h5>
                        <small>Date d'édition</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center p-3">
                        <h5 class="mb-1">Équilibré</h5>
                        <small>État Comptable</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau du journal -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Détail des Écritures Comptables
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-journal mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="100">Date</th>
                                <th width="80">Journal</th>
                                <th width="100">Pièce</th>
                                <th>Description</th>
                                <th width="120">Compte Débit</th>
                                <th width="120">Compte Crédit</th>
                                <th width="120" class="text-end">Montant</th>
                                <th width="150">Tiers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($operations) > 0): ?>
                                <?php foreach ($operations as $op): ?>
                                <tr>
                                    <td>
                                        <small><?php echo htmlspecialchars($op['date_operation']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary badge-journal">
                                            <?php echo htmlspecialchars($op['journal_code']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($op['piece_ref'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($op['description']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($op['journal_libelle']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary"><?php echo htmlspecialchars($op['compte_debit']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($op['compte_debit_libelle']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success"><?php echo htmlspecialchars($op['compte_credit']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($op['compte_credit_libelle']); ?></small>
                                    </td>
                                    <td class="text-end montant text-dark">
                                        <?php echo number_format($op['montant'], 2, ',', ' '); ?>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($op['tiers'] ?? 'N/A'); ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3"></i><br>
                                        Aucune écriture comptable trouvée
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Totaux :</td>
                                <td class="text-end fw-bold">
                                    <?php echo number_format($total_debit, 2, ',', ' '); ?> FCFA
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="row mt-4">
            <div class="col-12 text-center text-muted">
                <small>
                    Document généré le <?php echo date('d/m/Y à H:i'); ?> - 
                    Système Comptable OHADA - SYSCOHADA UEMOA
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToExcel() {
            // Implémentation simplifiée pour l'export Excel
            alert('Fonction d\'export Excel à implémenter');
        }
    </script>
</body>
</html>
