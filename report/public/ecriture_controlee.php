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
// Récupération des centres analytiques
$centres = $pdo->query("SELECT * FROM CENTRES_ANALYTIQUES ORDER BY type_centre, code")->fetchAll();

// Définition des comptes par type de journal (AUTOMATIQUE)
$comptes_par_journal = [
    'AC' => ['debit' => 601, 'credit' => 401, 'debit_lib' => 'Achats de marchandises', 'credit_lib' => 'Fournisseurs'],
    'VE' => ['debit' => 411, 'credit' => 701, 'debit_lib' => 'Clients', 'credit_lib' => 'Ventes de marchandises'],
    'BK' => ['debit' => 521, 'credit' => 701, 'debit_lib' => 'Banque', 'credit_lib' => 'Ventes'],
    'CK' => ['debit' => 571, 'credit' => 701, 'debit_lib' => 'Caisse', 'credit_lib' => 'Ventes'],
    'OD' => ['debit' => 601, 'credit' => 521, 'debit_lib' => 'Achats', 'credit_lib' => 'Banque'],
    'SI' => ['debit' => 112, 'credit' => 120, 'debit_lib' => 'Report à nouveau', 'credit_lib' => 'Résultat'],
];

// Variables pour l'affichage dynamique
$selected_journal_id = isset($_POST['journal_id']) ? (int)$_POST['journal_id'] : 0;
$selected_journal_code = '';
$compte_debit_auto = '';
$compte_credit_auto = '';

if ($selected_journal_id > 0) {
    $stmt = $pdo->prepare("SELECT code FROM JOURNAUX WHERE id = ?");
    $stmt->execute([$selected_journal_id]);
    $journal = $stmt->fetch();
    if ($journal) {
        $selected_journal_code = $journal['code'];
        if (isset($comptes_par_journal[$selected_journal_code])) {
            $compte_debit_auto = $comptes_par_journal[$selected_journal_code]['debit'];
            $compte_credit_auto = $comptes_par_journal[$selected_journal_code]['credit'];
        }
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider'])) {
    $date = $_POST['date'];
    $libelle = trim($_POST['libelle']);
    $montant = (float)$_POST['montant'];
    $ref = trim($_POST['ref']);
    $journal_id = (int)$_POST['journal_id'];
    $centre_id = !empty($_POST['centre_id']) ? (int)$_POST['centre_id'] : null;
    
    // Récupérer le code du journal
    $stmt = $pdo->prepare("SELECT code FROM JOURNAUX WHERE id = ?");
    $stmt->execute([$journal_id]);
    $journal = $stmt->fetch();
    
    if (!$journal) {
        $error = "❌ Journal non trouvé";
    } else {
        $journal_code = $journal['code'];
        
        // Récupérer les comptes automatiques
        if (!isset($comptes_par_journal[$journal_code])) {
            $error = "❌ Configuration comptable manquante pour ce journal";
        } elseif ($montant <= 0) {
            $error = "❌ Le montant doit être supérieur à zéro";
        } else {
            $comptes = $comptes_par_journal[$journal_code];
            $compte_debit = $comptes['debit'];
            $compte_credit = $comptes['credit'];
            
            try {
                $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, journal_id, section_analytique_id, user_id, type_ecriture) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'COURANTE')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$date, $libelle, $compte_debit, $compte_credit, $montant, $ref, $journal_id, $centre_id, $_SESSION['user_id']]);
                $success = "✅ Écriture validée - Journal: $journal_code | Débit: $compte_debit | Crédit: $compte_credit";
            } catch (Exception $e) {
                $error = "❌ ERREUR: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pencil-square"></i> Saisie d'écriture comptable</h5>
                <small>Comptabilité générale - Règles SYSCOHADA</small>
            </div>
            <div class="card-body">
                <?php if($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>Référence</label>
                            <input type="text" name="ref" class="form-control" placeholder="FACT-001">
                        </div>
                        <div class="col-md-4">
                            <label>Journal</label>
                            <select name="journal_id" id="journal_select" class="form-select" required onchange="this.form.submit()">
                                <option value="">-- Sélectionner un journal --</option>
                                <?php foreach($journaux as $j): ?>
                                    <option value="<?= $j['id'] ?>" <?= $selected_journal_id == $j['id'] ? 'selected' : '' ?>>
                                        <?= $j['code'] ?> - <?= htmlspecialchars($j['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label>Libellé</label>
                            <input type="text" name="libelle" class="form-control" placeholder="Description de l'opération..." required>
                        </div>
                        
                        <!-- Affichage des comptes automatiques -->
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="fw-bold text-danger">
                                    <i class="bi bi-arrow-down-circle"></i> Compte DÉBIT
                                </label>
                                <?php if($selected_journal_code && isset($comptes_par_journal[$selected_journal_code])): ?>
                                    <div class="alert alert-danger mt-2 mb-0">
                                        <strong><?= $comptes_par_journal[$selected_journal_code]['debit'] ?></strong><br>
                                        <small><?= $comptes_par_journal[$selected_journal_code]['debit_lib'] ?></small>
                                        <input type="hidden" name="debite" value="<?= $comptes_par_journal[$selected_journal_code]['debit'] ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-secondary mt-2 mb-0">
                                        Sélectionnez un journal
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="fw-bold text-success">
                                    <i class="bi bi-arrow-up-circle"></i> Compte CRÉDIT
                                </label>
                                <?php if($selected_journal_code && isset($comptes_par_journal[$selected_journal_code])): ?>
                                    <div class="alert alert-success mt-2 mb-0">
                                        <strong><?= $comptes_par_journal[$selected_journal_code]['credit'] ?></strong><br>
                                        <small><?= $comptes_par_journal[$selected_journal_code]['credit_lib'] ?></small>
                                        <input type="hidden" name="credite" value="<?= $comptes_par_journal[$selected_journal_code]['credit'] ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-secondary mt-2 mb-0">
                                        Sélectionnez un journal
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label>Section analytique (optionnel)</label>
                            <select name="centre_id" class="form-select">
                                <option value="">-- Aucune --</option>
                                <?php foreach($centres as $c): ?>
                                    <option value="<?= $c['id'] ?>">[<?= $c['type_centre'] ?>] <?= $c['code'] ?> - <?= $c['libelle'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label>Montant (FCFA)</label>
                            <input type="number" name="montant" class="form-control" step="1" placeholder="0" required>
                        </div>
                        
                        <div class="col-12 text-center mt-3">
                            <button type="submit" name="valider" class="btn-omega px-5 py-2">
                                <i class="bi bi-check-lg"></i> Valider l'écriture
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Légende des journaux et comptes associés -->
                <div class="alert alert-info mt-4">
                    <strong>📋 Correspondance Journal → Comptes :</strong>
                    <div class="row mt-2">
                        <?php foreach($comptes_par_journal as $code => $c): ?>
                        <div class="col-md-4">
                            <span class="badge bg-primary"><?= $code ?></span>
                            → Débit: <strong><?= $c['debit'] ?></strong> (<?= $c['debit_lib'] ?>)<br>
                            &nbsp;&nbsp;&nbsp;Crédit: <strong><?= $c['credit'] ?></strong> (<?= $c['credit_lib'] ?>)
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
