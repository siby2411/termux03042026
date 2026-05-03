<?php
// soldes_gestion.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

$id_exercice = $_SESSION['id_exercice'];

// Calculer les soldes intermédiaires
function calculerSIG($pdo, $id_exercice) {
    $sig = [];
    
    // 1. MARGE COMMERCIALE
    $sql_marge = "SELECT 
        SUM(CASE WHEN c.numero LIKE '70%' THEN e.credit - e.debit ELSE 0 END) as ventes,
        SUM(CASE WHEN c.numero LIKE '60%' THEN e.debit - e.credit ELSE 0 END) as achats
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice";
    
    $stmt = $pdo->prepare($sql_marge);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $marge = $stmt->fetch();
    $sig['marge_commerciale'] = $marge['ventes'] - $marge['achats'];
    
    // 2. PRODUCTION DE L'EXERCICE
    $sql_production = "SELECT 
        SUM(CASE WHEN c.numero LIKE '71%' THEN e.credit - e.debit ELSE 0 END) as production_vendue,
        SUM(CASE WHEN c.numero LIKE '72%' THEN e.credit - e.debit ELSE 0 END) as production_stockee,
        SUM(CASE WHEN c.numero LIKE '73%' THEN e.credit - e.debit ELSE 0 END) as production_immobilisee
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice";
    
    $stmt = $pdo->prepare($sql_production);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $production = $stmt->fetch();
    $sig['production_exercice'] = $production['production_vendue'] + $production['production_stockee'] + $production['production_immobilisee'];
    
    // 3. VALEUR AJOUTÉE
    $sql_va = "SELECT 
        SUM(CASE WHEN c.numero BETWEEN '60' AND '62' THEN e.debit - e.credit ELSE 0 END) as consommation,
        SUM(CASE WHEN c.numero BETWEEN '70' AND '74' THEN e.credit - e.debit ELSE 0 END) as production
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice";
    
    $stmt = $pdo->prepare($sql_va);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $va = $stmt->fetch();
    $sig['valeur_ajoutee'] = $va['production'] - $va['consommation'];
    
    // 4. EXCÉDENT BRUT D'EXPLOITATION (EBE)
    $sql_ebe = "SELECT 
        SUM(CASE WHEN c.numero BETWEEN '62' AND '65' THEN e.debit - e.credit ELSE 0 END) as autres_charges,
        SUM(CASE WHEN c.numero BETWEEN '75' AND '78' THEN e.credit - e.debit ELSE 0 END) as autres_produits
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice";
    
    $stmt = $pdo->prepare($sql_ebe);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $ebe = $stmt->fetch();
    $sig['ebe'] = $sig['valeur_ajoutee'] - $ebe['autres_charges'] + $ebe['autres_produits'];
    
    // 5. RÉSULTAT D'EXPLOITATION
    $sql_dotations = "SELECT 
        SUM(CASE WHEN c.numero LIKE '68%' THEN e.debit - e.credit ELSE 0 END) as dotations
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice";
    
    $stmt = $pdo->prepare($sql_dotations);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $dotations = $stmt->fetch();
    $sig['resultat_exploitation'] = $sig['ebe'] - $dotations['dotations'];
    
    // 6. RÉSULTAT COURANT AVANT IMPÔT
    $sql_financier = "SELECT 
        SUM(CASE WHEN c.numero BETWEEN '66' AND '67' THEN e.debit - e.credit ELSE 0 END) as charges_financieres,
        SUM(CASE WHEN c.numero BETWEEN '76' AND '77' THEN e.credit - e.debit ELSE 0 END) as produits_financiers
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice";
    
    $stmt = $pdo->prepare($sql_financier);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $financier = $stmt->fetch();
    $sig['resultat_courant'] = $sig['resultat_exploitation'] - $financier['charges_financieres'] + $financier['produits_financiers'];
    
    // 7. RÉSULTAT NET
    $sql_exceptionnel = "SELECT 
        SUM(CASE WHEN c.numero LIKE '67%' THEN e.debit - e.credit ELSE 0 END) as charges_exceptionnelles,
        SUM(CASE WHEN c.numero LIKE '77%' THEN e.credit - e.debit ELSE 0 END) as produits_exceptionnels
        FROM ecritures e
        JOIN comptes_ohada c ON e.compte_num = c.numero
        WHERE e.id_exercice = :id_exercice";
    
    $stmt = $pdo->prepare($sql_exceptionnel);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $exceptionnel = $stmt->fetch();
    $sig['resultat_net'] = $sig['resultat_courant'] - $exceptionnel['charges_exceptionnelles'] + $exceptionnel['produits_exceptionnels'];
    
    return $sig;
}

