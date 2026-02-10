<?php
// bilan-comptable.php - Version corrigée

// Activer toutes les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration
require_once 'config/database.php';

// Vérifier la connexion
if (!isset($pdo)) {
    die("Erreur de connexion à la base de données.");
}

// Fonction de formatage
function formatMontant($montant) {
    if ($montant === null || $montant === '' || !is_numeric($montant)) {
        return '0,00 FCFA';
    }
    return number_format(floatval($montant), 2, ',', ' ') . ' FCFA';
}

// Récupérer les exercices
$sql_exercices = "SELECT id_exercice, annee FROM exercices_comptables ORDER BY annee DESC";
$exercices = $pdo->query($sql_exercices)->fetchAll(PDO::FETCH_ASSOC);

// Déterminer l'exercice
$exercice_courant = null;
$id_exercice = null;

if (isset($_GET['exercice']) && is_numeric($_GET['exercice'])) {
    $id_exercice = intval($_GET['exercice']);
    $sql_exercice = "SELECT * FROM exercices_comptables WHERE id_exercice = ?";
    $stmt = $pdo->prepare($sql_exercice);
    $stmt->execute([$id_exercice]);
    $exercice_courant = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$exercice_courant && count($exercices) > 0) {
    $id_exercice = $exercices[0]['id_exercice'];
    $sql_exercice = "SELECT * FROM exercices_comptables WHERE id_exercice = ?";
    $stmt = $pdo->prepare($sql_exercice);
    $stmt->execute([$id_exercice]);
    $exercice_courant = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($exercice_courant && $id_exercice) {
    try {
        // CALCUL SIMPLIFIÉ DU BILAN
        
        // ACTIF = Comptes 2, 3, 4, 5 (soldes débiteurs)
        $sql_actif = "SELECT 
                        compte_num,
                        libelle,
                        SUM(debit - credit) as solde
                      FROM ecritures 
                      WHERE id_exercice = ?
                      AND LEFT(compte_num, 1) IN ('2', '3', '4', '5')
                      GROUP BY compte_num, libelle
                      HAVING solde > 0
                      ORDER BY compte_num";
        
        $stmt = $pdo->prepare($sql_actif);
        $stmt->execute([$id_exercice]);
        $actif = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_actif = array_sum(array_column($actif, 'solde'));
        
        // PASSIF = Comptes 1 et 4 (soldes créditeurs)
        $sql_passif = "SELECT 
                         compte_num,
                         libelle,
                         SUM(credit - debit) as solde
                       FROM ecritures 
                       WHERE id_exercice = ?
                       AND LEFT(compte_num, 1) IN ('1', '4')
                       GROUP BY compte_num, libelle
                       HAVING solde > 0
                       ORDER BY compte_num";
        
        $stmt = $pdo->prepare($sql_passif);
        $stmt->execute([$id_exercice]);
        $passif = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_passif = array_sum(array_column($passif, 'solde'));
        
        // CAPITAUX PROPRES = Comptes 10...
        $sql_capitaux = "SELECT 
                           compte_num,
                           libelle,
                           SUM(credit - debit) as solde
                         FROM ecritures 
                         WHERE id_exercice = ?
                         AND compte_num LIKE '10%'
                         GROUP BY compte_num, libelle";
        
        $stmt = $pdo->prepare($sql_capitaux);
        $stmt->execute([$id_exercice]);
        $capitaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_capitaux = array_sum(array_column($capitaux, 'solde'));
        
        $donnees_valides = true;
        
    } catch (PDOException $e) {
        $erreur = "Erreur de calcul: " . $e->getMessage();
        $donnees_valides = false;
    }
} else {
    $erreur = "Aucun exercice sélectionné";
    $donnees_valides = false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan Comptable - SYSCOA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h1 class="h3 mb-2"><i class="fas fa-balance-scale me-2"></i>Bilan Comptable</h1>
                                <?php if ($exercice_courant): ?>
                                <p class="mb-0">Exercice <?php echo htmlspecialchars($exercice_courant['annee']); ?> - 
                                Au <?php echo date('d/m/Y', strtotime($exercice_courant['date_fin'])); ?></p>
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
        <!-- Indicateurs -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6>TOTAL ACTIF</h6>
                        <h3><?php echo formatMontant($total_actif); ?></h3>
                        <small><?php echo count($actif); ?> postes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h6>TOTAL PASSIF</h6>
                        <h3><?php echo formatMontant($total_passif); ?></h3>
                        <small><?php echo count($passif); ?> postes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card <?php echo abs($total_actif - $total_passif) < 0.01 ? 'bg-success' : 'bg-danger'; ?> text-white">
                    <div class="card-body text-center">
                        <h6>ÉQUILIBRE</h6>
                        <h3><?php echo formatMontant(abs($total_actif - $total_passif)); ?></h3>
                        <small><?php echo abs($total_actif - $total_passif) < 0.01 ? 'Bilan équilibré' : 'Déséquilibre'; ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau du bilan -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>ACTIF</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Compte</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($actif)): ?>
                                        <?php foreach ($actif as $a): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?php echo $a['compte_num']; ?></span></td>
                                            <td><?php echo htmlspecialchars($a['libelle']); ?></td>
                                            <td class="text-end text-info fw-bold"><?php echo formatMontant($a['solde']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center text-muted py-3">Aucun actif</td></tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-active">
                                    <tr>
                                        <th colspan="2" class="text-end">TOTAL ACTIF</th>
                                        <th class="text-end text-info"><?php echo formatMontant($total_actif); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>PASSIF</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Compte</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($passif)): ?>
                                        <?php foreach ($passif as $p): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?php echo $p['compte_num']; ?></span></td>
                                            <td><?php echo htmlspecialchars($p['libelle']); ?></td>
                                            <td class="text-end text-warning fw-bold"><?php echo formatMontant($p['solde']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center text-muted py-3">Aucun passif</td></tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-active">
                                    <tr>
                                        <th colspan="2" class="text-end">TOTAL PASSIF</th>
                                        <th class="text-end text-warning"><?php echo formatMontant($total_passif); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Synthèse -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>SYNTHÈSE DU BILAN</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h4 class="text-info"><?php echo formatMontant($total_actif); ?></h4>
                                <p class="text-muted mb-0">Total Actif</p>
                            </div>
                            <div class="col-md-4">
                                <h4 class="text-warning"><?php echo formatMontant($total_passif); ?></h4>
                                <p class="text-muted mb-0">Total Passif</p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="<?php echo abs($total_actif - $total_passif) < 0.01 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo formatMontant(abs($total_actif - $total_passif)); ?>
                                </h3>
                                <span class="badge <?php echo abs($total_actif - $total_passif) < 0.01 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo abs($total_actif - $total_passif) < 0.01 ? 'ÉQUILIBRÉ' : 'DÉSÉQUILIBRÉ'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="alert <?php echo abs($total_actif - $total_passif) < 0.01 ? 'alert-success' : 'alert-danger'; ?> mt-4">
                            <h5 class="mb-0">
                                <i class="fas fa-<?php echo abs($total_actif - $total_passif) < 0.01 ? 'check' : 'exclamation'; ?>-circle me-2"></i>
                                Actif (<?php echo formatMontant($total_actif); ?>) 
                                <?php echo $total_actif > $total_passif ? '>' : '<'; ?> 
                                Passif (<?php echo formatMontant($total_passif); ?>)
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Impossible de générer le bilan pour cet exercice.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
