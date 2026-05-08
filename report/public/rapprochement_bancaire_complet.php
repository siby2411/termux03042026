<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Rapprochement Bancaire";
$page_icon = "arrow-left-right";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Encaissement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'encaissement') {
    $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (CURDATE(), ?, 521, ?, ?, ?, 'TRESORERIE')");
    $stmt->execute([$_POST['libelle'], $_POST['compte_credit'], $_POST['montant'], $_POST['reference']]);
    $message = "✅ Encaissement enregistré";
}

// Décaissement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'decaissement') {
    $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (CURDATE(), ?, ?, 521, ?, ?, 'TRESORERIE')");
    $stmt->execute([$_POST['libelle'], $_POST['compte_debit'], $_POST['montant'], $_POST['reference']]);
    $message = "✅ Décaissement enregistré";
}

$operations = $pdo->query("SELECT * FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 521 OR compte_credite_id = 521 ORDER BY date_ecriture DESC LIMIT 20")->fetchAll();
$solde = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_debite_id = 521 THEN montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN compte_credite_id = 521 THEN montant ELSE 0 END), 0) FROM ECRITURES_COMPTABLES")->fetchColumn();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-left-right"></i> Rapprochement bancaire - Compte 521</h5>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>💵 Solde bancaire actuel : <?= number_format($solde, 0, ',', ' ') ?> FCFA</strong>
                </div>

                <ul class="nav nav-tabs">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#encaissement">📥 Encaissement</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#decaissement">📤 Décaissement</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#releve">📋 Relevé bancaire</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="encaissement">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="encaissement">
                            <div class="col-md-6"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                            <div class="col-md-3"><label>Compte crédit</label><select name="compte_credit" class="form-select"><option value="701">701 - Ventes</option><option value="703">703 - Prestations</option></select></div>
                            <div class="col-md-2"><label>Montant</label><input type="number" name="montant" class="form-control" required></div>
                            <div class="col-md-1"><button type="submit" class="btn-omega mt-4">Enregistrer</button></div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="decaissement">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="decaissement">
                            <div class="col-md-6"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                            <div class="col-md-3"><label>Compte débit</label><select name="compte_debit" class="form-select"><option value="601">601 - Achats</option><option value="606">606 - Fournitures</option></select></div>
                            <div class="col-md-2"><label>Montant</label><input type="number" name="montant" class="form-control" required></div>
                            <div class="col-md-1"><button type="submit" class="btn-omega mt-4">Enregistrer</button></div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="releve">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark"><tr><th>Date</th><th>Libellé</th><th>Référence</th><th class="text-success">Encaissement</th><th class="text-danger">Décaissement</th><th>Pointé</th></tr></thead>
                                <tbody>
                                    <?php foreach($operations as $op): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($op['date_ecriture'])) ?></td>
                                        <td><?= $op['libelle'] ?></td>
                                        <td><?= $op['reference_piece'] ?? '-' ?></td>
                                        <td class="text-success"><?= $op['compte_debite_id'] == 521 ? number_format($op['montant'], 0, ',', ' ') . ' F' : '-' ?></td>
                                        <td class="text-danger"><?= $op['compte_credite_id'] == 521 ? number_format($op['montant'], 0, ',', ' ') . ' F' : '-' ?></td>
                                        <td class="text-center"><input type="checkbox"></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-primary mt-2" onclick="alert('Rapprochement effectué')">Valider le pointage</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