$sig_data = calculerSIG($pdo, $id_exercice);

// Sauvegarder dans la table soldes_gestion
foreach ($sig_data as $type => $montant) {
    $sql_save = "INSERT INTO soldes_gestion (id_exercice, type_solde, libelle, montant, date_calcul)
                 VALUES (:id_exercice, :type, :libelle, :montant, NOW())
                 ON DUPLICATE KEY UPDATE montant = :montant2, date_calcul = NOW()";
    
    $libelles = [
        'marge_commerciale' => 'Marge commerciale',
        'production_exercice' => 'Production de l\'exercice',
        'valeur_ajoutee' => 'Valeur ajoutée',
        'ebe' => 'Excédent brut d\'exploitation',
        'resultat_exploitation' => 'Résultat d\'exploitation',
        'resultat_courant' => 'Résultat courant avant impôt',
        'resultat_net' => 'Résultat net'
    ];
    
    $stmt = $pdo->prepare($sql_save);
    $stmt->execute([
        ':id_exercice' => $id_exercice,
        ':type' => $type,
        ':libelle' => $libelles[$type],
        ':montant' => $montant,
        ':montant2' => $montant
    ]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Soldes Intermédiaires de Gestion</title>
    <style>
        .sig-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .sig-table th, .sig-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: right;
        }
        .sig-table th {
            background: #2c3e50;
            color: white;
            text-align: left;
        }
        .sig-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .sig-table tr:hover {
            background: #e9ecef;
        }
        .positive {
            color: #28a745;
            font-weight: bold;
        }
        .negative {
            color: #dc3545;
            font-weight: bold;
        }
        .sig-header {
            background: linear-gradient(135deg, #2c3e50, #4a6491);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .chart-container {
            height: 400px;
            margin: 40px 0;
        }
    </style>
</head>
<body>
    <div class="sig-container">
        <div class="sig-header">
            <h1><i class="fas fa-chart-line"></i> Soldes Intermédiaires de Gestion</h1>
            <p>Exercice : <?php echo $_SESSION['exercice_nom']; ?></p>
        </div>
        
        <div class="sig-table-container">
            <table class="sig-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>DÉSIGNATION</th>
                        <th>MONTANT (FCFA)</th>
                        <th>% DU CA</th>
                        <th>ANALYSE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $ca_total = $sig_data['marge_commerciale'] + $sig_data['production_exercice'];
                    $i = 1;
                    foreach ($sig_data as $key => $value): 
                        $pourcentage = $ca_total > 0 ? ($value / $ca_total * 100) : 0;
                        $classe = $value >= 0 ? 'positive' : 'negative';
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php 
                            $libelles = [
                                'marge_commerciale' => 'MARGE COMMERCIALE',
                                'production_exercice' => 'PRODUCTION DE L\'EXERCICE',
                                'valeur_ajoutee' => 'VALEUR AJOUTÉE',
                                'ebe' => 'EXCÉDENT BRUT D\'EXPLOITATION (EBE)',
                                'resultat_exploitation' => 'RÉSULTAT D\'EXPLOITATION',
                                'resultat_courant' => 'RÉSULTAT COURANT AVANT IMPÔT',
                                'resultat_net' => 'RÉSULTAT NET'
                            ];
                            echo $libelles[$key];
                        ?></strong></td>
                        <td class="<?php echo $classe; ?>">
                            <?php echo number_format($value, 0, ',', ' '); ?>
                        </td>
                        <td><?php echo number_format($pourcentage, 2, ',', ' '); ?>%</td>
                        <td>
                            <?php if ($pourcentage > 15): ?>
                                <span style="color: #28a745;">✓ Excellent</span>
                            <?php elseif ($pourcentage > 5): ?>
                                <span style="color: #ffc107;">↔ Satisfaisant</span>
                            <?php else: ?>
                                <span style="color: #dc3545;">⚠ À améliorer</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="chart-container">
            <canvas id="sigChart"></canvas>
        </div>
        
        <div class="sig-actions">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimer le SIG
            </button>
            <button onclick="exportToExcel()" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exporter Excel
            </button>
            <button onclick="comparerExercices()" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Comparaison exercices
            </button>
        </div>
    </div>
    
    <script>
    const ctx = document.getElementById('sigChart').getContext('2d');
    const sigChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Marge Commerciale', 'Production', 'Valeur Ajoutée', 'EBE', 'Résultat Exploitation', 'Résultat Courant', 'Résultat Net'],
            datasets: [{
                label: 'Soldes Intermédiaires (FCFA)',
                data: [
                    <?php echo $sig_data['marge_commerciale']; ?>,
                    <?php echo $sig_data['production_exercice']; ?>,
                    <?php echo $sig_data['valeur_ajoutee']; ?>,
                    <?php echo $sig_data['ebe']; ?>,
                    <?php echo $sig_data['resultat_exploitation']; ?>,
                    <?php echo $sig_data['resultat_courant']; ?>,
                    <?php echo $sig_data['resultat_net']; ?>
                ],
                backgroundColor: [
                    '#3498db', '#2ecc71', '#9b59b6', '#f1c40f',
                    '#e74c3c', '#1abc9c', '#34495e'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution des Soldes Intermédiaires'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
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
    
    function exportToExcel() {
        window.location.href = 'export_sig.php?id_exercice=<?php echo $id_exercice; ?>';
    }
    
    function comparerExercices() {
        window.location.href = 'comparatif_sig.php';
    }
    </script>
</body>
</html><?php
// soldes_gestion.php - Analyse des soldes intermédiaires de gestion

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Récupérer les exercices
$sql_exercices = "SELECT id_exercice, annee FROM exercices_comptables ORDER BY annee DESC";
$exercices = $pdo->query($sql_exercices)->fetchAll(PDO::FETCH_ASSOC);

// Déterminer l'exercice
$id_exercice = isset($_GET['exercice']) ? intval($_GET['exercice']) : null;
if (!$id_exercice && count($exercices) > 0) {
    $id_exercice = $exercices[0]['id_exercice'];
}

if ($id_exercice) {
    // CALCUL DES SIG (Soldes Intermédiaires de Gestion)
    
    // 1. MARGE COMMERCIALE
    $sql_marge = "SELECT 
                    SUM(CASE WHEN compte_num LIKE '707%' THEN credit ELSE 0 END) as ventes,
                    SUM(CASE WHEN compte_num LIKE '607%' THEN debit ELSE 0 END) as achats_revendu
                  FROM ecritures 
                  WHERE id_exercice = ?";
    
    // 2. PRODUCTION DE L'EXERCICE
    $sql_production = "SELECT 
                        SUM(CASE WHEN LEFT(compte_num, 1) = '7' AND compte_num NOT LIKE '707%' THEN credit ELSE 0 END) as production,
                        SUM(CASE WHEN compte_num LIKE '60%' AND compte_num NOT LIKE '607%' THEN debit ELSE 0 END) as achats_consommes
                      FROM ecritures 
                      WHERE id_exercice = ?";
    
    // 3. VALEUR AJOUTÉE
    // VA = Marge commerciale + Production - Consommations
    
    // 4. EXCÉDENT BRUT D'EXPLOITATION
    $sql_ebe = "SELECT 
                  SUM(CASE WHEN compte_num LIKE '62%' THEN debit ELSE 0 END) as charges_personnel,
                  SUM(CASE WHEN compte_num LIKE '63%' THEN debit ELSE 0 END) as charges_externes,
                  SUM(CASE WHEN compte_num LIKE '64%' THEN debit ELSE 0 END) as charges_fiscales,
                  SUM(CASE WHEN compte_num LIKE '68%' THEN debit ELSE 0 END) as dotations
                FROM ecritures 
                WHERE id_exercice = ?";
    
    // 5. RÉSULTAT D'EXPLOITATION
    // RE = EBE - Autres charges d'exploitation
    
    // 6. RÉSULTAT COURANT
    $sql_financier = "SELECT 
                        SUM(CASE WHEN compte_num LIKE '76%' THEN credit ELSE 0 END) as produits_financiers,
                        SUM(CASE WHEN compte_num LIKE '66%' THEN debit ELSE 0 END) as charges_financieres
                      FROM ecritures 
                      WHERE id_exercice = ?";
    
    // 7. RÉSULTAT EXCEPTIONNEL
    $sql_exceptionnel = "SELECT 
                           SUM(CASE WHEN compte_num LIKE '77%' THEN credit ELSE 0 END) as produits_exceptionnels,
                           SUM(CASE WHEN compte_num LIKE '67%' THEN debit ELSE 0 END) as charges_exceptionnelles
                         FROM ecritures 
                         WHERE id_exercice = ?";
    
    // 8. RÉSULTAT NET
    $sql_resultat = "SELECT 
                       (SELECT SUM(credit) FROM ecritures WHERE id_exercice = ? AND LEFT(compte_num, 1) = '7') as total_produits,
                       (SELECT SUM(debit) FROM ecritures WHERE id_exercice = ? AND LEFT(compte_num, 1) = '6') as total_charges";
    
    try {
        // Exécution des requêtes
        $stmt = $pdo->prepare($sql_marge);
        $stmt->execute([$id_exercice]);
        $marge = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare($sql_production);
        $stmt->execute([$id_exercice]);
        $production = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare($sql_ebe);
        $stmt->execute([$id_exercice]);
        $ebe_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare($sql_financier);
        $stmt->execute([$id_exercice]);
        $financier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare($sql_exceptionnel);
        $stmt->execute([$id_exercice]);
        $exceptionnel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare($sql_resultat);
        $stmt->execute([$id_exercice, $id_exercice]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calcul des SIG
        $sig = [
            'marge_commerciale' => ($marge['ventes'] ?? 0) - ($marge['achats_revendu'] ?? 0),
            'production_exercice' => $production['production'] ?? 0,
            'consommations' => $production['achats_consommes'] ?? 0,
            'valeur_ajoutee' => 0, // Calculé plus bas
            'charges_personnel' => $ebe_data['charges_personnel'] ?? 0,
            'charges_externes' => $ebe_data['charges_externes'] ?? 0,
            'charges_fiscales' => $ebe_data['charges_fiscales'] ?? 0,
            'dotations' => $ebe_data['dotations'] ?? 0,
            'ebe' => 0, // Calculé plus bas
            'produits_financiers' => $financier['produits_financiers'] ?? 0,
            'charges_financieres' => $financier['charges_financieres'] ?? 0,
            'resultat_financier' => ($financier['produits_financiers'] ?? 0) - ($financier['charges_financieres'] ?? 0),
            'produits_exceptionnels' => $exceptionnel['produits_exceptionnels'] ?? 0,
            'charges_exceptionnelles' => $exceptionnel['charges_exceptionnelles'] ?? 0,
            'resultat_exceptionnel' => ($exceptionnel['produits_exceptionnels'] ?? 0) - ($exceptionnel['charges_exceptionnelles'] ?? 0),
            'resultat_net' => ($resultat['total_produits'] ?? 0) - ($resultat['total_charges'] ?? 0)
        ];
        
        // Calculs intermédiaires
        $sig['valeur_ajoutee'] = $sig['marge_commerciale'] + $sig['production_exercice'] - $sig['consommations'];
        $sig['ebe'] = $sig['valeur_ajoutee'] - $sig['charges_personnel'];
        $sig['resultat_exploitation'] = $sig['ebe'] - $sig['charges_externes'] - $sig['charges_fiscales'] - $sig['dotations'];
        $sig['resultat_courant'] = $sig['resultat_exploitation'] + $sig['resultat_financier'];
        $sig['resultat_avant_impot'] = $sig['resultat_courant'] + $sig['resultat_exceptionnel'];
        
        $donnees_valides = true;
        
    } catch (PDOException $e) {
        $erreur = "Erreur de calcul des SIG: " . $e->getMessage();
        $donnees_valides = false;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soldes Intermédiaires de Gestion - SYSCOA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/navigation.php'; ?>
    
    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h1 class="h2 mb-3"><i class="fas fa-chart-pie"></i> Soldes Intermédiaires de Gestion</h1>
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <?php if (isset($exercices) && count($exercices) > 0): ?>
                                <p class="mb-0">Analyse de la performance économique</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" class="d-flex">
                                    <select name="exercice" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($exercices as $ex): ?>
                                        <option value="<?php echo $ex['id_exercice']; ?>" 
                                            <?php echo ($id_exercice == $ex['id_exercice']) ? 'selected' : ''; ?>>
                                            Exercice <?php echo $ex['annee']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($erreur)): ?>
        <div class="alert alert-danger"><?php echo $erreur; ?></div>
        <?php endif; ?>

        <?php if ($donnees_valides): ?>
        <!-- Tableau des SIG -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Analyse des soldes intermédiaires de gestion</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60%">SOLDE INTERMÉDIAIRE DE GESTION</th>
                                        <th width="20%" class="text-end">Montant (FCFA)</th>
                                        <th width="20%" class="text-end">% du CA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Calcul du chiffre d'affaires approximatif
                                    $ca = $sig['marge_commerciale'] + $sig['production_exercice'];
                                    $ca = $ca > 0 ? $ca : 1;
                                    ?>
                                    
                                    <!-- 1. Chiffre d'affaires -->
                                    <tr class="table-primary">
                                        <td><strong>1. CHIFFRE D'AFFAIRES NET</strong></td>
                                        <td class="text-end"><strong><?php echo number_format($ca, 0, ',', ' '); ?></strong></td>
                                        <td class="text-end"><strong>100%</strong></td>
                                    </tr>
                                    
                                    <!-- 2. Marge commerciale -->
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;2. Marge commerciale</td>
                                        <td class="text-end <?php echo $sig['marge_commerciale'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($sig['marge_commerciale'], 0, ',', ' '); ?>
                                        </td>
                                        <td class="text-end"><?php echo round(($sig['marge_commerciale']/$ca)*100, 1); ?>%</td>
                                    </tr>
                                    
                                    <!-- 3. Production de l'exercice -->
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;3. Production de l'exercice</td>
                                        <td class="text-end"><?php echo number_format($sig['production_exercice'], 0, ',', ' '); ?></td>
                                        <td class="text-end"><?php echo round(($sig['production_exercice']/$ca)*100, 1); ?>%</td>
                                    </tr>
                                    
                                    <!-- 4. Consommations -->
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;4. Consommations de l'exercice</td>
                                        <td class="text-end text-danger">(<?php echo number_format($sig['consommations'], 0, ',', ' '); ?>)</td>
                                        <td class="text-end"><?php echo round(($sig['consommations']/$ca)*100, 1); ?>%</td>
                                    </tr>
                                    
                                    <!-- 5. Valeur ajoutée -->
                                    <tr class="table-info">
                                        <td><strong>5. VALEUR AJOUTÉE</strong></td>
                                        <td class="text-end"><strong class="<?php echo $sig['valeur_ajoutee'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($sig['valeur_ajoutee'], 0, ',', ' '); ?>
                                        </strong></td>
                                        <td class="text-end"><strong><?php echo round(($sig['valeur_ajoutee']/$ca)*100, 1); ?>%</strong></td>
                                    </tr>
                                    
                                    <!-- 6. Charges de personnel -->
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;6. Charges de personnel</td>
                                        <td class="text-end text-danger">(<?php echo number_format($sig['charges_personnel'], 0, ',', ' '); ?>)</td>
                                        <td class="text-end"><?php echo round(($sig['charges_personnel']/$ca)*100, 1); ?>%</td>
                                    </tr>
                                    
                                    <!-- 7. Excédent Brut d'Exploitation -->
                                    <tr class="table-warning">
                                        <td><strong>7. EXCÉDENT BRUT D'EXPLOITATION (EBE)</strong></td>
                                        <td class="text-end"><strong class="<?php echo $sig['ebe'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($sig['ebe'], 0, ',', ' '); ?>
                                        </strong></td>
                                        <td class="text-end"><strong><?php echo round(($sig['ebe']/$ca)*100, 1); ?>%</strong></td>
                                    </tr>
                                    
                                    <!-- 8. Autres charges -->
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;8. Autres charges d'exploitation</td>
                                        <td class="text-end text-danger">(<?php echo number_format($sig['charges_externes'] + $sig['charges_fiscales'] + $sig['dotations'], 0, ',', ' '); ?>)</td>
                                        <td class="text-end"><?php echo round((($sig['charges_externes'] + $sig['charges_fiscales'] + $sig['dotations'])/$ca)*100, 1); ?>%</td>
                                    </tr>
                                    
                                    <!-- 9. Résultat d'exploitation -->
                                    <tr class="table-success">
                                        <td><strong>9. RÉSULTAT D'EXPLOITATION</strong></td>
                                        <td class="text-end"><strong class="<?php echo $sig['resultat_exploitation'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($sig['resultat_exploitation'], 0, ',', ' '); ?>
                                        </strong></td>
                                        <td class="text-end"><strong><?php echo round(($sig['resultat_exploitation']/$ca)*100, 1); ?>%</strong></td>
                                    </tr>
                                    
                                    <!-- 10. Résultat financier -->
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;10. Résultat financier</td>
                                        <td class="text-end <?php echo $sig['resultat_financier'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($sig['resultat_financier'], 0, ',', ' '); ?>
                                        </td>
                                        <td class="text-end"><?php echo round(($sig['resultat_financier']/$ca)*100, 1); ?>%</td>
                                    </tr>
                                    
                                    <!-- 11. Résultat courant -->
                                    <tr class="table-info">
                                        <td><strong>11. RÉSULTAT COURANT AVANT IMPÔTS</strong></td>
                                        <td class="text-end"><strong class="<?php echo $sig['resultat_courant'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($sig['resultat_courant'], 0, ',', ' '); ?>
                                        </strong></td>
                                        <td class="text-end"><strong><?php echo round(($sig['resultat_courant']/$ca)*100, 1); ?>%</strong></td>
                                    </tr>
                                    
                                    <!-- 12. Résultat exceptionnel -->
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;12. Résultat exceptionnel</td>
                                        <td class="text-end <?php echo $sig['resultat_exceptionnel'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($sig['resultat_exceptionnel'], 0, ',', ' '); ?>
                                        </td>
                                        <td class="text-end"><?php echo round(($sig['resultat_exceptionnel']/$ca)*100, 1); ?>%</td>
                                    </tr>
                                    
                                    <!-- 13. Résultat net -->
                                    <tr class="table-primary">
                                        <td><strong>13. RÉSULTAT NET DE L'EXERCICE</strong></td>
                                        <td class="text-end"><strong class="<?php echo $sig['resultat_net'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($sig['resultat_net'], 0, ',', ' '); ?>
                                        </strong></td>
                                        <td class="text-end"><strong><?php echo round(($sig['resultat_net']/$ca)*100, 1); ?>%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Répartition des SIG</h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px; background: #f8f9fa; border-radius: 8px; padding: 20px;">
                            <p class="text-center text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-3"></i><br>
                                Graphique des soldes intermédiaires
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Indicateurs clés</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6>Taux de marge commerciale</h6>
                                        <h3 class="<?php echo ($sig['marge_commerciale']/$ca)*100 >= 20 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo round(($sig['marge_commerciale']/$ca)*100, 1); ?>%
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6>Taux de VA</h6>
                                        <h3 class="<?php echo ($sig['valeur_ajoutée']/$ca)*100 >= 30 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo round(($sig['valeur_ajoutee']/$ca)*100, 1); ?>%
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6>Rentabilité d'exploitation</h6>
                                        <h3 class="<?php echo ($sig['resultat_exploitation']/$ca)*100 >= 10 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo round(($sig['resultat_exploitation']/$ca)*100, 1); ?>%
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6>Rentabilité nette</h6>
                                        <h3 class="<?php echo ($sig['resultat_net']/$ca)*100 >= 5 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo round(($sig['resultat_net']/$ca)*100, 1); ?>%
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Boutons d'action -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer le rapport
                        </button>
                        <a href="compte_resultat.php?exercice=<?php echo $id_exercice; ?>" class="btn btn-success">
                            <i class="fas fa-file-alt me-2"></i>Voir le compte de résultat
                        </a>
                        <a href="rapport_sig.php?exercice=<?php echo $id_exercice; ?>" class="btn btn-info">
                            <i class="fas fa-chart-line me-2"></i>Analyse comparative
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Données insuffisantes pour calculer les soldes intermédiaires.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
