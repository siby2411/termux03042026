<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Dotations aux Amortissements";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Calcul automatique des dotations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'calculer_dotation') {
        $immobilisation_id = (int)$_POST['immobilisation_id'];
        $exercice = (int)$_POST['exercice'];
        $type_calcul = $_POST['type_calcul'];
        
        // Récupérer l'immobilisation
        $stmt = $pdo->prepare("SELECT * FROM AMORTISSEMENTS WHERE id = ?");
        $stmt->execute([$immobilisation_id]);
        $immo = $stmt->fetch();
        
        if ($immo) {
            $valeur_originale = $immo['valeur_originale'];
            $taux = $immo['taux'];
            $amortissement_cumule = $immo['amortissement_cumule'];
            $duree_ans = $immo['duree_ans'];
            $date_acquisition = new DateTime($immo['date_acquisition']);
            $annee_acquisition = $date_acquisition->format('Y');
            
            // Calcul de l'annuité selon la méthode
            if ($type_calcul === 'LINEAIRE') {
                $annuite = ($valeur_originale * $taux) / 100;
            } elseif ($type_calcul === 'DEGRESSIF') {
                $coefficient = ($duree_ans <= 4) ? 1.5 : (($duree_ans <= 6) ? 2 : 2.5);
                $taux_degressif = $taux * $coefficient;
                $annuite = ($valeur_originale - $amortissement_cumule) * ($taux_degressif / 100);
                if ($duree_ans <= 2) $annuite = ($valeur_originale - $amortissement_cumule) / $duree_ans;
            } else {
                $annuite = ($valeur_originale - $amortissement_cumule) / ($duree_ans - ($exercice - $annee_acquisition));
            }
            
            // Enregistrement de la dotation
            $stmt = $pdo->prepare("INSERT INTO DOTATIONS_AMORTISSEMENTS (immobilisation_id, date_dotation, exercice, montant_dotation, amortissement_cumule, valeur_nette_comptable, type_calcul) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $immobilisation_id,
                date('Y-m-d'),
                $exercice,
                $annuite,
                $amortissement_cumule + $annuite,
                $valeur_originale - ($amortissement_cumule + $annuite),
                $type_calcul
            ]);
            
            // Création de l'écriture comptable
            $compte_immo = $immo['compte_immobilisation'];
            $compte_amort = $compte_immo + 100; // Convention SYSCOHADA
            
            $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'DOTATION')");
            $stmt->execute([
                date('Y-m-d'),
                "Dotation aux amortissements $type_calcul - " . $immo['libelle'] . " - Exercice $exercice",
                681,
                $compte_amort,
                $annuite,
                "DOT-$exercice-" . $immo['compte_immobilisation']
            ]);
            
            $message = "✅ Dotation aux amortissements de " . number_format($annuite, 0, ',', ' ') . " FCFA enregistrée";
        }
    }
}

// Récupération des dotations existantes
$dotations = $pdo->query("
    SELECT d.*, a.libelle, a.compte_immobilisation, a.valeur_originale
    FROM DOTATIONS_AMORTISSEMENTS d
    JOIN AMORTISSEMENTS a ON d.immobilisation_id = a.id
    ORDER BY d.exercice DESC
")->fetchAll();

$immobilisations = $pdo->query("
    SELECT * FROM AMORTISSEMENTS WHERE statut = 'ACTIF'
")->fetchAll();

$total_dotations = array_sum(array_column($dotations, 'montant_dotation'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator-fill"></i> Dotations aux Amortissements</h5>
                <small>Calcul et enregistrement selon les méthodes SYSCOHADA</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <!-- Méthodes d'amortissement -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light text-center">
                            <div class="card-body">
                                <i class="bi bi-arrow-right fs-2 text-primary"></i>
                                <h6>Méthode Linéaire</h6>
                                <small>Annuité constante sur toute la durée</small>
                                <p class="mt-2"><code>Annuité = Valeur brute × Taux / 100</code></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light text-center">
                            <div class="card-body">
                                <i class="bi bi-graph-down fs-2 text-warning"></i>
                                <h6>Méthode Dégressive</h6>
                                <small>Annuité décroissante</small>
                                <p class="mt-2"><code>Annuité = VNC × Taux dégressif / 100</code></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light text-center">
                            <div class="card-body">
                                <i class="bi bi-calculator fs-2 text-success"></i>
                                <h6>Méthode Dérogatoire</h6>
                                <small>Dépassement exceptionnel</small>
                                <p class="mt-2"><code>Écart linéaire/dégressif</code></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire de calcul -->
                <div class="card bg-light mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-plus-circle"></i> Nouvelle dotation
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="calculer_dotation">
                            <div class="col-md-5">
                                <label>Immobilisation</label>
                                <select name="immobilisation_id" class="form-select" required>
                                    <option value="">Sélectionner une immobilisation</option>
                                    <?php foreach($immobilisations as $i): ?>
                                    <option value="<?= $i['id'] ?>">
                                        <?= $i['libelle'] ?> - Valeur: <?= number_format($i['valeur_originale'], 0, ',', ' ') ?> F
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Exercice</label>
                                <select name="exercice" class="form-select">
                                    <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                                    <option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Méthode de calcul</label>
                                <select name="type_calcul" class="form-select">
                                    <option value="LINEAIRE">Linéaire (Recommandé SYSCOHADA)</option>
                                    <option value="DEGRESSIF">Dégressif</option>
                                    <option value="DEROGATOIRE">Dérogatoire</option>
                                </select>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn-omega">
                                    <i class="bi bi-calculator"></i> Calculer et enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Historique des dotations -->
                <h6><i class="bi bi-clock-history"></i> Historique des dotations</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Immobilisation</th>
                                <th>Exercice</th>
                                <th>Méthode</th>
                                <th>Montant dotation</th>
                                <th>Amort. cumulé</th>
                                <th>VNC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($dotations as $d): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($d['date_dotation'])) ?></td>
                                <td><?= htmlspecialchars($d['libelle']) ?> (<?= $d['compte_immobilisation'] ?>)</td>
                                <td class="text-center"><?= $d['exercice'] ?></td>
                                <td class="text-center"><?= $d['type_calcul'] ?></td>
                                <td class="text-end text-danger"><?= number_format($d['montant_dotation'], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($d['amortissement_cumule'], 0, ',', ' ') ?> F</td>
                                <td class="text-end text-primary"><?= number_format($d['valeur_nette_comptable'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr><td colspan="4" class="text-end fw-bold">TOTAL DOTATIONS :</td>
                                <td class="text-end fw-bold"><?= number_format($total_dotations, 0, ',', ' ') ?> F</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
