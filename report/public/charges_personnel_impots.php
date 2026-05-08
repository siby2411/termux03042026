<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Charges de personnel & Impôts et taxes";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Comptabilisation mensuelle des salaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'comptabiliser_salaires') {
    $mois = $_POST['mois'];
    $annee = $_POST['annee'];
    $total_brut = $_POST['total_brut'];
    $total_cnss = $_POST['total_cnss'];
    $total_ipres = $_POST['total_ipres'];
    $total_css = $_POST['total_css'];
    $total_irpp = $_POST['total_irpp'];
    $net_a_payer = $_POST['net_a_payer'];
    $total_charges = $_POST['total_charges'];
    
    // Écriture comptable de paie
    $ecriture = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES 
        (?, 'Salaires bruts', 641, 421, ?, 'PAIE-$annee-$mois', 'PAIE'),
        (?, 'CNSS part employeur', 651, 431, ?, 'PAIE-$annee-$mois', 'PAIE'),
        (?, 'IPRES part employeur', 652, 432, ?, 'PAIE-$annee-$mois', 'PAIE'),
        (?, 'CSS part employeur', 653, 433, ?, 'PAIE-$annee-$mois', 'PAIE')");
    
    $message = "✅ Comptabilisation des salaires effectuée - Net à payer: " . number_format($net_a_payer, 0, ',', ' ') . " FCFA";
}

// Enregistrement d'un impôt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'enregistrer_impot') {
    $stmt = $pdo->prepare("INSERT INTO IMPOTS_MENSUELS (exercice, mois, type_impot, base_calcul, taux, montant_du, date_limite) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['exercice'], $_POST['mois'], $_POST['type_impot'], $_POST['base_calcul'], $_POST['taux'], $_POST['montant_du'], $_POST['date_limite']]);
    $message = "✅ Impôt enregistré";
}

// Paiement d'un impôt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'payer_impot') {
    $stmt = $pdo->prepare("UPDATE IMPOTS_MENSUELS SET montant_paye = ?, date_paiement = ?, statut = 'PAYE' WHERE id = ?");
    $stmt->execute([$_POST['montant_paye'], date('Y-m-d'), $_POST['impot_id']]);
    
    $ecriture = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'IMPOT')");
    $ecriture->execute([date('Y-m-d'), "Paiement " . $_POST['type_impot'], 4441, 521, $_POST['montant_paye'], "IMP-" . date('Ymd'), 'IMPOT']);
    
    $message = "✅ Impôt payé";
}

$impots = $pdo->query("SELECT * FROM IMPOTS_MENSUELS ORDER BY exercice DESC, mois DESC")->fetchAll();
$total_impots_du = array_sum(array_column($impots, 'montant_du'));
$total_impots_payes = array_sum(array_column($impots, 'montant_paye'));
?>

