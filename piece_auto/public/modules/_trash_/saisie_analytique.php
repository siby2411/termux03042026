<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Saisie analytique - Affectation centres coûts/profits";
$page_icon = "pie-chart";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Récupération des centres analytiques
$centres = $pdo->query("SELECT * FROM CENTRES_ANALYTIQUES ORDER BY type_centre, code")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'saisir_charge') {
        $date = $_POST['date'];
        $libelle = trim($_POST['libelle']);
        $montant = (float)$_POST['montant'];
        $centre_id = (int)$_POST['centre_id'];
        $type_charge = $_POST['type_charge'];
        $ref = trim($_POST['reference']);
        
        // Vérification du montant
        if ($montant <= 0) {
            $error = "❌ Le montant doit être supérieur à 0";
        } elseif ($centre_id == 0) {
            $error = "❌ Veuillez sélectionner un centre analytique";
        } else {
            // Détermination du compte selon le type de charge
            $compte_charge = 0;
            switch($type_charge) {
                case 'ACHAT': $compte_charge = 601; break;
                case 'SALAIRE': $compte_charge = 641; break;
                case 'LOYER': $compte_charge = 613; break;
                case 'ELECTRICITE': $compte_charge = 628; break;
                case 'TRANSPORT': $compte_charge = 625; break;
                case 'FOURNITURES': $compte_charge = 606; break;
                default: $compte_charge = 601;
            }
            
            try {
                // Insertion dans ECRITURES_COMPTABLES
                $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, section_analytique_id, type_ecriture) VALUES (?, ?, ?, 521, ?, ?, ?, 'ANALYTIQUE')");
                $stmt->execute([$date, $libelle, $compte_charge, $montant, $ref, $centre_id]);
                
                $message = "✅ Charge affectée au centre analytique - Montant: " . number_format($montant, 0, ',', ' ') . " FCFA";
            } catch(Exception $e) {
                $error = "Erreur: " . $e->getMessage();
            }
        }
    }
    
    if ($_POST['action'] === 'saisir_produit') {
        $date = $_POST['date'];
        $libelle = trim($_POST['libelle']);
        $montant = (float)$_POST['montant'];
        $centre_id = (int)$_POST['centre_id'];
        $type_produit = $_POST['type_produit'];
        $ref = trim($_POST['reference']);
        
        if ($montant <= 0) {
            $error = "❌ Le montant doit être supérieur à 0";
        } elseif ($centre_id == 0) {
            $error = "❌ Veuillez sélectionner un centre analytique";
        } else {
            // Détermination du compte selon le type de produit
            $compte_produit = 0;
            switch($type_produit) {
                case 'VENTE': $compte_produit = 701; break;
                case 'PRESTATION': $compte_produit = 703; break;
                case 'SERVICE': $compte_produit = 706; break;
                default: $compte_produit = 701;
            }
            
            try {
                $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, section_analytique_id, type_ecriture) VALUES (?, ?, 521, ?, ?, ?, ?, 'ANALYTIQUE')");
                $stmt->execute([$date, $libelle, $compte_produit, $montant, $ref, $centre_id]);
                
                $message = "✅ Produit affecté au centre analytique - Montant: " . number_format($montant, 0, ',', ' ') . " FCFA";
            } catch(Exception $e) {
                $error = "Erreur: " . $e->getMessage();
            }
        }
    }
}

