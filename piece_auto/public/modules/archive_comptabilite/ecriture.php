<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Saisie d'écriture - Journal SYSCOHADA";
$page_icon = "pencil-square";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    $libelle = trim($_POST['libelle'] ?? '');
    $debit_id = (int)($_POST['debite'] ?? 0);
    $credit_id = (int)($_POST['credite'] ?? 0);
    $montant = (float)($_POST['montant'] ?? 0);
    $ref = trim($_POST['ref'] ?? '');

    if ($debit_id === $credit_id) {
        $error = "⚠️ Le compte débit et crédit ne peuvent pas être identiques.";
    } elseif ($montant <= 0) {
        $error = "⚠️ Le montant doit être supérieur à 0 FCFA.";
    } elseif (empty($libelle)) {
        $error = "⚠️ Le libellé de l'écriture est obligatoire.";
    } else {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM PLAN_COMPTABLE_UEMOA WHERE compte_id = ?");
        $checkStmt->execute([$debit_id]);
        $debitExists = $checkStmt->fetchColumn() > 0;
        $checkStmt->execute([$credit_id]);
        $creditExists = $checkStmt->fetchColumn() > 0;

        if (!$debitExists) {
            $error = "❌ Compte débit $debit_id non trouvé dans le plan SYSCOHADA.";
        } elseif (!$creditExists) {
            $error = "❌ Compte crédit $credit_id non trouvé dans le plan SYSCOHADA.";
        } else {
            try {
                $pdo->beginTransaction();
                $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, user_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$date, $libelle, $debit_id, $credit_id, $montant, $ref, $_SESSION['user_id']]);
                $pdo->commit();
                $success = "✅ Écriture validée - Principe de la partie double respecté (Débit = Crédit).";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Erreur SQL : " . $e->getMessage();
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-journal-bookmark-fill"></i> Journal de Saisie (Norme SYSCOHADA UEMOA - Mohamet Siby)</h5>
                <small class="text-muted">Remplissez tous les champs pour enregistrer une opération comptable</small>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="row g-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date d'opération</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Référence pièce</label>
                        <input type="text" name="ref" class="form-control" placeholder="Ex: FACT-2026-001">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Montant (FCFA)</label>
                        <input type="number" name="montant" class="form-control fw-bold text-primary" step="1" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Libellé</label>
                        <input type="text" name="libelle" class="form-control" placeholder="Désignation de l'opération..." required>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <label class="form-label text-danger fw-semibold">
                                <i class="bi bi-arrow-down-circle"></i> Compte Débit
                            </label>
                            <input type="number" name="debite" class="form-control" placeholder="Ex: 521 (Banque)" required>
                            <small class="text-muted">Classe 1 à 9 - Plan comptable SYSCOHADA</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <label class="form-label text-success fw-semibold">
                                <i class="bi bi-arrow-up-circle"></i> Compte Crédit
                            </label>
                            <input type="number" name="credite" class="form-control" placeholder="Ex: 701 (Ventes)" required>
                            <small class="text-muted">Classe 1 à 9 - Plan comptable SYSCOHADA</small>
                        </div>
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn-omega px-5 py-2">
                            <i class="bi bi-check2-all"></i> ENREGISTRER L'OPÉRATION
                        </button>
                        <a href="ecriture_list.php" class="btn btn-outline-secondary ms-3">
                            <i class="bi bi-list-ul"></i> Voir le journal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
