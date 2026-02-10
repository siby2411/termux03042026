<?php
// journal_comptable.php - Version finale corrigée

// Activer toutes les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration de la base de données
require_once 'config/database.php';

// Vérifier la connexion
if (!isset($pdo)) {
    die("Erreur de connexion à la base de données.");
}

// Fonction de formatage
function formatMontant($montant) {
    if ($montant === null || $montant === '' || !is_numeric($montant)) {
        return '0,00';
    }
    return number_format(floatval($montant), 2, ',', ' ');
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
    // Prendre le dernier exercice ouvert
    $sql_exercice = "SELECT * FROM exercices_comptables WHERE statut = 'ouvert' ORDER BY annee DESC LIMIT 1";
    $exercice_courant = $pdo->query($sql_exercice)->fetch(PDO::FETCH_ASSOC);
    $id_exercice = $exercice_courant ? $exercice_courant['id_exercice'] : $exercices[0]['id_exercice'];
}

// Filtres
$filtre_journal = $_GET['journal'] ?? '';
$filtre_date_debut = $_GET['date_debut'] ?? '';
$filtre_date_fin = $_GET['date_fin'] ?? '';
$filtre_compte = $_GET['compte'] ?? '';

// Construction de la requête
$sql = "SELECT ecriture_id, date_ecriture, journal_code, num_piece, compte_num, libelle, debit, credit 
        FROM ecritures 
        WHERE id_exercice = ?";
$params = [$id_exercice];

if (!empty($filtre_journal)) {
    $sql .= " AND journal_code = ?";
    $params[] = $filtre_journal;
}

if (!empty($filtre_date_debut)) {
    $sql .= " AND date_ecriture >= ?";
    $params[] = $filtre_date_debut;
}

if (!empty($filtre_date_fin)) {
    $sql .= " AND date_ecriture <= ?";
    $params[] = $filtre_date_fin;
}

if (!empty($filtre_compte)) {
    $sql .= " AND compte_num LIKE ?";
    $params[] = $filtre_compte . '%';
}

$sql .= " ORDER BY date_ecriture, ecriture_id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ecritures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcul des totaux
    $total_debit = 0;
    $total_credit = 0;
    foreach ($ecritures as $e) {
        $total_debit += floatval($e['debit']);
        $total_credit += floatval($e['credit']);
    }
    
    $donnees_valides = true;
    
} catch (PDOException $e) {
    $erreur = "Erreur SQL: " . $e->getMessage();
    $donnees_valides = false;
}

// Liste des journaux distincts
$journaux = $pdo->query("SELECT DISTINCT journal_code FROM ecritures ORDER BY journal_code")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Comptable - SYSCOA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.03);
        }
        .badge-journal {
            font-size: 0.75rem;
            padding: 3px 8px;
        }
    </style>
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
                                <h1 class="h3 mb-2"><i class="fas fa-book me-2"></i>Journal Comptable</h1>
                                <?php if ($exercice_courant): ?>
                                <p class="mb-0">Exercice <?php echo htmlspecialchars($exercice_courant['annee']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" class="d-flex gap-2">
                                    <select name="exercice" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($exercices as $ex): ?>
                                        <option value="<?php echo $ex['id_exercice']; ?>" 
                                            <?php echo ($id_exercice == $ex['id_exercice']) ? 'selected' : ''; ?>>
                                            Exercice <?php echo $ex['annee']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-light" onclick="window.location.href='saisie_ecriture.php'">
                                        <i class="fas fa-plus me-1"></i>Nouvelle
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <input type="hidden" name="exercice" value="<?php echo $id_exercice; ?>">
                            
                            <div class="col-md-3">
                                <label class="form-label">Journal</label>
                                <select name="journal" class="form-select">
                                    <option value="">Tous les journaux</option>
                                    <?php foreach ($journaux as $j): ?>
                                    <option value="<?php echo $j; ?>" <?php echo ($filtre_journal == $j) ? 'selected' : ''; ?>>
                                        <?php echo $j; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_debut" class="form-control" value="<?php echo $filtre_date_debut; ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_fin" class="form-control" value="<?php echo $filtre_date_fin; ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Compte</label>
                                <input type="text" name="compte" class="form-control" placeholder="Ex: 411" value="<?php echo $filtre_compte; ?>">
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i>Filtrer
                                    </button>
                                    <a href="journal_comptable.php?exercice=<?php echo $id_exercice; ?>" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des écritures -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Écritures comptables</h5>
                        <div>
                            <button class="btn btn-sm btn-primary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>Imprimer
                            </button>
                            <button class="btn btn-sm btn-success" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (isset($erreur)): ?>
                        <div class="alert alert-danger m-3"><?php echo $erreur; ?></div>
                        <?php endif; ?>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="90">Date</th>
                                        <th width="70">Journal</th>
                                        <th width="100">Pièce</th>
                                        <th width="100">Compte</th>
                                        <th>Libellé</th>
                                        <th width="120" class="text-end">Débit</th>
                                        <th width="120" class="text-end">Crédit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($donnees_valides && !empty($ecritures)): ?>
                                        <?php foreach ($ecritures as $e): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($e['date_ecriture'])); ?></td>
                                            <td>
                                                <span class="badge badge-journal bg-info">
                                                    <?php echo htmlspecialchars($e['journal_code']); ?>
                                                </span>
                                            </td>
                                            <td><small><?php echo htmlspecialchars($e['num_piece']); ?></small></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($e['compte_num']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($e['libelle']); ?></td>
                                            <td class="text-end <?php echo $e['debit'] > 0 ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                                <?php echo formatMontant($e['debit']); ?>
                                            </td>
                                            <td class="text-end <?php echo $e['credit'] > 0 ? 'text-success fw-bold' : 'text-muted'; ?>">
                                                <?php echo formatMontant($e['credit']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p>Aucune écriture trouvée</p>
                                                    <a href="saisie_ecriture.php" class="btn btn-primary mt-2">
                                                        <i class="fas fa-plus me-2"></i>Saisir une écriture
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if ($donnees_valides && !empty($ecritures)): ?>
                                <tfoot class="table-active">
                                    <tr>
                                        <th colspan="5" class="text-end">TOTAUX</th>
                                        <th class="text-end text-danger"><?php echo formatMontant($total_debit); ?></th>
                                        <th class="text-end text-success"><?php echo formatMontant($total_credit); ?></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">SOLDE</th>
                                        <th colspan="2" class="text-center <?php echo $total_debit == $total_credit ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo formatMontant($total_debit - $total_credit); ?>
                                            <?php if ($total_debit != $total_credit): ?>
                                            <br><small class="text-danger">Déséquilibre!</small>
                                            <?php endif; ?>
                                        </th>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($donnees_valides && !empty($ecritures)): ?>
                    <div class="card-footer bg-white">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <?php echo count($ecritures); ?> écriture(s) - 
                                    Débit: <?php echo formatMontant($total_debit); ?> F - 
                                    Crédit: <?php echo formatMontant($total_credit); ?> F
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <?php if ($total_debit == $total_credit): ?>
                                <span class="badge bg-success">Journal équilibré</span>
                                <?php else: ?>
                                <span class="badge bg-danger">
                                    Déséquilibre: <?php echo formatMontant(abs($total_debit - $total_credit)); ?> F
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToExcel() {
            const table = document.querySelector('table');
            const html = table.outerHTML;
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'journal_<?php echo date('Y-m-d'); ?>.xls';
            a.click();
        }
        
        // Auto-actualisation toutes les 2 minutes
        setTimeout(() => {
            window.location.reload();
        }, 120000);
    </script>
</body>
</html>