<div class="row">
    <div class="col-md-12">
        <!-- Salaire Header -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-people"></i> Charges de personnel - Comptabilisation mensuelle</h5>
                <small>Salaires, CNSS, IPRES, CSS, IRPP - SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>📊 Schéma de comptabilisation SYSCOHADA :</strong><br>
                    <code>Débit 641 (Salaires) + Débit 651-653 (Charges sociales) / Crédit 421 (Dettes personnel) + Crédit 431-433 (Organismes sociaux)</code>
                </div>
                
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="comptabiliser_salaires">
                    <div class="col-md-2"><label>Mois</label><select name="mois" class="form-select"><?php for($i=1;$i<=12;$i++): ?><option value="<?= $i ?>" <?= $i==date('m')?'selected':'' ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option><?php endfor; ?></select></div>
                    <div class="col-md-2"><label>Année</label><input type="number" name="annee" value="<?= date('Y') ?>" class="form-control"></div>
                    <div class="col-md-2"><label>Salaires bruts (F)</label><input type="number" name="total_brut" class="form-control" value="3500000" required></div>
                    <div class="col-md-2"><label>CNSS (4.5%)</label><input type="number" name="total_cnss" class="form-control" value="157500"></div>
                    <div class="col-md-2"><label>IPRES (8%)</label><input type="number" name="total_ipres" class="form-control" value="280000"></div>
                    <div class="col-md-2"><label>CSS (7%)</label><input type="number" name="total_css" class="form-control" value="245000"></div>
                    <div class="col-md-3"><label>IRPP (est.)</label><input type="number" name="total_irpp" class="form-control" value="350000"></div>
                    <div class="col-md-3"><label>Net à payer</label><input type="number" name="net_a_payer" class="form-control" value="2467500"></div>
                    <div class="col-md-3"><label>Total charges patronales</label><input type="number" name="total_charges" class="form-control" value="682500"></div>
                    <div class="col-md-3"><button type="submit" class="btn-omega">Comptabiliser</button></div>
                </form>
            </div>
        </div>
        
        <!-- Impôts -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-receipt"></i> Impôts et taxes - Suivi des déclarations</h5>
                <small>TVA, IS, IRPP, CSS, IPRES</small>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4"><div class="card bg-primary text-white text-center"><div class="card-body"><h4><?= number_format($total_impots_du, 0, ',', ' ') ?> F</h4><small>Impôts dus</small></div></div></div>
                    <div class="col-md-4"><div class="card bg-success text-white text-center"><div class="card-body"><h4><?= number_format($total_impots_payes, 0, ',', ' ') ?> F</h4><small>Impôts payés</small></div></div></div>
                    <div class="col-md-4"><div class="card bg-warning text-dark text-center"><div class="card-body"><button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#newImpotModal">+ Nouvel impôt</button></div></div></div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Exercice</th><th>Période</th><th>Type</th><th>Base</th><th>Taux</th><th>Montant dû</th><th>Payé</th><th>Échéance</th><th>Statut</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($impots as $i): ?>
                            <tr>
                                <td class="text-center"><?= $i['exercice'] ?> </td>
                                <td class="text-center"><?= $i['mois'] ?>/<?= $i['exercice'] ?></td>
                                <td><?= $i['type_impot'] ?></td>
                                <td class="text-end"><?= number_format($i['base_calcul'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= $i['taux'] ?>%</td>
                                <td class="text-end"><?= number_format($i['montant_du'], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($i['montant_paye'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($i['date_limite'])) ?></td>
                                <td><span class="badge <?= $i['statut'] == 'PAYE' ? 'bg-success' : 'bg-danger' ?>"><?= $i['statut'] ?></span></td>
                                <td><?php if($i['statut'] != 'PAYE'): ?><button class="btn btn-sm btn-success" onclick="payerImpot(<?= $i['id'] ?>, '<?= $i['type_impot'] ?>', <?= $i['montant_du'] ?>)">Payer</button><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouvel Impôt -->
<div class="modal fade" id="newImpotModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5>➕ Enregistrer un impôt/taxe</h5></div>
<form method="POST"><div class="modal-body"><input type="hidden" name="action" value="enregistrer_impot">
    <div class="row"><div class="col-md-6"><label>Exercice</label><input type="number" name="exercice" class="form-control" value="<?= date('Y') ?>"></div>
    <div class="col-md-6"><label>Mois</label><select name="mois" class="form-select"><?php for($i=1;$i<=12;$i++): ?><option value="<?= $i ?>"><?= date('F', mktime(0,0,0,$i,1)) ?></option><?php endfor; ?></select></div></div>
    <div class="mb-2"><label>Type d'impôt</label><select name="type_impot" class="form-select"><option value="TVA">TVA (18%)</option><option value="IS">IS (25%)</option><option value="IRPP">IRPP</option><option value="CSS">CSS (7%)</option><option value="IPRES">IPRES (16%)</option></select></div>
    <div class="mb-2"><label>Base calcul (FCFA)</label><input type="number" name="base_calcul" class="form-control" required></div>
    <div class="mb-2"><label>Taux (%)</label><input type="number" name="taux" class="form-control" step="0.1" required></div>
    <div class="mb-2"><label>Montant dû (FCFA)</label><input type="number" name="montant_du" class="form-control" required></div>
    <div class="mb-2"><label>Date limite</label><input type="date" name="date_limite" class="form-control" required></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-danger">Enregistrer</button></div></form></div></div></div>

<script>
function payerImpot(id, type, montant) {
    let form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="action" value="payer_impot"><input type="hidden" name="impot_id" value="'+id+'"><input type="hidden" name="montant_paye" value="'+montant+'"><input type="hidden" name="type_impot" value="'+type+'">';
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include 'inc_footer.php'; ?>
