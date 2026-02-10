<?php
// budget.php
require_once 'config/database.php';
require_once 'includes/header.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier les permissions
$user_role = $_SESSION['role'] ?? '';
$allowed_roles = ['admin', 'comptable', 'consultant'];
if (!in_array($user_role, $allowed_roles)) {
    die('<div class="alert alert-danger m-4">Accès refusé. Vous n\'avez pas les permissions nécessaires.</div>');
}

$user = get_current_sysco_user();
$exercice_annee = isset($_GET['exercice']) ? intval($_GET['exercice']) : date('Y');

// Récupérer l'id_exercice
try {
    $sql_exercice = "SELECT id_exercice FROM exercices_comptables WHERE annee = :annee";
    $stmt_exercice = $pdo->prepare($sql_exercice);
    $stmt_exercice->execute([':annee' => $exercice_annee]);
    $id_exercice = $stmt_exercice->fetchColumn();
    
    if (!$id_exercice) {
        $sql_insert = "INSERT INTO exercices_comptables (annee, date_debut, date_fin, statut) 
                       VALUES (:annee, :debut, :fin, 'ouvert')";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([
            ':annee' => $exercice_annee,
            ':debut' => $exercice_annee . '-01-01',
            ':fin' => $exercice_annee . '-12-31'
        ]);
        $id_exercice = $pdo->lastInsertId();
    }
} catch (PDOException $e) {
    die('<div class="alert alert-danger m-4">Erreur exercice: ' . $e->getMessage() . '</div>');
}

// Traitement CRUD
$action = $_GET['action'] ?? 'list';
$id_budget = $_GET['id'] ?? 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $compte = trim($_POST['compte_budgete'] ?? '');
    $montant = floatval($_POST['montant_budget'] ?? 0);
    $type = trim($_POST['type_budget'] ?? '');
    
    if ($compte && $montant > 0 && $type) {
        try {
            if ($_POST['action'] == 'add') {
                $sql = "INSERT INTO budgets (exercice_id, compte_budgete, type_budget, montant_budget) 
                        VALUES (:ex_id, :compte, :type, :montant)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':ex_id' => $id_exercice,
                    ':compte' => $compte,
                    ':type' => $type,
                    ':montant' => $montant
                ]);
                $message = '<div class="alert alert-success">Budget ajouté avec succès</div>';
            } elseif ($_POST['action'] == 'edit' && $id_budget > 0) {
                $sql = "UPDATE budgets SET compte_budgete = :compte, type_budget = :type, 
                        montant_budget = :montant WHERE id_budget = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':compte' => $compte,
                    ':type' => $type,
                    ':montant' => $montant,
                    ':id' => $id_budget
                ]);
                $message = '<div class="alert alert-success">Budget modifié avec succès</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Veuillez remplir tous les champs</div>';
    }
}

if ($action == 'delete' && $id_budget > 0) {
    $sql = "DELETE FROM budgets WHERE id_budget = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_budget]);
    $message = '<div class="alert alert-success">Budget supprimé avec succès</div>';
}

// Récupérer les budgets
try {
    $sql = "SELECT b.*, 
            (SELECT SUM(montant) FROM ecritures 
             WHERE numero_compte = b.compte_budgete 
             AND YEAR(date_ecriture) = :annee) as realise
            FROM budgets b
            WHERE b.exercice_id = :ex_id
            ORDER BY b.type_budget, b.compte_budgete";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':ex_id' => $id_exercice, ':annee' => $exercice_annee]);
    $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $budgets = [];
}

// Récupérer les exercices
try {
    $sql_ex = "SELECT annee FROM exercices_comptables ORDER BY annee DESC";
    $exercices = $pdo->query($sql_ex)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $exercices = [['annee' => date('Y')]];
}

// Calculer les totaux
$total_prevu = 0;
$total_reel = 0;
foreach ($budgets as $budget) {
    $total_prevu += $budget['montant_budget'];
    $total_reel += $budget['realise'] ?? 0;
}

