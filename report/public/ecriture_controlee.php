<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Saisie d'écriture contrôlée - SYSCOHADA";
$page_icon = "pencil-square";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$error = '';
$success = '';
$comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $libelle = trim($_POST['libelle']);
    $debit_id = (int)$_POST['debite'];
    $credit_id = (int)$_POST['credite'];
    $montant = (float)$_POST['montant'];
    $ref = trim($_POST['ref']);
    
    // Vérification 1: Montant positif
    if ($montant <= 0) {
        $error = "❌ ERREUR SYSCOHADA: Le montant doit être supérieur à zéro";
    }
    // Vérification 2: Comptes identiques
    elseif ($debit_id == $credit_id) {
        $error = "❌ ERREUR SYSCOHADA: Un compte ne peut pas être à la fois débité et crédité";
    }
    // Vérification 3: Existence des comptes
    else {
        $debit_ok = false;
        $credit_ok = false;
        foreach($comptes as $c) {
            if($c['compte_id'] == $debit_id) $debit_ok = true;
            if($c['compte_id'] == $credit_id) $credit_ok = true;
        }
        
        if(!$debit_ok) $error = "❌ ERREUR SYSCOHADA: Le compte débit $debit_id n'existe pas dans le plan comptable";
        elseif(!$credit_ok) $error = "❌ ERREUR SYSCOHADA: Le compte crédit $credit_id n'existe pas dans le plan comptable";
        else {
            // Vérification 4: Cohérence des classes
            $classe_debit = floor($debit_id / 100);
            $classe_credit = floor($credit_id / 100);
            
            if(($classe_debit >= 6 && $classe_debit <=7) && ($classe_credit >=6 && $classe_credit <=7)) {
                $error = "❌ ERREUR SYSCOHADA: Une écriture ne peut pas être entre deux comptes de charges/produits";
            }
            elseif($classe_debit >= 7 && $classe_debit <=8) {
                $error = "❌ ERREUR SYSCOHADA: Un compte de produit (Classe 7) ou engagement (Classe 8) ne peut pas être au débit";
            }
            elseif($classe_credit >= 6 && $classe_credit <=6) {
                $error = "❌ ERREUR SYSCOHADA: Un compte de charge (Classe 6) ne peut pas être au crédit";
            }
            else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$date, $libelle, $debit_id, $credit_id, $montant, $ref, $_SESSION['user_id']]);
                    $success = "✅ Écriture validée - Conforme SYSCOHADA UEMOA";
                } catch (Exception $e) {
                    $error = "❌ ERREUR: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pencil-square"></i> Saisie d'écriture contrôlée - SYSCOHADA</h5>
                <small>Validation automatique selon les règles OHADA</small>
            </div>
            <div class="card-body">
                <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                
                <form method="POST" class="row g-3">
                    <div class="col-md-3"><label>Date</label><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="col-md-3"><label>Référence</label><input type="text" name="ref" class="form-control" placeholder="FACT-001"></div>
                    <div class="col-md-6"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                    <div class="col-md-3"><label>Compte débit</label><select name="debite" class="form-select" required><option value="">Sélectionner</option><?php foreach($comptes as $c): ?><option value="<?= $c['compte_id'] ?>"><?= $c['compte_id'] ?> - <?= htmlspecialchars($c['intitule_compte']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label>Compte crédit</label><select name="credite" class="form-select" required><option value="">Sélectionner</option><?php foreach($comptes as $c): ?><option value="<?= $c['compte_id'] ?>"><?= $c['compte_id'] ?> - <?= htmlspecialchars($c['intitule_compte']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" step="1" required></div>
                    <div class="col-md-3"><button type="submit" class="btn-omega w-100 mt-4">Valider</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
