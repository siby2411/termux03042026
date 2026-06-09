<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Lettrage comptable";
$page_icon = "link";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Récupération des tiers
$tiers = $pdo->query("SELECT * FROM TIERS ORDER BY raison_sociale")->fetchAll();

// Lettrage automatique
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'lettrage_auto') {
        $tiers_id = (int)$_POST['tiers_id'];
        $type = $_POST['type_lettrage'];
        
        // Récupération des factures non lettrées
        $factures = $pdo->prepare("
            SELECT e.id, e.montant, e.date_ecriture, e.libelle 
            FROM ECRITURES_COMPTABLES e
            WHERE e.compte_debite_id = ? AND e.lettrage_id IS NULL
            ORDER BY e.date_ecriture ASC
        ");
        $factures->execute([$type == 'CLIENT' ? 411 : 401]);
        $liste_factures = $factures->fetchAll();
        
        // Récupération des règlements non lettrés
        $reglements = $pdo->prepare("
            SELECT e.id, e.montant, e.date_ecriture, e.libelle 
            FROM ECRITURES_COMPTABLES e
            WHERE e.compte_credite_id = ? AND e.lettrage_id IS NULL
            ORDER BY e.date_ecriture ASC
        ");
        $reglements->execute([$type == 'CLIENT' ? 411 : 401]);
        $liste_reglements = $reglements->fetchAll();
        
        $_SESSION['factures'] = $liste_factures;
        $_SESSION['reglements'] = $liste_reglements;
        $_SESSION['tiers_id'] = $tiers_id;
        $_SESSION['type_lettrage'] = $type;
        
        header("Location: lettrage_detail.php");
        exit();
    }
}

// Calcul des soldes par tiers
$soldes = [];
foreach($tiers as $t) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(CASE WHEN compte_debite_id = ? THEN montant ELSE 0 END), 0) - 
               COALESCE(SUM(CASE WHEN compte_credite_id = ? THEN montant ELSE 0 END), 0) as solde
        FROM ECRITURES_COMPTABLES WHERE (compte_debite_id = ? OR compte_credite_id = ?) AND lettrage_id IS NULL
    ");
    $compte = $t['numero_compte'];
    $stmt->execute([$compte, $compte, $compte, $compte]);
    $soldes[$t['id']] = $stmt->fetchColumn();
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-link"></i> Lettrage comptable</h5>
                <small>Rapprochement automatique des factures et règlements</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📌 Lettrage automatique</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="lettrage_auto">
                                    <div class="col-md-6"><label>Tiers</label>
                                        <select name="tiers_id" class="form-select" required>
                                            <option value="">Sélectionner</option>
                                            <?php foreach($tiers as $t): ?>
                                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['raison_sociale']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6"><label>Type</label>
                                        <select name="type_lettrage" class="form-select" required>
                                            <option value="CLIENT">Client (411)</option><option value="FOURNISSEUR">Fournisseur (401)</option>
                                        </select>
                                    </div>
                                    <div class="col-12"><button type="submit" class="btn-omega w-100">Lancer le lettrage</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Soldes des tiers non lettrés</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light"><tr><th>Tiers</th><th class="text-end">Solde (F)</th><th>Statut</th></tr></thead>
                                <tbody>
                                    <?php foreach($tiers as $t): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['raison_sociale']) ?> </td>
                                        <td class="text-end <?= $soldes[$t['id']] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($soldes[$t['id']], 0, ',', ' ') ?> F
                                         </td>
                                        <td class="text-center">
                                            <?php if(abs($soldes[$t['id']]) < 1): ?>
                                                <span class="badge bg-success">✓ Lettré</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">À lettrer</span>
                                            <?php endif; ?>
                                         </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
