<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Régularisations SYSCOHADA";
$page_icon = "arrow-repeat";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Insertion régularisation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $date_regul = $_POST['date_regul'];
    $libelle = trim($_POST['libelle']);
    $type_regul = $_POST['type_regul'];
    $montant = (float)$_POST['montant'];
    $exercice = $_POST['exercice'] ?? date('Y');
    
    // Détermination des comptes selon SYSCOHADA
    $comptes = [
        'CHARGE_CONSTATE_AVANCE' => ['compte_charge' => 481, 'compte_tiers' => null, 'compte_produit' => null],
        'PRODUIT_CONSTATE_AVANCE' => ['compte_produit' => 482, 'compte_tiers' => null, 'compte_charge' => null],
        'CHARGES_A_PAYER' => ['compte_charge' => 483, 'compte_tiers' => 401, 'compte_produit' => null],
        'PRODUITS_A_RECEVOIR' => ['compte_produit' => 484, 'compte_tiers' => 411, 'compte_charge' => null],
    ];
    
    $compte_charge = $comptes[$type_regul]['compte_charge'] ?? null;
    $compte_produit = $comptes[$type_regul]['compte_produit'] ?? null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO REGULARISATIONS (date_regul, libelle, compte_charge_id, compte_produit_id, montant, type_regul, exercice) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$date_regul, $libelle, $compte_charge, $compte_produit, $montant, $type_regul, $exercice]);
        $message = "✅ Régularisation enregistrée avec succès";
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

// Récupération des régularisations
$regularisations = $pdo->query("
    SELECT r.*, 
           CASE 
               WHEN type_regul = 'CHARGE_CONSTATE_AVANCE' THEN 'Charge constatée d''avance'
               WHEN type_regul = 'PRODUIT_CONSTATE_AVANCE' THEN 'Produit constaté d''avance'
               WHEN type_regul = 'CHARGES_A_PAYER' THEN 'Charges à payer'
               WHEN type_regul = 'PRODUITS_A_RECEVOIR' THEN 'Produits à recevoir'
           END as type_libelle
    FROM REGULARISATIONS r
    ORDER BY r.date_regul DESC
")->fetchAll();

$total_regul = array_sum(array_column($regularisations, 'montant'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-repeat"></i> Régularisations - Charges & Produits (SYSCOHADA)</h5>
                <small>Conformément au PCG OHADA - Classe 48</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#liste_regul">Régularisations actives</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nouvelle_regul">Nouvelle régularisation</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contrepassation">Contrepassation auto</button></li>
                </ul>
                
                <div class="tab-content mt-3">
                    <!-- Onglet Liste -->
                    <div class="tab-pane fade show active" id="liste_regul">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Libellé</th>
                                        <th>Exercice</th>
                                        <th class="text-end">Montant</th>
                                        <th>Contrepassation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($regularisations as $r): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($r['date_regul'])) ?>?</td>
                                        <td><span class="badge bg-info"><?= $r['type_libelle'] ?></span></td>
                                        <td><?= htmlspecialchars($r['libelle']) ?></td>
                                        <td class="text-center"><?= $r['exercice'] ?></td>
                                        <td class="text-end"><?= number_format($r['montant'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center">
                                            <?php if($r['contrepassation']): ?>
                                                <span class="badge bg-success">Contrepassé le <?= $r['date_contrepassation'] ?></span>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-warning" onclick="confirmContrepassation(<?= $r['id'] ?>)">
                                                    Contrepasser
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">TOTAL :</td>
                                        <td class="text-end fw-bold"><?= number_format($total_regul, 0, ',', ' ') ?> F</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Onglet Nouvelle régularisation -->
                    <div class="tab-pane fade" id="nouvelle_regul">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="add">
                            <div class="col-md-6">
                                <label>Type de régularisation</label>
                                <select name="type_regul" class="form-select" required>
                                    <option value="CHARGE_CONSTATE_AVANCE">Charge constatée d'avance (Classe 48)</option>
                                    <option value="PRODUIT_CONSTATE_AVANCE">Produit constaté d'avance (Classe 48)</option>
                                    <option value="CHARGES_A_PAYER">Charges à payer (Fournisseurs)</option>
                                    <option value="PRODUITS_A_RECEVOIR">Produits à recevoir (Clients)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Exercice</label>
                                <input type="number" name="exercice" class="form-control" value="<?= date('Y') ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label>Libellé</label>
                                <input type="text" name="libelle" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label>Date régularisation</label>
                                <input type="date" name="date_regul" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label>Montant (FCFA)</label>
                                <input type="number" name="montant" class="form-control" step="1000" required>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn-omega">Enregistrer la régularisation</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Onglet Contrepassation -->
                    <div class="tab-pane fade" id="contrepassation">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong>Principe de contrepassation SYSCOHADA :</strong><br>
                            À l'ouverture de l'exercice suivant, les écritures de régularisation sont automatiquement contrepassées.
                            Cela permet de rétablir les comptes de charges et produits dans leur état initial.
                        </div>
                        <form method="POST" action="contrepassation_action.php" class="row g-3">
                            <div class="col-md-6">
                                <label>Exercice à contrepasser</label>
                                <select name="exercice" class="form-select" required>
                                    <option value="<?= date('Y')-1 ?>">N-1 (<?= date('Y')-1 ?>)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Date de contrepassation</label>
                                <input type="date" name="date_contrepassation" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Confirmer la contrepassation automatique ?')">
                                    <i class="bi bi-arrow-repeat"></i> Contrepasser automatiquement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmContrepassation(id) {
    if(confirm('Confirmer la contrepassation de cette écriture ?')) {
        window.location.href = 'contrepasser.php?id=' + id;
    }
}
</script>

<?php include 'inc_footer.php'; ?>
