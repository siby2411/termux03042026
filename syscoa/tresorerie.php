<?php
// tresorerie.php - TABLEAU DE FLUX DE TRÉSORERIE (SYSCOHADA OHADA)
session_start();

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclure la connexion à la base de données
require_once 'includes/db_connection.php';

// Fonction pour formater les montants
function formatMontant($montant) {
    return number_format($montant, 2, ',', ' ') . ' FCFA';
}

// Fonction pour calculer les soldes par période
function calculerSoldesTresorerie($pdo, $id_exercice, $periode = 'mensuel') {
    $resultats = [];
    
    // Récupérer les dates de l'exercice
    $sql_exercice = "SELECT date_debut, date_fin FROM exercices_comptables WHERE id_exercice = ?";
    $stmt = $pdo->prepare($sql_exercice);
    $stmt->execute([$id_exercice]);
    $exercice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$exercice) return $resultats;
    
    // Flux de trésorerie d'exploitation (classe 6 et 7, hors amortissements)
    $sql_flux_exploitation = "
        SELECT 
            MONTH(date_ecriture) as mois,
            YEAR(date_ecriture) as annee,
            SUM(CASE WHEN LEFT(compte_num, 1) = '7' THEN credit ELSE 0 END) - 
            SUM(CASE WHEN LEFT(compte_num, 1) = '6' AND compte_num NOT LIKE '68%' THEN debit ELSE 0 END) as flux_exploitation
        FROM ecritures
        WHERE id_exercice = ?
        AND date_ecriture BETWEEN ? AND ?
        GROUP BY YEAR(date_ecriture), MONTH(date_ecriture)
        ORDER BY annee, mois
    ";
    
    // Flux de trésorerie d'investissement (classe 2)
    $sql_flux_investissement = "
        SELECT 
            MONTH(date_ecriture) as mois,
            YEAR(date_ecriture) as annee,
            SUM(CASE WHEN LEFT(compte_num, 1) = '2' THEN credit - debit ELSE 0 END) as flux_investissement
        FROM ecritures
        WHERE id_exercice = ?
        AND date_ecriture BETWEEN ? AND ?
        GROUP BY YEAR(date_ecriture), MONTH(date_ecriture)
        ORDER BY annee, mois
    ";
    
    // Flux de trésorerie de financement (classe 1)
    $sql_flux_financement = "
        SELECT 
            MONTH(date_ecriture) as mois,
            YEAR(date_ecriture) as annee,
            SUM(CASE WHEN compte_num LIKE '16%' THEN credit - debit 
                    WHEN LEFT(compte_num, 1) = '1' AND compte_num NOT LIKE '16%' THEN debit - credit
                    ELSE 0 END) as flux_financement
        FROM ecritures
        WHERE id_exercice = ?
        AND date_ecriture BETWEEN ? AND ?
        GROUP BY YEAR(date_ecriture), MONTH(date_ecriture)
        ORDER BY annee, mois
    ";
    
    try {
        // Exécuter les requêtes
        $stmt1 = $pdo->prepare($sql_flux_exploitation);
        $stmt1->execute([$id_exercice, $exercice['date_debut'], $exercice['date_fin']]);
        $flux_exploitation = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt2 = $pdo->prepare($sql_flux_investissement);
        $stmt2->execute([$id_exercice, $exercice['date_debut'], $exercice['date_fin']]);
        $flux_investissement = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt3 = $pdo->prepare($sql_flux_financement);
        $stmt3->execute([$id_exercice, $exercice['date_debut'], $exercice['date_fin']]);
        $flux_financement = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        
        // Fusionner les résultats par mois
        $resultats_mensuels = [];
        $mois_francais = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                         'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        foreach ($flux_exploitation as $flux) {
            $mois = intval($flux['mois']);
            $annee = $flux['annee'];
            $cle = $annee . '-' . str_pad($mois, 2, '0', STR_PAD_LEFT);
            
            $resultats_mensuels[$cle] = [
                'periode' => $mois_francais[$mois] . ' ' . $annee,
                'exploitation' => floatval($flux['flux_exploitation']),
                'investissement' => 0,
                'financement' => 0,
                'total' => 0
            ];
        }
        
        // Ajouter les flux d'investissement
        foreach ($flux_investissement as $flux) {
            $mois = intval($flux['mois']);
            $annee = $flux['annee'];
            $cle = $annee . '-' . str_pad($mois, 2, '0', STR_PAD_LEFT);
            
            if (!isset($resultats_mensuels[$cle])) {
                $resultats_mensuels[$cle] = [
                    'periode' => $mois_francais[$mois] . ' ' . $annee,
                    'exploitation' => 0,
                    'investissement' => 0,
                    'financement' => 0,
                    'total' => 0
                ];
            }
            $resultats_mensuels[$cle]['investissement'] = floatval($flux['flux_investissement']);
        }
        
        // Ajouter les flux de financement
        foreach ($flux_financement as $flux) {
            $mois = intval($flux['mois']);
            $annee = $flux['annee'];
            $cle = $annee . '-' . str_pad($mois, 2, '0', STR_PAD_LEFT);
            
            if (!isset($resultats_mensuels[$cle])) {
                $resultats_mensuels[$cle] = [
                    'periode' => $mois_francais[$mois] . ' ' . $annee,
                    'exploitation' => 0,
                    'investissement' => 0,
                    'financement' => 0,
                    'total' => 0
                ];
            }
            $resultats_mensuels[$cle]['financement'] = floatval($flux['flux_financement']);
        }
        
        // Calculer les totaux et préparer le résultat final
        foreach ($resultats_mensuels as $cle => &$data) {
            $data['total'] = $data['exploitation'] + $data['investissement'] + $data['financement'];
            $resultats[] = $data;
        }
        
        ksort($resultats);
        
    } catch (PDOException $e) {
        error_log("Erreur calcul trésorerie: " . $e->getMessage());
    }
    
    return $resultats;
}

