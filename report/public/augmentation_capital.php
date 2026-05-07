<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Augmentation de Capital";
$page_icon = "bank";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Récupération des données financières
$capitaux_propres = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 101 AND 199")->fetchColumn();
$dettes_total = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 40 AND 49")->fetchColumn();
$capital_actuel = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 101 AND type_ecriture != 'AUGMENTATION_CAPITAL'")->fetchColumn();

$ratio_autonomie = $capitaux_propres / max(($capitaux_propres + $dettes_total), 1) * 100;
$condition_credit = ($capitaux_propres >= $dettes_total);

// Traitement augmentation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant_augmentation = (float)$_POST['montant'];
    $modalite = $_POST['modalite'];
    $date_augmentation = $_POST['date_augmentation'];
    
    if ($montant_augmentation > 0) {
        // Écriture comptable d'augmentation
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'AUGMENTATION_CAPITAL')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $date_augmentation,
            "Augmentation de capital - $modalite",
            521,  // Banque
            101,  // Capital
            $montant_augmentation,
            "AUG-CAP-" . date('Ymd')
        ]);
        
        $message = "✅ Augmentation de capital de " . number_format($montant_augmentation, 0, ',', ' ') . " FCFA enregistrée";
        
        // Mise à jour des variables
        $capital_actuel += $montant_augmentation;
        $capitaux_propres += $montant_augmentation;
        $ratio_autonomie = $capitaux_propres / max(($capitaux_propres + $dettes_total), 1) * 100;
        $condition_credit = ($capitaux_propres >= $dettes_total);
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-bank"></i> Augmentation de Capital</h5>
                <small>Renforcement des fonds propres selon SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <!-- Situation financière -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-bank fs-2"></i>
                                <h4><?= number_format($capital_actuel, 0, ',', ' ') ?> F</h4>
                                <small>Capital social actuel</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-piggy-bank fs-2"></i>
                                <h4><?= number_format($capitaux_propres, 0, ',', ' ') ?> F</h4>
                                <small>Capitaux propres totaux</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <i class="bi bi-pie-chart fs-2"></i>
                                <h4><?= number_format($ratio_autonomie, 1) ?>%</h4>
                                <small>Ratio d'autonomie financière</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Condition de crédit -->
                <div class="alert <?= $condition_credit ? 'alert-success' : 'alert-danger' ?> mb-4">
                    <i class="bi bi-shield-check"></i>
                    <strong>Condition de crédit SYSCOHADA :</strong>
                    <?php if($condition_credit): ?>
                        ✅ CAPITAUX PROPRES (<?= number_format($capitaux_propres, 0, ',', ' ') ?> F) ≥ DETTES (<?= number_format($dettes_total, 0, ',', ' ') ?> F)
                        → L'entreprise est éligible à un crédit bancaire
                    <?php else: ?>
                        ❌ CAPITAUX PROPRES (<?= number_format($capitaux_propres, 0, ',', ' ') ?> F) &lt; DETTES (<?= number_format($dettes_total, 0, ',', ' ') ?> F)
                        → Augmentation de capital recommandée avant demande de crédit
                    <?php endif; ?>
                </div>
                
                <!-- Formulaire augmentation -->
                <div class="card bg-light">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-plus-circle"></i> Nouvelle augmentation de capital
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label>Montant de l'augmentation (FCFA)</label>
                                <input type="number" name="montant" class="form-control" step="100000" required>
                            </div>
                            <div class="col-md-6">
                                <label>Modalité</label>
                                <select name="modalite" class="form-select" required>
                                    <option value="Apport en numéraire">Apport en numéraire (espèces)</option>
                                    <option value="Apport en nature">Apport en nature</option>
                                    <option value="Incorporation réserves">Incorporation de réserves</option>
                                    <option value="Conversion dettes">Conversion de dettes en capital</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Date d'effet</label>
                                <input type="date" name="date_augmentation" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label>Référence légale</label>
                                <input type="text" name="reference" class="form-control" placeholder="Délibération AGE n°, etc.">
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn-omega">
                                    <i class="bi bi-arrow-up-circle"></i> Procéder à l'augmentation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Impacts de l'augmentation -->
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>📊 Impacts d'une augmentation de capital :</strong>
                    <ul class="mt-2 mb-0">
                        <li>✅ Augmentation des fonds propres</li>
                        <li>✅ Amélioration du ratio d'autonomie financière</li>
                        <li>✅ Meilleure capacité d'emprunt (condition capitaux propres ≥ dettes)</li>
                        <li>✅ Renforcement de la crédibilité auprès des partenaires</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
