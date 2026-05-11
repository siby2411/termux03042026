<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Charges de personnel & Impôts";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// ==================== TRAITEMENT SALAIRES ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'comptabiliser_salaires') {
        $mois = (int)$_POST['mois'];
        $annee = (int)$_POST['annee'];
        $total_brut = (float)$_POST['total_brut'];
        $cnss_employeur = (float)$_POST['cnss_employeur'];
        $ipres_employeur = (float)$_POST['ipres_employeur'];
        $css_employeur = (float)$_POST['css_employeur'];
        $irpp = (float)$_POST['irpp'];
        $net_a_payer = (float)$_POST['net_a_payer'];
        $reference = "PAIE-$annee-$mois";
        $date_ecriture = date('Y-m-d');
        
        try {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES 
                    (?, 'Salaires bruts', 641, 421, ?, ?, 'PAIE'),
                    (?, 'CNSS part employeur', 651, 431, ?, ?, 'PAIE'),
                    (?, 'IPRES part employeur', 652, 432, ?, ?, 'PAIE'),
                    (?, 'CSS part employeur', 653, 433, ?, ?, 'PAIE'),
                    (?, 'IRPP dû', 421, 4442, ?, ?, 'PAIE')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $date_ecriture, $total_brut, $reference,
                $date_ecriture, $cnss_employeur, $reference,
                $date_ecriture, $ipres_employeur, $reference,
                $date_ecriture, $css_employeur, $reference,
                $date_ecriture, $irpp, $reference
            ]);
            $message = "✅ Comptabilisation des salaires effectuée – Net à payer : " . number_format($net_a_payer, 0, ',', ' ') . " FCFA";
        } catch (PDOException $e) {
            $error = "❌ Erreur lors de l'insertion : " . $e->getMessage();
        }
    }
    
    // ==================== GESTION DES IMPÔTS ====================
    if ($_POST['action'] === 'enregistrer_impot') {
        $exercice = (int)$_POST['exercice'];
        $mois = (int)$_POST['mois'];
        $type_impot = $_POST['type_impot'];
        $base_calcul = (float)$_POST['base_calcul'];
        $taux = (float)$_POST['taux'];
        $montant_du = (float)$_POST['montant_du'];
        $date_limite = $_POST['date_limite'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO IMPOTS_MENSUELS (exercice, mois, type_impot, base_calcul, taux, montant_du, date_limite, statut) VALUES (?, ?, ?, ?, ?, ?, ?, 'DU')");
            $stmt->execute([$exercice, $mois, $type_impot, $base_calcul, $taux, $montant_du, $date_limite]);
            $message = "✅ Impôt enregistré (aucune écriture comptable). Cliquez sur 'Payer' pour générer l'écriture.";
        } catch (PDOException $e) {
            $error = "❌ Erreur : " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'payer_impot') {
        $id = (int)$_POST['impot_id'];
        $type_impot = $_POST['type_impot'];
        $montant = (float)$_POST['montant_paye'];
        
        $compte_impot = 0;
        switch ($type_impot) {
            case 'TVA': $compte_impot = 4443; break;
            case 'IS': $compte_impot = 4441; break;
            case 'IRPP': $compte_impot = 4442; break;
            case 'CSS': $compte_impot = 4444; break;
            case 'IPRES': $compte_impot = 4445; break;
            default: $compte_impot = 4441;
        }
        
        try {
            $pdo->beginTransaction();
            $update = $pdo->prepare("UPDATE IMPOTS_MENSUELS SET montant_paye = ?, date_paiement = CURDATE(), statut = 'PAYE' WHERE id = ?");
            $update->execute([$montant, $id]);
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, 521, ?, ?, 'IMPOT')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([date('Y-m-d'), "Paiement $type_impot", $compte_impot, $montant, "IMP-" . date('Ymd')]);
            $pdo->commit();
            $message = "✅ Impôt payé – Écriture comptable générée.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "❌ Erreur : " . $e->getMessage();
        }
    }
}