// Récupération des dernières saisies analytiques
$dernieres_saisies = $pdo->query("
    SELECT e.*, c.code as centre_code, c.libelle as centre_libelle
    FROM ECRITURES_COMPTABLES e
    JOIN CENTRES_ANALYTIQUES c ON e.section_analytique_id = c.id
    WHERE e.type_ecriture = 'ANALYTIQUE'
    ORDER BY e.id DESC
    LIMIT 10
")->fetchAll();

// Calcul des totaux par centre
$totaux_centres = [];
foreach($centres as $c) {
    $charges = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE section_analytique_id = ? AND compte_debite_id BETWEEN 600 AND 699 AND type_ecriture = 'ANALYTIQUE'");
    $charges->execute([$c['id']]);
    $total_charges = $charges->fetchColumn();
    
    $produits = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE section_analytique_id = ? AND compte_credite_id BETWEEN 700 AND 799 AND type_ecriture = 'ANALYTIQUE'");
    $produits->execute([$c['id']]);
    $total_produits = $produits->fetchColumn();
    
    $totaux_centres[$c['id']] = [
        'code' => $c['code'],
        'libelle' => $c['libelle'],
        'type' => $c['type_centre'],
        'charges' => $total_charges,
        'produits' => $total_produits,
        'resultat' => $total_produits - $total_charges
    ];
}

$total_charges = array_sum(array_column($totaux_centres, 'charges'));
$total_produits = array_sum(array_column($totaux_centres, 'produits'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pie-chart"></i> Saisie analytique - Centres de coûts/profits</h5>
                <small>Affectez facilement charges et produits aux centres analytiques</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <!-- KPIs -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_charges, 0, ',', ' ') ?> F</h4>
                                <small>Total charges analytiques</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_produits, 0, ',', ' ') ?> F</h4>
                                <small>Total produits analytiques</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_produits - $total_charges, 0, ',', ' ') ?> F</h4>
                                <small>Résultat analytique</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglets -->
                <ul class="nav nav-tabs" id="analytiqueTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#charges">📉 Saisir une charge</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#produits">📈 Saisir un produit</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#resultats">📊 Résultats par centre</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#historique">📋 Historique</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <!-- Onglet Charges -->
                    <div class="tab-pane fade show active" id="charges">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📉 Ajouter une charge</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="saisir_charge">
                                    <div class="col-md-3">
                                        <label>Date</label>
                                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Référence</label>
                                        <input type="text" name="reference" class="form-control" placeholder="FACT-001">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Libellé</label>
                                        <input type="text" name="libelle" class="form-control" placeholder="Description de la charge" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Type de charge</label>
                                        <select name="type_charge" class="form-select">
                                            <option value="ACHAT">Achat de marchandises</option>
                                            <option value="SALAIRE">Salaires</option>
                                            <option value="LOYER">Loyer</option>
                                            <option value="ELECTRICITE">Électricité/Eau</option>
                                            <option value="TRANSPORT">Transport</option>
                                            <option value="FOURNITURES">Fournitures de bureau</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Centre analytique</label>
                                        <select name="centre_id" class="form-select" required>
                                            <option value="">-- Sélectionner un centre --</option>
                                            <?php foreach($centres as $c): ?>
                                                <option value="<?= $c['id'] ?>">[<?= $c['type_centre'] ?>] <?= $c['code'] ?> - <?= $c['libelle'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Montant (FCFA)</label>
                                        <input type="number" name="montant" class="form-control" step="1" placeholder="Ex: 50000" required>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn-omega px-4">
                                            <i class="bi bi-save"></i> Enregistrer la charge
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Produits -->
                    <div class="tab-pane fade" id="produits">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📈 Ajouter un produit</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="saisir_produit">
                                    <div class="col-md-3">
                                        <label>Date</label>
                                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Référence</label>
                                        <input type="text" name="reference" class="form-control" placeholder="FACT-001">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Libellé</label>
                                        <input type="text" name="libelle" class="form-control" placeholder="Description du produit" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Type de produit</label>
                                        <select name="type_produit" class="form-select">
                                            <option value="VENTE">Vente de marchandises</option>
                                            <option value="PRESTATION">Prestation de services</option>
                                            <option value="SERVICE">Service facturé</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Centre analytique</label>
                                        <select name="centre_id" class="form-select" required>
                                            <option value="">-- Sélectionner un centre --</option>
                                            <?php foreach($centres as $c): ?>
                                                <option value="<?= $c['id'] ?>">[<?= $c['type_centre'] ?>] <?= $c['code'] ?> - <?= $c['libelle'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Montant (FCFA)</label>
                                        <input type="number" name="montant" class="form-control" step="1" placeholder="Ex: 100000" required>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn-omega px-4">
                                            <i class="bi bi-save"></i> Enregistrer le produit
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Résultats -->
                    <div class="tab-pane fade" id="resultats">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr class="text-center">
                                        <th>Centre</th><th>Type</th><th class="text-end">Charges (F)</th>
                                        <th class="text-end">Produits (F)</th><th class="text-end">Résultat (F)</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($totaux_centres as $t): ?>
                                    <tr>
                                        <td><strong><?= $t['code'] ?></strong> - <?= $t['libelle'] ?> </td>
                                        <td class="text-center"><span class="badge <?= $t['type'] == 'PROFIT' ? 'bg-success' : 'bg-danger' ?>"><?= $t['type'] ?></span> </td>
                                        <td class="text-end text-danger"><?= number_format($t['charges'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end text-success"><?= number_format($t['produits'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end <?= $t['resultat'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($t['resultat'], 0, ',', ' ') ?> F
                                        </td>
                                        <td class="text-center">
                                            <?php if($t['resultat'] > 0): ?>
                                                <span class="badge bg-success">✅ Rentable</span>
                                            <?php elseif($t['resultat'] < 0): ?>
                                                <span class="badge bg-danger">⚠️ Déficitaire</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">⚖️ Neutre</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr class="fw-bold">
                                        <td colspan="2" class="text-end">TOTAUX :</td>
                                        <td class="text-end"><?= number_format($total_charges, 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= number_format($total_produits, 0, ',', ' ') ?> F</td>
                                        <td class="text-end text-primary"><?= number_format($total_produits - $total_charges, 0, ',', ' ') ?> F</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Onglet Historique -->
                    <div class="tab-pane fade" id="historique">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th><th>Référence</th><th>Libellé</th>
                                        <th>Centre</th><th class="text-end">Montant</th><th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($dernieres_saisies as $s): 
                                        $est_charge = ($s['compte_debite_id'] >= 600 && $s['compte_debite_id'] <= 699);
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($s['date_ecriture'])) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($s['reference_piece'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($s['libelle']) ?></td>
                                        <td class="text-center"><?= $s['centre_code'] ?> - <?= $s['centre_libelle'] ?></td>
                                        <td class="text-end <?= $est_charge ? 'text-danger' : 'text-success' ?>">
                                            <?= number_format($s['montant'], 0, ',', ' ') ?> F
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $est_charge ? 'bg-danger' : 'bg-success' ?>">
                                                <?= $est_charge ? 'CHARGE' : 'PRODUIT' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($dernieres_saisies)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">Aucune saisie analytique pour le moment</td></tr>
                                    <?php endif; ?>
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
