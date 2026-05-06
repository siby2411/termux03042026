<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Amortissements SYSCOHADA";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Calcul automatique des amortissements pour nouvel investissement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $compte_immo = (int)$_POST['compte_immo'];
    $libelle = trim($_POST['libelle']);
    $date_acq = $_POST['date_acquisition'];
    $valeur = (float)$_POST['valeur'];
    $duree = (int)$_POST['duree'];
    $type_amort = $_POST['type_amort'] ?? 'LINEAIRE';
    
    // Calcul du taux
    $taux = 100 / $duree;
    $compte_amort = $compte_immo + 100; // Convention comptable SYSCOHADA
    
    if ($valeur > 0 && $duree > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO AMORTISSEMENTS (compte_immobilisation, compte_amortissement, libelle, date_acquisition, valeur_originale, duree_ans, type_amort, taux, exercice_en_cours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$compte_immo, $compte_amort, $libelle, $date_acq, $valeur, $duree, $type_amort, $taux, date('Y')]);
            $message = "✅ Immobilisation enregistrée. Amortissement linéaire calculé sur $duree ans (taux $taux%)";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

// Récupération des immobilisations non encore enregistrées dans AMORTISSEMENTS
$immo_non_amorties = $pdo->query("
    SELECT DISTINCT e.compte_debite_id as compte_id, c.intitule_compte, SUM(e.montant) as total
    FROM ECRITURES_COMPTABLES e
    JOIN PLAN_COMPTABLE_UEMOA c ON e.compte_debite_id = c.compte_id
    WHERE e.compte_debite_id BETWEEN 200 AND 299
    AND NOT EXISTS (SELECT 1 FROM AMORTISSEMENTS a WHERE a.compte_immobilisation = e.compte_debite_id)
    GROUP BY e.compte_debite_id, c.intitule_compte
")->fetchAll();

// Récupération des amortissements existants
$amortissements = $pdo->query("
    SELECT a.*, 
           (valeur_originale * taux / 100) as annuite,
           amortissement_cumule,
           (valeur_originale - amortissement_cumule) as valeur_nette
    FROM AMORTISSEMENTS a
    ORDER BY a.date_acquisition DESC
")->fetchAll();

$total_valeur_brute = array_sum(array_column($amortissements, 'valeur_originale'));
$total_amort_cumule = array_sum(array_column($amortissements, 'amortissement_cumule'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-building"></i> Gestion des Amortissements - SYSCOHADA</h5>
                <small>Conformément à l'Acte Uniforme OHADA</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lista">Liste des amortissements</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nouveau">Nouvelle immobilisation</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#immo_manquantes">Immobilisations existantes</button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3">
                    <!-- Onglet Liste -->
                    <div class="tab-pane fade show active" id="lista">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr class="text-center">
                                        <th>N° Cpt</th>
                                        <th>Libellé</th>
                                        <th>Date acq.</th>
                                        <th>Valeur brute</th>
                                        <th>Taux</th>
                                        <th>Annuite</th>
                                        <th>Amort. cumulé</th>
                                        <th>Valeur nette</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($amortissements as $a): ?>
                                    <tr>
                                        <td class="text-center"><?= $a['compte_immobilisation'] ?></td>
                                        <td><?= htmlspecialchars($a['libelle']) ?></td>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($a['date_acquisition'])) ?></td>
                                        <td class="text-end"><?= number_format($a['valeur_originale'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center"><?= $a['taux'] ?>%</td>
                                        <td class="text-end"><?= number_format(($a['valeur_originale'] * $a['taux'] / 100), 0, ',', ' ') ?> F</td>
                                        <td class="text-end text-danger"><?= number_format($a['amortissement_cumule'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end fw-bold text-primary"><?= number_format($a['valeur_nette'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">TOTAUX :</td>
                                        <td class="text-end"><?= number_format($total_valeur_brute, 0, ',', ' ') ?> F</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td class="text-end"><?= number_format($total_amort_cumule, 0, ',', ' ') ?> F</td>
                                        <td class="text-end fw-bold text-primary"><?= number_format($total_valeur_brute - $total_amort_cumule, 0, ',', ' ') ?> F</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Onglet Nouvelle immobilisation -->
                    <div class="tab-pane fade" id="nouveau">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="add">
                            <div class="col-md-6">
                                <label>Compte immobilisation</label>
                                <select name="compte_immo" class="form-select" required>
                                    <option value="">Sélectionner</option>
                                    <option value="231">231 - Constructions</option>
                                    <option value="241">241 - Matériel informatique</option>
                                    <option value="245">245 - Mobilier</option>
                                    <option value="253">253 - Véhicules</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Libellé</label>
                                <input type="text" name="libelle" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label>Date acquisition</label>
                                <input type="date" name="date_acquisition" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label>Valeur (FCFA)</label>
                                <input type="number" name="valeur" class="form-control" step="1000" required>
                            </div>
                            <div class="col-md-4">
                                <label>Durée (ans)</label>
                                <select name="duree" class="form-select" required>
                                    <option value="3">3 ans (33.33%)</option>
                                    <option value="4">4 ans (25%)</option>
                                    <option value="5" selected>5 ans (20%)</option>
                                    <option value="10">10 ans (10%)</option>
                                    <option value="15">15 ans (6.67%)</option>
                                    <option value="20">20 ans (5%)</option>
                                </select>
                            </div>
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn-omega">Enregistrer l'immobilisation</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Onglet Immobilisations existantes -->
                    <div class="tab-pane fade" id="immo_manquantes">
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> Ces immobilisations sont présentes dans les écritures mais pas dans le tableau d'amortissement
                        </div>
                        <div class="row">
                            <?php foreach($immo_non_amorties as $immo): ?>
                            <div class="col-md-6">
                                <div class="card bg-light mb-2">
                                    <div class="card-body">
                                        <strong>Compte <?= $immo['compte_id'] ?></strong><br>
                                        <?= htmlspecialchars($immo['intitule_compte']) ?><br>
                                        Valeur: <?= number_format($immo['total'], 0, ',', ' ') ?> FCFA
                                        <button class="btn btn-sm btn-primary float-end" onclick="remplirFormulaire(<?= $immo['compte_id'] ?>, '<?= addslashes($immo['intitule_compte']) ?>', <?= $immo['total'] ?>)">
                                            <i class="bi bi-plus"></i> Amortir
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function remplirFormulaire(compte, libelle, valeur) {
    document.querySelector('[name="compte_immo"]').value = compte;
    document.querySelector('[name="libelle"]').value = libelle;
    document.querySelector('[name="valeur"]').value = valeur;
    document.querySelector('[data-bs-target="#nouveau"]').click();
}
</script>

<?php include 'inc_footer.php'; ?>
