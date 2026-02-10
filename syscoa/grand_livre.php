<?php
// grand_livre.php - Version corrigée
session_start();
require_once 'config/database.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier les permissions
$user_role = $_SESSION['role'] ?? '';
$allowed_roles = ['admin', 'comptable'];
if (!in_array($user_role, $allowed_roles)) {
    die('<div class="alert alert-danger m-4">
            <i class="bi bi-shield-exclamation me-2"></i>
            Accès refusé. Seuls les administrateurs et comptables peuvent accéder au grand livre.
         </div>');
}

// Fonction de formatage
if (!function_exists('format_montant')) {
    function format_montant($montant, $devise = 'FCFA') {
        if ($montant === null || $montant === '') {
            return '0,00 ' . $devise;
        }
        return number_format(floatval($montant), 2, ',', ' ') . ' ' . $devise;
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd/m/Y') {
        if (empty($date) || $date == '0000-00-00') {
            return '';
        }
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    }
}

$exercice = isset($_GET['exercice']) ? intval($_GET['exercice']) : date('Y');
$compte = isset($_GET['compte']) ? trim($_GET['compte']) : '';
$date_debut = isset($_GET['date_debut']) ? trim($_GET['date_debut']) : '';
$date_fin = isset($_GET['date_fin']) ? trim($_GET['date_fin']) : '';

// Récupérer les comptes pour le sélecteur
try {
    $sql_comptes = "SELECT numero_compte, nom_compte 
                    FROM comptes_ohada 
                    WHERE LENGTH(numero_compte) >= 3 
                    ORDER BY numero_compte";
    $comptes = $pdo->query($sql_comptes)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $comptes = [];
}

// Construire la requête
$where = ["1=1"];
$params = [];

if (!empty($compte)) {
    $where[] = "e.compte_num LIKE :compte";
    $params[':compte'] = $compte . '%';
}

if (!empty($date_debut)) {
    $where[] = "e.date_ecriture >= :date_debut";
    $params[':date_debut'] = $date_debut;
}

if (!empty($date_fin)) {
    $where[] = "e.date_ecriture <= :date_fin";
    $params[':date_fin'] = $date_fin;
}

$where[] = "e.id_exercice IN (SELECT id_exercice FROM exercices_comptables WHERE annee = :exercice)";
$params[':exercice'] = $exercice;

$where_clause = implode(' AND ', $where);

// Récupérer les mouvements
try {
    $sql_mouvements = "SELECT 
                        e.date_ecriture,
                        e.num_piece,
                        e.libelle,
                        e.debit,
                        e.credit,
                        e.ref_lettrage,
                        c.numero_compte,
                        c.nom_compte
                       FROM ecritures e
                       LEFT JOIN comptes_ohada c ON e.compte_num = c.numero_compte
                       WHERE $where_clause
                       ORDER BY e.date_ecriture, e.ecriture_id";
    
    $stmt = $pdo->prepare($sql_mouvements);
    $stmt->execute($params);
    $mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $mouvements = [];
    $error_message = "Erreur: " . $e->getMessage();
}

// Calculer les totaux
$total_debit = 0;
$total_credit = 0;
$solde = 0;

foreach ($mouvements as $mvt) {
    $total_debit += floatval($mvt['debit']);
    $total_credit += floatval($mvt['credit']);
    $solde += floatval($mvt['debit']) - floatval($mvt['credit']);
}

// Récupérer le solde initial
$solde_initial = 0;
if (!empty($compte)) {
    try {
        $sql_solde_initial = "SELECT 
                              (SELECT COALESCE(SUM(solde), 0) FROM soldes_comptes 
                               WHERE numero_compte = :compte 
                               AND exercice_id IN (SELECT id_exercice FROM exercices_comptables WHERE annee = :exercice)) as solde";
        
        $stmt_solde = $pdo->prepare($sql_solde_initial);
        $stmt_solde->execute([':compte' => $compte, ':exercice' => $exercice]);
        $result = $stmt_solde->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $solde_initial = floatval($result['solde']);
        }
    } catch (Exception $e) {
        $solde_initial = 0;
    }
}

$solde_final = $solde_initial + $solde;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Livre - SYSCO OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, #1a365d, #2b6cb0);
            color: white;
            padding: 2rem;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
        }
        
        .data-table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .data-table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        
        .lettre-oui {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }
        
        .lettre-non {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }
    </style>