// Récupérer budget pour édition
$budget_edit = null;
if ($action == 'edit' && $id_budget > 0) {
    $sql = "SELECT * FROM budgets WHERE id_budget = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_budget]);
    $budget_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!-- Header Content -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-1">
            <i class="bi bi-cash-stack text-primary me-2"></i>
            Contrôle Budgétaire
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Tableau de bord</a></li>
                <li class="breadcrumb-item active">Budgets</li>
            </ol>
        </nav>
    </div>
    
    <div class="d-flex gap-2">
        <select class="form-select form-select-sm w-auto" onchange="location.href='budget.php?exercice='+this.value">
            <?php foreach($exercices as $ex): ?>
            <option value="<?= $ex['annee'] ?>" <?= $ex['annee'] == $exercice_annee ? 'selected' : '' ?>>
                Exercice <?= $ex['annee'] ?>
            </option>
            <?php endforeach; ?>
        </select>
        
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
            <i class="bi bi-plus-circle me-1"></i> Nouveau budget
        </button>
    </div>
</div>

<?= $message ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-light opacity-75 mb-1">Budget Prévisionnel</h6>
                    <h3 class="mb-0"><?= number_format($total_prevu, 0, ',', ' ') ?> FCFA</h3>
                </div>
                <i class="bi bi-currency-exchange fs-4 opacity-50"></i>
            </div>
            <div class="mt-3">
                <small class="text-light opacity-75">Total des budgets approuvés</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card bg-warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-light opacity-75 mb-1">Réalisations</h6>
                    <h3 class="mb-0"><?= number_format($total_reel, 0, ',', ' ') ?> FCFA</h3>
                </div>
                <i class="bi bi-graph-up-arrow fs-4 opacity-50"></i>
            </div>
            <div class="mt-3">
                <small class="text-light opacity-75">Dépenses réalisées</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card <?= ($total_prevu - $total_reel) >= 0 ? 'bg-success' : 'bg-danger' ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-light opacity-75 mb-1">Écart Budgétaire</h6>
                    <h3 class="mb-0"><?= number_format($total_prevu - $total_reel, 0, ',', ' ') ?> FCFA</h3>
                </div>
                <i class="bi bi-<?= ($total_prevu - $total_reel) >= 0 ? 'arrow-down-right' : 'arrow-up-right' ?> fs-4 opacity-50"></i>
            </div>
            <div class="mt-3">
                <div class="progress-bar-custom">
                    <div class="progress-value bg-light" 
                         style="width: <?= $total_prevu > 0 ? min(100, ($total_reel/$total_prevu)*100) : 0 ?>%"></div>
                </div>
                <small class="text-light opacity-75">
                    Réalisation: <?= $total_prevu > 0 ? round(($total_reel/$total_prevu)*100, 1) : 0 ?>%
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card bg-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-light opacity-75 mb-1">Budgets Actifs</h6>
                    <h3 class="mb-0"><?= count($budgets) ?></h3>
                </div>
                <i class="bi bi-files fs-4 opacity-50"></i>
            </div>
            <div class="mt-3">
                <small class="text-light opacity-75">Nombre de lignes budgétaires</small>
            </div>
        </div>
    </div>
</div>

<!-- Table des budgets -->
<div class="sysco-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead>
                    <tr>
                        <th>Compte</th>
                        <th>Type</th>
                        <th class="text-end">Budget</th>
                        <th class="text-end">Réalisé</th>
                        <th class="text-end">Écart</th>
                        <th>Progression</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($budgets)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted">Aucun budget défini pour cet exercice</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                                Créer un budget
                            </button>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($budgets as $budget): 
                        $realise = $budget['realise'] ?? 0;
                        $ecart = $budget['montant_budget'] - $realise;
                        $pourcentage = $budget['montant_budget'] > 0 ? ($realise/$budget['montant_budget'])*100 : 0;
                        $status_color = $pourcentage <= 90 ? 'success' : ($pourcentage <= 110 ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($budget['compte_budgete']) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-<?= $budget['type_budget'] == 'investissement' ? 'primary' : 
                                                   ($budget['type_budget'] == 'ventes' ? 'success' : 
                                                   ($budget['type_budget'] == 'achats' ? 'danger' : 'info')) ?>">
                                <?= ucfirst($budget['type_budget']) ?>
                            </span>
                        </td>
                        <td class="text-end fw-bold">
                            <?= number_format($budget['montant_budget'], 0, ',', ' ') ?> FCFA
                        </td>
                        <td class="text-end">
                            <?= number_format($realise, 0, ',', ' ') ?> FCFA
                        </td>
                        <td class="text-end fw-bold <?= $ecart >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($ecart, 0, ',', ' ') ?> FCFA
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress-bar-custom flex-grow-1">
                                    <div class="progress-value bg-<?= $status_color ?>" 
                                         style="width: <?= min(100, $pourcentage) ?>%"></div>
                                </div>
                                <span class="text-<?= $status_color ?> fw-bold" style="min-width: 50px">
                                    <?= round($pourcentage, 1) ?>%
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="budget.php?action=edit&id=<?= $budget['id_budget'] ?>&exercice=<?= $exercice_annee ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="budget.php?action=delete&id=<?= $budget['id_budget'] ?>&exercice=<?= $exercice_annee ?>" 
                                   class="btn btn-outline-danger"
                                   onclick="return confirm('Supprimer ce budget ?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout/Modification -->
<div class="modal fade" id="addBudgetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="budget.php?exercice=<?= $exercice_annee ?>">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?= $action == 'edit' ? 'Modifier le budget' : 'Nouveau budget' ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?= $action == 'edit' ? 'edit' : 'add' ?>">
                    <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $id_budget ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Compte budgété *</label>
                        <input type="text" class="form-control" name="compte_budgete" 
                               value="<?= htmlspecialchars($budget_edit['compte_budgete'] ?? '') ?>"
                               placeholder="Ex: 601, 701, 211..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Type de budget *</label>
                        <select class="form-select" name="type_budget" required>
                            <option value="">Sélectionner...</option>
                            <option value="investissement" <?= ($budget_edit['type_budget'] ?? '') == 'investissement' ? 'selected' : '' ?>>Investissement</option>
                            <option value="fonctionnement" <?= ($budget_edit['type_budget'] ?? '') == 'fonctionnement' ? 'selected' : '' ?>>Fonctionnement</option>
                            <option value="ventes" <?= ($budget_edit['type_budget'] ?? '') == 'ventes' ? 'selected' : '' ?>>Ventes</option>
                            <option value="achats" <?= ($budget_edit['type_budget'] ?? '') == 'achats' ? 'selected' : '' ?>>Achats</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Montant budgété (FCFA) *</label>
                        <input type="number" class="form-control" name="montant_budget" 
                               step="0.01" min="0" 
                               value="<?= htmlspecialchars($budget_edit['montant_budget'] ?? '') ?>"
                               placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        <?= $action == 'edit' ? 'Modifier' : 'Enregistrer' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($action == 'edit'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('addBudgetModal'));
        modal.show();
    });
</script>
<?php endif; ?>

<!-- Footer Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once 'includes/footer.php'; ?>