$impots = $pdo->query("SELECT * FROM IMPOTS_MENSUELS ORDER BY exercice DESC, mois DESC")->fetchAll();
$total_du = array_sum(array_column($impots, 'montant_du'));
$total_paye = array_sum(array_column($impots, 'montant_paye'));
?>

<div class="row">
    <div class="col-md-12">
        <!-- ==================== SECTION SALAIRES ==================== -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-people"></i> Charges de personnel – Comptabilisation mensuelle</h5>
                <small>Salaires, CNSS, IPRES, CSS, IRPP – SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <strong>📊 Schéma de comptabilisation SYSCOHADA :</strong><br>
                    Débit 641 (Salaires) + Débit 651-653 (Charges sociales) / Crédit 421 (Dettes personnel) + Crédit 431-433 (Organismes sociaux) + Crédit 4442 (IRPP)
                </div>

                <form method="POST" class="row g-3" id="salariesForm">
                    <input type="hidden" name="action" value="comptabiliser_salaires">
                    <div class="col-md-2">
                        <label>Mois</label>
                        <select name="mois" class="form-select">
                            <?php for($i=1; $i<=12; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == date('m') ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Année</label>
                        <input type="number" name="annee" class="form-control" value="<?= date('Y') ?>">
                    </div>
                    <div class="col-md-2">
                        <label>Salaires bruts (F)</label>
                        <input type="number" name="total_brut" id="total_brut" class="form-control" step="1000" required>
                    </div>
                    <div class="col-md-2">
                        <label>CNSS employeur (4.5%)</label>
                        <input type="number" name="cnss_employeur" id="cnss_emp" class="form-control" step="100" readonly style="background:#e9ecef">
                    </div>
                    <div class="col-md-2">
                        <label>IPRES employeur (8%)</label>
                        <input type="number" name="ipres_employeur" id="ipres_emp" class="form-control" step="100" readonly style="background:#e9ecef">
                    </div>
                    <div class="col-md-2">
                        <label>CSS employeur (7%)</label>
                        <input type="number" name="css_employeur" id="css_emp" class="form-control" step="100" readonly style="background:#e9ecef">
                    </div>
                    <div class="col-md-3">
                        <label>IRPP estimé (F)</label>
                        <input type="number" name="irpp" id="irpp" class="form-control" step="100" required>
                    </div>
                    <div class="col-md-3">
                        <label>Net à payer (F)</label>
                        <input type="number" name="net_a_payer" id="net_a_payer" class="form-control" step="100" readonly style="background:#e9ecef">
                    </div>
                    <div class="col-md-3">
                        <label>Total charges patronales</label>
                        <input type="text" id="total_charges" class="form-control" readonly>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn-omega mt-4">Comptabiliser</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== SECTION IMPÔTS ==================== -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-receipt"></i> Impôts et taxes – Suivi des déclarations</h5>
                <small>TVA, IS, IRPP, CSS, IPRES</small>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_du, 0, ',', ' ') ?> F</h4>
                                <small>Impôts dus</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_paye, 0, ',', ' ') ?> F</h4>
                                <small>Impôts payés</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#newImpotModal">+ Nouvel impôt</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Exercice</th><th>Période</th><th>Type</th><th>Base (F)</th><th>Taux</th>
                                <th>Montant dû (F)</th><th>Payé (F)</th><th>Échéance</th><th>Statut</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($impots as $i): ?>
                            <tr>
                                <td class="text-center"><?= $i['exercice'] ?> </td>
                                <td class="text-center"><?= $i['mois'] ?>/<?= $i['exercice'] ?> </td>
                                <td><?= $i['type_impot'] ?> </td>
                                <td class="text-end"><?= number_format($i['base_calcul'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= $i['taux'] ?>%</td>
                                <td class="text-end"><?= number_format($i['montant_du'], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($i['montant_paye'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($i['date_limite'])) ?> </td>
                                <td class="text-center"><span class="badge <?= $i['statut'] == 'PAYE' ? 'bg-success' : 'bg-danger' ?>"><?= $i['statut'] ?></span></td>
                                <td class="text-center">
                                    <?php if($i['statut'] != 'PAYE'): ?>
                                        <button class="btn btn-sm btn-success" onclick="payerImpot(<?= $i['id'] ?>, '<?= $i['type_impot'] ?>', <?= $i['montant_du'] ?>)">Payer</button>
                                    <?php else: ?>-<?php endif; ?>
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

<!-- Modal : Nouvel impôt -->
<div class="modal fade" id="newImpotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Enregistrer un impôt / taxe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="enregistrer_impot">
                    <div class="mb-2"><label>Exercice</label><input type="number" name="exercice" class="form-control" value="<?= date('Y') ?>" required></div>
                    <div class="mb-2"><label>Mois</label><select name="mois" class="form-select"><?php for($i=1;$i<=12;$i++): ?><option value="<?= $i ?>"><?= date('F', mktime(0,0,0,$i,1)) ?></option><?php endfor; ?></select></div>
                    <div class="mb-2"><label>Type</label><select name="type_impot" class="form-select"><option>TVA</option><option>IS</option><option>IRPP</option><option>CSS</option><option>IPRES</option></select></div>
                    <div class="mb-2"><label>Base calcul (F)</label><input type="number" name="base_calcul" class="form-control" required></div>
                    <div class="mb-2"><label>Taux (%)</label><input type="number" name="taux" class="form-control" step="0.1" required></div>
                    <div class="mb-2"><label>Montant dû (F)</label><input type="number" name="montant_du" class="form-control" required></div>
                    <div class="mb-2"><label>Date limite</label><input type="date" name="date_limite" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-danger">Enregistrer</button></div>
            </form>
        </div>
    </div>
</div>

<script>
// Calcul automatique des charges patronales et du net à payer
const brutInput = document.getElementById('total_brut');
const irppInput = document.getElementById('irpp');
const cnssEmpSpan = document.getElementById('cnss_emp');
const ipresEmpSpan = document.getElementById('ipres_emp');
const cssEmpSpan = document.getElementById('css_emp');
const netOutput = document.getElementById('net_a_payer');
const totalChargesSpan = document.getElementById('total_charges');

function calculerCharges() {
    let brut = parseFloat(brutInput.value) || 0;
    let cnss_emp = brut * 0.045;
    let ipres_emp = brut * 0.08;
    let css_emp = brut * 0.07;
    let charges_patro = cnss_emp + ipres_emp + css_emp;
    
    cnssEmpSpan.value = cnss_emp.toFixed(0);
    ipresEmpSpan.value = ipres_emp.toFixed(0);
    cssEmpSpan.value = css_emp.toFixed(0);
    totalChargesSpan.value = charges_patro.toFixed(0) + ' F';
}

function calculerNet() {
    let brut = parseFloat(brutInput.value) || 0;
    let cnss_sal = brut * 0.045;
    let ipres_sal = brut * 0.08;
    let css_sal = brut * 0.01;
    let irpp = parseFloat(irppInput.value) || 0;
    let net = brut - cnss_sal - ipres_sal - css_sal - irpp;
    netOutput.value = net.toFixed(0);
}

brutInput.addEventListener('input', function() {
    calculerCharges();
    calculerNet();
});
irppInput.addEventListener('input', calculerNet);

// Initialiser les calculs au chargement
calculerCharges();
calculerNet();

function payerImpot(id, type, montant) {
    let form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="action" value="payer_impot">' +
                     '<input type="hidden" name="impot_id" value="' + id + '">' +
                     '<input type="hidden" name="type_impot" value="' + type + '">' +
                     '<input type="hidden" name="montant_paye" value="' + montant + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include 'inc_footer.php'; ?>
