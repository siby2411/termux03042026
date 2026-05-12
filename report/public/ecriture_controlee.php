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

// Récupération des journaux actifs
$journaux = $pdo->query("SELECT * FROM JOURNAUX WHERE actif = 1 ORDER BY code")->fetchAll();
// Récupération des sections analytiques
$sections = $pdo->query("SELECT * FROM SECTIONS_ANALYTIQUES WHERE actif = 1 ORDER BY code")->fetchAll();
// Récupération des comptes
$comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $libelle = trim($_POST['libelle']);
    $debit_id = (int)$_POST['debite'];
    $credit_id = (int)$_POST['credite'];
    $montant = (float)$_POST['montant'];
    $ref = trim($_POST['ref']);
    $journal_id = !empty($_POST['journal_id']) ? (int)$_POST['journal_id'] : null;
    $section_id = !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null;
    
    // Vérifications SYSCOHADA
    if ($montant <= 0) {
        $error = "❌ ERREUR SYSCOHADA: Le montant doit être supérieur à zéro";
    } elseif ($debit_id == $credit_id) {
        $error = "❌ ERREUR SYSCOHADA: Un compte ne peut pas être à la fois débité et crédité";
    } else {
        // Vérifier l'existence des comptes
        $debit_ok = false;
        $credit_ok = false;
        foreach($comptes as $c) {
            if($c['compte_id'] == $debit_id) $debit_ok = true;
            if($c['compte_id'] == $credit_id) $credit_ok = true;
        }
        
        if(!$debit_ok) $error = "❌ ERREUR SYSCOHADA: Le compte débit $debit_id n'existe pas";
        elseif(!$credit_ok) $error = "❌ ERREUR SYSCOHADA: Le compte crédit $credit_id n'existe pas";
        else {
            // Vérifier les classes
            $classe_debit = floor($debit_id / 100);
            $classe_credit = floor($credit_id / 100);
            
            if(($classe_debit >= 6 && $classe_debit <=7) && ($classe_credit >=6 && $classe_credit <=7)) {
                $error = "❌ ERREUR SYSCOHADA: Une écriture ne peut pas être entre deux comptes charges/produits";
            } elseif($classe_debit >= 7) {
                $error = "❌ ERREUR SYSCOHADA: Un compte de produit (Classe 7) ne peut pas être au débit";
            } elseif($classe_credit >= 6 && $classe_credit <= 6) {
                $error = "❌ ERREUR SYSCOHADA: Un compte de charge (Classe 6) ne peut pas être au crédit";
            } else {
                try {
                    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, journal_id, section_analytique_id, user_id, type_ecriture) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'COURANTE')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$date, $libelle, $debit_id, $credit_id, $montant, $ref, $journal_id, $section_id, $_SESSION['user_id']]);
                    $success = "✅ Écriture validée et enregistrée dans le Grand Livre";
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
                <?php if($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" class="row g-3">
                    <div class="col-md-3">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label>Référence</label>
                        <input type="text" name="ref" class="form-control" placeholder="FACT-001">
                    </div>
                    <div class="col-md-6">
                        <label>Libellé</label>
                        <input type="text" name="libelle" class="form-control" placeholder="Description de l'opération..." required>
                    </div>
                    
                    <!-- NOUVEAU CHAMP : JOURNAL -->
                    <div class="col-md-4">
                        <label>📓 Journal <span class="text-muted">(optionnel)</span></label>
                        <select name="journal_id" class="form-select">
                            <option value="">-- Aucun (journal par défaut) --</option>
                            <?php foreach($journaux as $j): ?>
                                <option value="<?= $j['id'] ?>"><?= $j['code'] ?> - <?= htmlspecialchars($j['libelle']) ?> (<?= $j['type_journal'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Sélectionnez le journal d'écriture (AC=Achats, VE=Ventes, BK=Banque...)</small>
                    </div>
                    
                    <!-- NOUVEAU CHAMP : SECTION ANALYTIQUE -->
                    <div class="col-md-4">
                        <label>📊 Section analytique <span class="text-muted">(optionnel)</span></label>
                        <select name="section_id" class="form-select">
                            <option value="">-- Aucune --</option>
                            <?php foreach($sections as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['code'] ?> - <?= htmlspecialchars($s['libelle']) ?> (<?= $s['type_section'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pour suivre la rentabilité par projet/département</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label>💰 Montant (FCFA)</label>
                        <input type="number" name="montant" class="form-control" step="1" placeholder="0" required>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <label class="form-label text-danger fw-bold">
                                <i class="bi bi-arrow-down-circle"></i> Compte Débit
                            </label>
                            <select name="debite" class="form-select" required>
                                <option value="">Sélectionner un compte</option>
                                <?php foreach($comptes as $c): ?>
                                    <option value="<?= $c['compte_id'] ?>"><?= $c['compte_id'] ?> - <?= htmlspecialchars($c['intitule_compte']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <label class="form-label text-success fw-bold">
                                <i class="bi bi-arrow-up-circle"></i> Compte Crédit
                            </label>
                            <select name="credite" class="form-select" required>
                                <option value="">Sélectionner un compte</option>
                                <?php foreach($comptes as $c): ?>
                                    <option value="<?= $c['compte_id'] ?>"><?= $c['compte_id'] ?> - <?= htmlspecialchars($c['intitule_compte']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn-omega px-5 py-2">
                            <i class="bi bi-check-lg"></i> Valider l'écriture
                        </button>
                    </div>
                </form>
                
                <!-- Légende des journaux disponibles -->
                <div class="alert alert-info mt-4">
                    <strong>📋 Journaux disponibles :</strong>
                    <div class="row mt-2">
                        <?php foreach($journaux as $j): ?>
                        <div class="col-md-3">
                            <span class="badge bg-primary"><?= $j['code'] ?></span> - <?= htmlspecialchars($j['libelle']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <strong>📊 Sections analytiques disponibles :</strong>
                    <div class="row mt-2">
                        <?php foreach($sections as $s): ?>
                        <div class="col-md-3">
                            <span class="badge bg-info"><?= $s['code'] ?></span> - <?= htmlspecialchars($s['libelle']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