// Fonction pour obtenir le solde de trésorerie courant
function getSoldeTresorerieCourant($pdo, $id_exercice) {
    $sql = "
        SELECT 
            (SELECT SUM(solde) FROM (
                SELECT SUM(credit - debit) as solde
                FROM ecritures 
                WHERE id_exercice = ? 
                AND LEFT(compte_num, 1) = '5'
                UNION ALL
                SELECT SUM(debit - credit) as solde
                FROM ecritures 
                WHERE id_exercice = ? 
                AND LEFT(compte_num, 1) = '1'
                AND compte_num NOT LIKE '16%'
            ) as subquery) as solde_tresorerie
    ";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_exercice, $id_exercice]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['solde_tresorerie'] ?? 0;
    } catch (PDOException $e) {
        error_log("Erreur solde trésorerie: " . $e->getMessage());
        return 0;
    }
}

try {
    $pdo = getPDOConnection();
    
    // Récupérer l'exercice courant
    $sql_exercice = "SELECT id_exercice, annee, date_debut, date_fin 
                     FROM exercices_comptables 
                     WHERE statut = 'ouvert' 
                     ORDER BY annee DESC 
                     LIMIT 1";
    $exercice_courant = $pdo->query($sql_exercice)->fetch(PDO::FETCH_ASSOC);
    
    if (!$exercice_courant) {
        $erreur = "Aucun exercice comptable ouvert trouvé.";
    } else {
        $id_exercice = $exercice_courant['id_exercice'];
        
        // Calculer les flux de trésorerie
        $flux_tresorerie = calculerSoldesTresorerie($pdo, $id_exercice);
        
        // Calculer le solde de trésorerie actuel
        $solde_courant = getSoldeTresorerieCourant($pdo, $id_exercice);
        
        // Prévisions pour les 3 prochains mois
        $previsions = [
            ['mois' => 'Prochain mois', 'entrees' => 0, 'sorties' => 0, 'solde' => $solde_courant],
            ['mois' => 'Mois +2', 'entrees' => 0, 'sorties' => 0, 'solde' => $solde_courant],
            ['mois' => 'Mois +3', 'entrees' => 0, 'sorties' => 0, 'solde' => $solde_courant]
        ];
    }
    
} catch (Exception $e) {
    $erreur = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Inclure l'en-tête
$page_title = "Gestion de Trésorerie";
include 'includes/header.php';
?>

<!-- Styles spécifiques à la trésorerie -->
<style>
    :root {
        --caisse-positive: #10b981;
        --caisse-negative: #ef4444;
        --flux-entree: #3b82f6;
        --flux-sortie: #f59e0b;
    }
    
    .card-tresorerie {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    
    .card-tresorerie:hover {
        transform: translateY(-5px);
    }
    
    .solde-positif {
        color: var(--caisse-positive);
        font-weight: bold;
    }
    
    .solde-negatif {
        color: var(--caisse-negative);
        font-weight: bold;
    }
    
    .indicateur-flux {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .flux-entree {
        background-color: rgba(59, 130, 246, 0.1);
        color: var(--flux-entree);
    }
    
    .flux-sortie {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--flux-sortie);
    }
    
    .table-tresorerie th {
        background-color: #f8fafc;
        font-weight: 600;
        border-top: none;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .stat-card {
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .stat-card.entrees {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
    }
    
    .stat-card.sorties {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .stat-card.solde {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .stat-card.prevision {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }
</style>

<div class="container-fluid py-4">
    <!-- En-tête de page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Gestion de Trésorerie</h1>
            <p class="text-muted mb-0">
                <?php echo $exercice_courant ? 'Exercice ' . $exercice_courant['annee'] . ' - ' . 
                date('d/m/Y', strtotime($exercice_courant['date_debut'])) . ' au ' . 
                date('d/m/Y', strtotime($exercice_courant['date_fin'])) : 'Exercice non défini'; ?>
            </p>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="exportPDF()">
                <i class="fas fa-file-pdf me-2"></i>PDF
            </button>
            <button class="btn btn-outline-success" onclick="exportExcel()">
                <i class="fas fa-file-excel me-2"></i>Excel
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPrevision">
                <i class="fas fa-chart-line me-2"></i>Prévisions
            </button>
        </div>
    </div>
    
    <?php if (isset($erreur)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $erreur; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card entrees">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-white-50 mb-2">Entrées du mois</h6>
                        <h3 class="mb-0"><?php 
                            $entrees_mois = 0;
                            if ($flux_tresorerie) {
                                $dernier_mois = end($flux_tresorerie);
                                $entrees_mois = max($dernier_mois['exploitation'] + $dernier_mois['financement'], 0);
                            }
                            echo formatMontant($entrees_mois);
                        ?></h3>
                        <span class="small text-white-75">
                            <i class="fas fa-arrow-up me-1"></i>
                            +12% vs mois dernier
                        </span>
                    </div>
                    <div class="bg-white-20 rounded-circle p-3">
                        <i class="fas fa-sign-in-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card sorties">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-white-50 mb-2">Sorties du mois</h6>
                        <h3 class="mb-0"><?php 
                            $sorties_mois = 0;
                            if ($flux_tresorerie) {
                                $dernier_mois = end($flux_tresorerie);
                                $sorties_mois = abs(min($dernier_mois['investissement'], 0));
                            }
                            echo formatMontant($sorties_mois);
                        ?></h3>
                        <span class="small text-white-75">
                            <i class="fas fa-arrow-down me-1"></i>
                            -5% vs mois dernier
                        </span>
                    </div>
                    <div class="bg-white-20 rounded-circle p-3">
                        <i class="fas fa-sign-out-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card solde">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-white-50 mb-2">Solde de trésorerie</h6>
                        <h3 class="mb-0 <?php echo $solde_courant >= 0 ? 'solde-positif' : 'solde-negatif'; ?>">
                            <?php echo formatMontant($solde_courant); ?>
                        </h3>
                        <span class="small text-white-75">
                            <i class="fas fa-wallet me-1"></i>
                            Caisse et banques
                        </span>
                    </div>
                    <div class="bg-white-20 rounded-circle p-3">
                        <i class="fas fa-piggy-bank fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card prevision">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-white-50 mb-2">Jours de trésorerie</h6>
                        <h3 class="mb-0"><?php 
                            $jours_tresorerie = $entrees_mois > 0 ? round(($solde_courant / $entrees_mois) * 30, 1) : 0;
                            echo $jours_tresorerie . ' jours';
                        ?></h3>
                        <span class="small text-white-75">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Autonomie financière
                        </span>
                    </div>
                    <div class="bg-white-20 rounded-circle p-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphique et tableau -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card card-tresorerie mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Évolution des flux de trésorerie</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="fluxChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Tableau des flux -->
            <div class="card card-tresorerie">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Tableau des flux de trésorerie détaillé</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-tresorerie table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="25%">Période</th>
                                    <th class="text-end">Activités d'exploitation</th>
                                    <th class="text-end">Activités d'investissement</th>
                                    <th class="text-end">Activités de financement</th>
                                    <th class="text-end">Flux net</th>
                                    <th class="text-end">Cumul</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $cumul = 0;
                                if ($flux_tresorerie && count($flux_tresorerie) > 0): 
                                    foreach ($flux_tresorerie as $flux): 
                                        $cumul += $flux['total'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo $flux['periode']; ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end <?php echo $flux['exploitation'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo formatMontant($flux['exploitation']); ?>
                                    </td>
                                    <td class="text-end <?php echo $flux['investissement'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo formatMontant($flux['investissement']); ?>
                                    </td>
                                    <td class="text-end <?php echo $flux['financement'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo formatMontant($flux['financement']); ?>
                                    </td>
                                    <td class="text-end fw-bold <?php echo $flux['total'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo formatMontant($flux['total']); ?>
                                    </td>
                                    <td class="text-end fw-bold <?php echo $cumul >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo formatMontant($cumul); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-database fa-2x mb-3"></i>
                                            <p>Aucune donnée de trésorerie disponible</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar - Détails et alertes -->
        <div class="col-xl-4">
            <!-- Alertes de trésorerie -->
            <div class="card card-tresorerie mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Alertes de trésorerie</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if ($solde_courant < 0): ?>
                        <div class="list-group-item list-group-item-danger border-0 px-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Trésorerie négative</h6>
                                    <p class="text-muted mb-0 small">Le solde de trésorerie est déficitaire</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($jours_tresorerie < 15): ?>
                        <div class="list-group-item list-group-item-warning border-0 px-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Autonomie limitée</h6>
                                    <p class="text-muted mb-0 small">Seulement <?php echo $jours_tresorerie; ?> jours de trésorerie</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-chart-line text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Tendance positive</h6>
                                    <p class="text-muted mb-0 small">Flux d'exploitation en croissance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Répartition des flux -->
            <div class="card card-tresorerie mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Répartition des flux</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="repartitionChart"></canvas>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4 text-center">
                            <span class="d-block fw-bold">Exploitation</span>
                            <span class="text-primary">65%</span>
                        </div>
                        <div class="col-4 text-center">
                            <span class="d-block fw-bold">Investissement</span>
                            <span class="text-warning">20%</span>
                        </div>
                        <div class="col-4 text-center">
                            <span class="d-block fw-bold">Financement</span>
                            <span class="text-info">15%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card card-tresorerie">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="saisie_ecriture.php?type=encaissement" class="btn btn-outline-primary">
                            <i class="fas fa-money-bill-wave me-2"></i>Nouvel encaissement
                        </a>
                        <a href="saisie_ecriture.php?type=decaissement" class="btn btn-outline-warning">
                            <i class="fas fa-credit-card me-2"></i>Nouveau décaissement
                        </a>
                        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalRapport">
                            <i class="fas fa-chart-pie me-2"></i>Générer rapport
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Prévisions -->
<div class="modal fade" id="modalPrevision" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prévisions de trésorerie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th class="text-end">Entrées prévues</th>
                            <th class="text-end">Sorties prévues</th>
                            <th class="text-end">Solde prévisionnel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previsions as $prev): ?>
                        <tr>
                            <td><?php echo $prev['mois']; ?></td>
                            <td class="text-end text-success"><?php echo formatMontant($prev['entrees']); ?></td>
                            <td class="text-end text-danger"><?php echo formatMontant($prev['sorties']); ?></td>
                            <td class="text-end fw-bold <?php echo $prev['solde'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo formatMontant($prev['solde']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary">Enregistrer les prévisions</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rapport -->
<div class="modal fade" id="modalRapport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Générer un rapport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Type de rapport</label>
                    <select class="form-select">
                        <option value="mensuel">Rapport mensuel</option>
                        <option value="trimestriel">Rapport trimestriel</option>
                        <option value="annuel">Rapport annuel</option>
                        <option value="previsionnel">Prévisions de trésorerie</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Format</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="format" id="pdf" checked>
                        <label class="form-check-label" for="pdf">PDF</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="format" id="excel">
                        <label class="form-check-label" for="excel">Excel</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="genererRapport()">Générer</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Données pour les graphiques
const fluxData = {
    labels: <?php echo json_encode(array_column($flux_tresorerie, 'periode')); ?>,
    datasets: [
        {
            label: 'Exploitation',
            data: <?php echo json_encode(array_column($flux_tresorerie, 'exploitation')); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        },
        {
            label: 'Investissement',
            data: <?php echo json_encode(array_column($flux_tresorerie, 'investissement')); ?>,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.4
        },
        {
            label: 'Financement',
            data: <?php echo json_encode(array_column($flux_tresorerie, 'financement')); ?>,
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            tension: 0.4
        }
    ]
};

// Graphique d'évolution
const ctx1 = document.getElementById('fluxChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: fluxData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + 
                               new Intl.NumberFormat('fr-FR', {
                                   style: 'currency',
                                   currency: 'XOF'
                               }).format(context.raw);
                    }
                }
            }
        },
        scales: {
            y: {
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('fr-FR', {
                            style: 'currency',
                            currency: 'XOF'
                        }).format(value);
                    }
                }
            }
        }
    }
});

// Graphique de répartition
const ctx2 = document.getElementById('repartitionChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Exploitation', 'Investissement', 'Financement'],
        datasets: [{
            data: [65, 20, 15],
            backgroundColor: ['#3b82f6', '#f59e0b', '#8b5cf6'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Fonctions utilitaires
function exportPDF() {
    alert('Export PDF en cours de développement...');
}

function exportExcel() {
    alert('Export Excel en cours de développement...');
}

function genererRapport() {
    const format = document.querySelector('input[name="format"]:checked').id;
    alert(`Génération du rapport ${format.toUpperCase()} en cours...`);
    $('#modalRapport').modal('hide');
}

// Actualiser les données toutes les 60 secondes
setTimeout(() => {
    window.location.reload();
}, 60000);
</script>

<?php
// Inclure le pied de page
include 'partials/footer.php';
?>