</head>
<body>
    <!-- En-tête Bootstrap -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">
                        <i class="bi bi-book me-2"></i>
                        Grand Livre Comptable
                    </h1>
                    <p class="mb-0 opacity-75">Consultation détaillée des mouvements par compte</p>
                </div>
                <div class="text-end">
                    <div class="badge bg-light text-dark mb-2">Exercice : <?= $exercice ?></div>
                    <div class="small"><?= htmlspecialchars($_SESSION['nom_complet'] ?? 'Utilisateur') ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Compte</label>
                        <select name="compte" class="form-select">
                            <option value="">Tous les comptes</option>
                            <?php foreach ($comptes as $c): ?>
                                <option value="<?= $c['numero_compte'] ?>" <?= $c['numero_compte'] == $compte ? 'selected' : '' ?>>
                                    <?= $c['numero_compte'] ?> - <?= htmlspecialchars($c['nom_compte']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Date début</label>
                        <input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Date fin</label>
                        <input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Exercice</label>
                        <select name="exercice" class="form-select">
                            <?php for($i = date('Y') - 5; $i <= date('Y') + 1; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == $exercice ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i> Rechercher
                        </button>
                        <a href="grand_livre.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Informations du compte -->
        <?php if (!empty($compte)): ?>
            <?php 
            try {
                $sql_compte_info = "SELECT * FROM comptes_ohada WHERE numero_compte = :compte LIMIT 1";
                $stmt_info = $pdo->prepare($sql_compte_info);
                $stmt_info->execute([':compte' => $compte]);
                $compte_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $compte_info = null;
            }
            ?>
            
            <div class="alert alert-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-file-text me-2"></i>
                            Compte : <strong><?= $compte ?></strong>
                            <?php if ($compte_info): ?>
                                - <?= htmlspecialchars($compte_info['nom_compte']) ?>
                            <?php endif; ?>
                        </h5>
                        <?php if ($compte_info): ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <small><strong>Nature :</strong> <?= $compte_info['nature_compte'] ?? 'Non défini' ?></small>
                                </div>
                                <div class="col-md-4">
                                    <small><strong>Classe :</strong> Classe <?= substr($compte, 0, 1) ?></small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="badge bg-primary fs-6"><?= format_montant($solde_final) ?></div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Cartes de résumé -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card border-start border-primary border-4">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Solde Initial</h6>
                        <h4 class="card-title"><?= format_montant($solde_initial) ?></h4>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card border-start border-success border-4">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Total Débit</h6>
                        <h4 class="card-title text-success"><?= format_montant($total_debit) ?></h4>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card border-start border-danger border-4">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Total Crédit</h6>
                        <h4 class="card-title text-danger"><?= format_montant($total_credit) ?></h4>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card border-start border-warning border-4">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Solde Final</h6>
                        <h4 class="card-title text-warning"><?= format_montant($solde_final) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableau du grand livre -->
        <div class="card data-table mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="120">Date</th>
                                <th width="120">Pièce</th>
                                <th width="150">Compte</th>
                                <th>Libellé</th>
                                <th width="120" class="text-end">Débit</th>
                                <th width="120" class="text-end">Crédit</th>
                                <th width="150" class="text-end">Solde cumulé</th>
                                <th width="100">Lettrage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mouvements)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                        <p class="text-muted">Aucun mouvement trouvé</p>
                                        <small>Modifiez les critères de recherche</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <!-- Solde initial -->
                                <tr class="table-primary">
                                    <td colspan="4" class="fw-bold">
                                        Solde initial
                                    </td>
                                    <td colspan="2"></td>
                                    <td class="text-end fw-bold">
                                        <?= format_montant($solde_initial) ?>
                                    </td>
                                    <td></td>
                                </tr>
                                
                                <?php 
                                $solde_cumule = $solde_initial;
                                foreach ($mouvements as $mvt):
                                    $montant_debit = floatval($mvt['debit']);
                                    $montant_credit = floatval($mvt['credit']);
                                    $solde_cumule += $montant_debit - $montant_credit;
                                ?>
                                    <tr>
                                        <td><?= format_date($mvt['date_ecriture']) ?></td>
                                        <td>
                                            <code><?= htmlspecialchars($mvt['num_piece']) ?></code>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($mvt['numero_compte'] ?? $compte) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($mvt['nom_compte'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($mvt['libelle']) ?></td>
                                        <td class="text-end <?= $montant_debit > 0 ? 'text-success fw-bold' : '' ?>">
                                            <?= $montant_debit > 0 ? format_montant($montant_debit) : '' ?>
                                        </td>
                                        <td class="text-end <?= $montant_credit > 0 ? 'text-danger fw-bold' : '' ?>">
                                            <?= $montant_credit > 0 ? format_montant($montant_credit) : '' ?>
                                        </td>
                                        <td class="text-end fw-bold">
                                            <?= format_montant($solde_cumule) ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($mvt['ref_lettrage'])): ?>
                                                <span class="lettre-oui">LETTRÉ</span>
                                            <?php else: ?>
                                                <span class="lettre-non">NON LETTRÉ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <!-- Solde final -->
                                <tr class="table-light">
                                    <td colspan="4" class="fw-bold">
                                        Solde final
                                    </td>
                                    <td class="text-end fw-bold text-success"><?= format_montant($total_debit) ?></td>
                                    <td class="text-end fw-bold text-danger"><?= format_montant($total_credit) ?></td>
                                    <td class="text-end fw-bold text-primary"><?= format_montant($solde_final) ?></td>
                                    <td></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Options d'export -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Imprimer
                    </button>
                    <button class="btn btn-primary" onclick="exportToExcel()">
                        <i class="bi bi-file-earmark-excel me-1"></i> Exporter Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToExcel() {
            let csv = [];
            
            // En-têtes
            csv.push(['Date', 'Pièce', 'Compte', 'Libellé', 'Débit', 'Crédit', 'Solde cumulé', 'Lettrage']);
            
            // Données
            <?php foreach ($mouvements as $mvt): ?>
                csv.push([
                    '<?= format_date($mvt['date_ecriture']) ?>',
                    '<?= $mvt['num_piece'] ?>',
                    '<?= $mvt['numero_compte'] ?? $compte ?>',
                    '<?= addslashes($mvt['libelle']) ?>',
                    '<?= $mvt['debit'] ?>',
                    '<?= $mvt['credit'] ?>',
                    '',
                    '<?= !empty($mvt['ref_lettrage']) ? 'LETTRÉ' : 'NON LETTRÉ' ?>'
                ]);
            <?php endforeach; ?>
            
            const csvContent = csv.map(row => row.join(';')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'grand_livre_<?= $compte ?: 'tous' ?>_<?= $exercice ?>.csv';
            link.click();
            URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
