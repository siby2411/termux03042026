<?php
// compte_resultat.php - Version finale corrigée

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclure la configuration de la base de données
require_once 'config/database.php';

// Fonction de formatage
if (!function_exists('formatMontant')) {
    function formatMontant($montant) {
        if ($montant === null || $montant === '' || !is_numeric($montant)) {
            return '0,00 FCFA';
        }
        return number_format(floatval($montant), 2, ',', ' ') . ' FCFA';
    }
}

// Récupérer TOUS les exercices pour permettre la sélection
$sql_exercices = "SELECT id_exercice, annee FROM exercices_comptables ORDER BY annee DESC";
$exercices = $pdo->query($sql_exercices)->fetchAll(PDO::FETCH_ASSOC);

// Déterminer l'exercice à afficher (par défaut: dernier ouvert)
$exercice_courant = null;
$id_exercice = null;

if (isset($_GET['exercice']) && is_numeric($_GET['exercice'])) {
    $id_exercice = intval($_GET['exercice']);
    $sql_exercice = "SELECT * FROM exercices_comptables WHERE id_exercice = ?";
    $stmt = $pdo->prepare($sql_exercice);
    $stmt->execute([$id_exercice]);
    $exercice_courant = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$exercice_courant) {
    // Prendre le dernier exercice ouvert
    $sql_exercice = "SELECT * FROM exercices_comptables WHERE statut = 'ouvert' ORDER BY annee DESC LIMIT 1";
    $exercice_courant = $pdo->query($sql_exercice)->fetch(PDO::FETCH_ASSOC);
    
    if (!$exercice_courant && count($exercices) > 0) {
        // Prendre le dernier exercice tout court
        $exercice_courant = $exercices[0];
    }
    
    $id_exercice = $exercice_courant ? $exercice_courant['id_exercice'] : null;
}

