<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') header("Location: login.php");
$page_title = "Agrandir le plan comptable";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $compte_id = (int)$_POST['compte_id'];
    $intitule = trim($_POST['intitule']);
    
    if(!preg_match('/^[1-9][0-9]{0,2}$/', $compte_id)) {
        $message = "❌ Format compte invalide (ex: 521, 701)";
    } elseif(strlen($intitule) < 5) {
        $message = "❌ Intitulé trop court";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO PLAN_COMPTABLE_UEMOA (compte_id, intitule_compte) VALUES (?, ?)");
            $stmt->execute([$compte_id, $intitule]);
            $message = "✅ Compte $compte_id - $intitule ajouté avec succès";
        } catch (Exception $e) {
            $message = "❌ Compte déjà existant ou erreur : " . $e->getMessage();
        }
    }
}

$comptes_existants = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id")->fetchAll();
?>
<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-plus-circle"></i> Ajouter un compte SYSCOHADA</h5>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Numéro de compte (3 chiffres)</label>
                        <input type="number" name="compte_id" class="form-control" placeholder="Ex: 521" required>
                        <small>Classes: 1=Capitaux, 2=Immobilisations, 3=Stocks, 4=Tiers, 5=Trésorerie, 6=Charges, 7=Produits, 8=Amortissements</small>
                    </div>
                    <div class="mb-3">
                        <label>Intitulé du compte</label>
                        <input type="text" name="intitule" class="form-control" placeholder="Banque, Ventes, Achats..." required>
                    </div>
                    <button type="submit" class="btn-omega w-100">Ajouter au plan comptable</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-table"></i> Plan comptable UEMOA existant</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:400px">
                    <table class="table table-sm">
                        <tr><th>Compte</th><th>Intitulé</th></tr>
                        <?php foreach($comptes_existants as $c): ?>
                        <tr><td><?= $c['compte_id'] ?></td><td><?= htmlspecialchars($c['intitule_compte']) ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
