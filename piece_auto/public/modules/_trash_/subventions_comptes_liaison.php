<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Subventions et Comptes de liaison (Classe 58)";
$page_icon = "arrow-left-right";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// ==================== SUBVENTION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'enregistrer_subvention') {
        $date = $_POST['date_subvention'];
        $libelle = trim($_POST['libelle']);
        $montant = (float)$_POST['montant'];
        $compte_subvention = $_POST['type_subvention'] == 'EQUIPEMENT' ? 109 : 131;
        
        try {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, ?, ?, ?, 'SUBVENTION')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date, "Subvention - $libelle", $compte_subvention, $montant, "SUB-" . date('Ymd')]);
            $message = "✅ Subvention enregistrée - Montant: " . number_format($montant, 0, ',', ' ') . " FCFA";
        } catch (Exception $e) { $error = "Erreur: " . $e->getMessage(); }
    }
    
    // ==================== VIREMENT INTERNE (Classe 58) ====================
    if ($_POST['action'] === 'effectuer_virement') {
        $date = $_POST['date_virement'];
        $libelle = trim($_POST['libelle_virement']);
        $montant = (float)$_POST['montant_virement'];
        $compte_debit = (int)$_POST['compte_debit'];
        $compte_credit = (int)$_POST['compte_credit'];
        
        try {
            $pdo->beginTransaction();
            
            // Écriture 1 : Débit 581 (Virements internes) / Crédit compte source
            $sql1 = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 581, ?, ?, ?, 'VIREMENT')";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([$date, "Virement interne - $libelle (sortie)", $compte_credit, $montant, "VIR-" . date('Ymd')]);
            
            // Écriture 2 : Débit compte destination / Crédit 581 (Virements internes)
            $sql2 = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, 581, ?, ?, 'VIREMENT')";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([$date, "Virement interne - $libelle (entrée)", $compte_debit, $montant, "VIR-" . date('Ymd')]);
            
            $pdo->commit();
            $message = "✅ Virement interne effectué : " . number_format($montant, 0, ',', ' ') . " FCFA";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "❌ Erreur virement: " . $e->getMessage();
        }
    }
}

// Récupération des données
$subventions = $pdo->query("SELECT * FROM ECRITURES_COMPTABLES WHERE type_ecriture = 'SUBVENTION' ORDER BY date_ecriture DESC")->fetchAll();
$virements = $pdo->query("SELECT * FROM ECRITURES_COMPTABLES WHERE type_ecriture = 'VIREMENT' ORDER BY date_ecriture DESC LIMIT 20")->fetchAll();
$total_subventions = array_sum(array_column($subventions, 'montant'));
$total_virements = array_sum(array_column($virements, 'montant'));
?>

<div class="row">
    <div class="col-md-12">
        <!-- ==================== SUBVENTIONS ==================== -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-gift"></i> Subventions (Classe 13)</h5>
                <small>Subventions d'équipement et d'exploitation</small>
            </div>
            <div class="card-body">
                <?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                
                <div class="row">
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">Nouvelle subvention</div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="enregistrer_subvention">
                                    <div class="mb-2"><label>Date</label><input type="date" name="date_subvention" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                                    <div class="mb-2"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                                    <div class="mb-2"><label>Type</label><select name="type_subvention" class="form-select"><option value="EQUIPEMENT">Subvention d'équipement (compte 109)</option><option value="EXPLOITATION">Subvention d'exploitation (compte 131)</option></select></div>
                                    <div class="mb-2"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" step="1000" required></div>
                                    <button type="submit" class="btn-omega w-100">Enregistrer la subvention</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6>Historique des subventions</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark"><tr><th>Date</th><th>Libellé</th><th class="text-end">Montant</th><th>Référence</th></tr></thead>
                                <tbody><?php foreach($subventions as $s): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($s['date_ecriture'])) ?></td>
                                    <td><?= htmlspecialchars($s['libelle']) ?></td>
                                    <td class="text-end text-success">+ <?= number_format($s['montant'], 0, ',', ' ') ?> F</td>
                                    <tr><?= $s['reference_piece'] ?></td>
                                </tr><?php endforeach; ?></tbody>
                                <tfoot class="table-secondary"><tr><td colspan="2" class="text-end fw-bold">TOTAL SUBVENTIONS :</td>
                                <td class="text-end fw-bold"><?= number_format($total_subventions, 0, ',', ' ') ?> F</td><td></td></tr></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== VIREMENTS INTERNES (CLASSE 58) ==================== -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-left-right"></i> Virements internes - Classe 58 (Compte 581)</h5>
                <small>Transferts entre comptes bancaires et caisses</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>📖 Schéma comptable SYSCOHADA :</strong><br>
                    <code>Débit 581 (Virements internes) / Crédit compte source</code><br>
                    <code>Débit compte destination / Crédit 581 (Virements internes)</code>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">Effectuer un virement interne</div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="effectuer_virement">
                                    <div class="mb-2"><label>Date virement</label><input type="date" name="date_virement" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                                    <div class="mb-2"><label>Libellé</label><input type="text" name="libelle_virement" class="form-control" placeholder="Ex: Approvisionnement caisse" required></div>
                                    <div class="mb-2"><label>Compte source (débité)</label>
                                        <select name="compte_credit" class="form-select" required>
                                            <option value="521">521 - Banque générale</option>
                                            <option value="5211">5211 - Banque CFA</option>
                                            <option value="511">511 - Banque X</option>
                                            <option value="53">53 - Chèques postaux</option>
                                        </select>
                                    </div>
                                    <div class="mb-2"><label>Compte destination (crédité)</label>
                                        <select name="compte_debit" class="form-select" required>
                                            <option value="541">541 - Caisse centrale</option><option value="581">581 - Virements internes</option></option>
                                            <option value="542">542 - Caisse annexe</option>
                                            <option value="521">521 - Banque générale</option>
                                            <option value="5212">5212 - Banque Euro</option>
                                        </select>
                                    </div>
                                    <div class="mb-2"><label>Montant (FCFA)</label><input type="number" name="montant_virement" class="form-control" step="1000" required></div>
                                    <button type="submit" class="btn-omega w-100"><i class="bi bi-arrow-left-right"></i> Exécuter le virement</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6>Historique des virements internes</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr><th>Date</th><th>Libellé</th><th>Compte débit</th><th>Compte crédit</th><th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $virements_par2 = [];
                                    $temp = [];
                                    foreach($virements as $v) {
                                        if(in_array($v['reference_piece'], $temp)) continue;
                                        $temp[] = $v['reference_piece'];
                                        $virements_par2[] = $v;
                                    }
                                    foreach($virements_par2 as $v): ?>
                                    <tr>
                                        <td class="text-nowrap"><?= date('d/m/Y', strtotime($v['date_ecriture'])) ?></td>
                                        <td><?= htmlspecialchars($v['libelle']) ?></td>
                                        <td class="text-center"><?= $v['compte_debite_id'] ?> <?= ($v['compte_debite_id']==581) ? '(581)' : '' ?></td>
                                        <td class="text-center"><?= $v['compte_credite_id'] ?> <?= ($v['compte_credite_id']==581) ? '(581)' : '' ?></td>
                                        <td class="text-end fw-bold"><?= number_format($v['montant'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-secondary mt-2">
                            <strong>💡 Exemple concret :</strong> Alimentation de la caisse depuis la banque
                            <ul class="mb-0 mt-1">
                                <li><code>Débit 581 - Virements internes / Crédit 521 - Banque</code></li>
                                <li><code>Débit 541 - Caisse / Crédit 581 - Virements internes</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