if ($exercice_courant && $id_exercice) {
    // REQUÊTES DIRECTES - CORRIGÉES
    // 1. PRODUITS - Classe 7
    $sql_produits = "SELECT 
                        compte_num as numero_compte,
                        libelle as nom_compte,
                        SUM(credit) as total_produits
                     FROM ecritures 
                     WHERE id_exercice = ? 
                     AND LEFT(compte_num, 1) = '7'
                     GROUP BY compte_num, libelle
                     HAVING total_produits > 0
                     ORDER BY compte_num";
    
    // 2. CHARGES - Classe 6
    $sql_charges = "SELECT 
                        compte_num as numero_compte,
                        libelle as nom_compte,
                        SUM(debit) as total_charges
                     FROM ecritures 
                     WHERE id_exercice = ? 
                     AND LEFT(compte_num, 1) = '6'
                     GROUP BY compte_num, libelle
                     HAVING total_charges > 0
                     ORDER BY compte_num";
    
    try {
        $stmt = $pdo->prepare($sql_produits);
        $stmt->execute([$id_exercice]);
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare($sql_charges);
        $stmt->execute([$id_exercice]);
        $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcul des totaux
        $total_produits = array_sum(array_column($produits, 'total_produits'));
        $total_charges = array_sum(array_column($charges, 'total_charges'));
        $resultat_exercice = $total_produits - $total_charges;
        
        $nb_comptes_produits = count($produits);
        $nb_comptes_charges = count($charges);
        
        $donnees_valides = true;
        
    } catch (PDOException $e) {
        $erreur = "Erreur SQL: " . $e->getMessage();
        $donnees_valides = false;
    }
} else {
    $erreur = "Aucun exercice comptable trouvé.";
    $donnees_valides = false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte de Résultat - SYSCOA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-analytics { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card-header-analytics { border-radius: 15px 15px 0 0 !important; }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/navigation.php'; ?>
    
    <div class="container-fluid py-4">
        <!-- En-tête avec sélecteur d'exercice -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-analytics bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="h2 mb-2"><i class="fas fa-chart-line"></i> Compte de Résultat</h1>
                                <?php if ($exercice_courant): ?>
                                <p class="mb-0">
                                    Exercice <?php echo htmlspecialchars($exercice_courant['annee']); ?>
                                    <?php if ($exercice_courant['date_debut']): ?>
                                    - Du <?php echo date('d/m/Y', strtotime($exercice_courant['date_debut'])); ?> 
                                    au <?php echo date('d/m/Y', strtotime($exercice_courant['date_fin'])); ?>
                                    <?php endif; ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
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
        <!-- Cartes indicateurs -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-analytics bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">PRODUITS</h6>
                                <h3><?php echo formatMontant($total_produits); ?></h3>
                                <small><?php echo $nb_comptes_produits; ?> comptes</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-up fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-analytics bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">CHARGES</h6>
                                <h3><?php echo formatMontant($total_charges); ?></h3>
                                <small><?php echo $nb_comptes_charges; ?> comptes</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-down fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-analytics bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-dark-50">MARGE BRUTE</h6>
                                <h3><?php echo formatMontant($total_produits - $total_charges); ?></h3>
                                <small>Taux: <?php echo $total_produits > 0 ? round((($total_produits - $total_charges)/$total_produits)*100, 1) : 0; ?>%</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-percentage fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-analytics <?php echo $resultat_exercice >= 0 ? 'bg-info' : 'bg-secondary'; ?> text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50">RÉSULTAT NET</h6>
                                <h3><?php echo formatMontant(abs($resultat_exercice)); ?></h3>
                                <small><?php echo $resultat_exercice >= 0 ? 'BÉNÉFICE' : 'PERTE'; ?></small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-balance-scale fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau détaillé -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card card-analytics">
                    <div class="card-header card-header-analytics bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-arrow-up me-2"></i>PRODUITS (Classe 7)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Compte</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($nb_comptes_produits > 0): ?>
                                        <?php foreach ($produits as $p): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?php echo $p['numero_compte']; ?></span></td>
                                            <td><?php echo htmlspecialchars($p['nom_compte']); ?></td>
                                            <td class="text-end text-success fw-bold"><?php echo formatMontant($p['total_produits']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center text-muted">Aucun produit</td></tr>
                                    <?php endif; ?>
                                    <tr class="table-active">
                                        <th colspan="2" class="text-end">TOTAL PRODUITS</th>
                                        <th class="text-end text-success"><?php echo formatMontant($total_produits); ?></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card card-analytics">
                    <div class="card-header card-header-analytics bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-arrow-down me-2"></i>CHARGES (Classe 6)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Compte</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($nb_comptes_charges > 0): ?>
                                        <?php foreach ($charges as $c): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?php echo $c['numero_compte']; ?></span></td>
                                            <td><?php echo htmlspecialchars($c['nom_compte']); ?></td>
                                            <td class="text-end text-danger fw-bold"><?php echo formatMontant($c['total_charges']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center text-muted">Aucune charge</td></tr>
                                    <?php endif; ?>
                                    <tr class="table-active">
                                        <th colspan="2" class="text-end">TOTAL CHARGES</th>
                                        <th class="text-end text-danger"><?php echo formatMontant($total_charges); ?></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Synthèse -->
        <div class="row">
            <div class="col-12">
                <div class="card card-analytics">
                    <div class="card-header card-header-analytics bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>SYNTHÈSE DU RÉSULTAT</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h4 class="text-success"><?php echo formatMontant($total_produits); ?></h4>
                                <p class="text-muted mb-0">Total Produits</p>
                            </div>
                            <div class="col-md-4">
                                <h4 class="text-danger"><?php echo formatMontant($total_charges); ?></h4>
                                <p class="text-muted mb-0">Total Charges</p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="<?php echo $resultat_exercice >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo formatMontant(abs($resultat_exercice)); ?>
                                </h3>
                                <span class="badge <?php echo $resultat_exercice >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $resultat_exercice >= 0 ? 'BÉNÉFICE' : 'PERTE'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="alert <?php echo $resultat_exercice >= 0 ? 'alert-success' : 'alert-danger'; ?> mt-4">
                            <h5 class="mb-0">
                                <i class="fas fa-<?php echo $resultat_exercice >= 0 ? 'check' : 'exclamation'; ?>-circle me-2"></i>
                                Résultat = Produits (<?php echo formatMontant($total_produits); ?>) - Charges (<?php echo formatMontant($total_charges); ?>)
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Aucune donnée disponible pour cet exercice.
            <a href="saisie_ecriture.php" class="alert-link">Saisir des écritures</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
