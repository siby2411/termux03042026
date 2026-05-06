<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$page_title = "Facturation - Module avancé";
$page_icon = "file-invoice";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Génération automatique du numéro de facture
$last_facture = $pdo->query("SELECT MAX(SUBSTRING(reference_piece, 5)) as last FROM ECRITURES_COMPTABLES WHERE reference_piece LIKE 'FACT%'")->fetchColumn();
$new_num = str_pad(($last_facture + 1), 4, '0', STR_PAD_LEFT);
$ref_facture = "FACT-" . date('Y') . "-" . $new_num;

$message = '';
$clients = [
    1001 => 'SOPRIM Sénégal',
    1002 => 'CBI - Cabinet Bousso & Associés',
    1003 => 'DER/FJ - Délégation à l\'Entrepreneuriat',
    1004 => 'BOA Sénégal',
    1005 => 'Orange Sénégal',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = (int)$_POST['client'];
    $libelle = trim($_POST['libelle']);
    $montant = (float)$_POST['montant'];
    $date_facture = $_POST['date_facture'];
    
    if ($montant > 0) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece) 
                                   VALUES (?, ?, 411, 703, ?, ?)");
            $stmt->execute([$date_facture, "Facture $ref_facture - $libelle", $montant, $ref_facture]);
            $pdo->commit();
            $message = "✅ Facture $ref_facture générée avec succès pour " . number_format($montant, 0, ',', ' ') . " FCFA";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "❌ Erreur : " . $e->getMessage();
        }
    }
}
?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-invoice"></i> Génération de facture</h5>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Référence facture</label>
                        <input type="text" class="form-control" value="<?= $ref_facture ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Client</label>
                        <select name="client" class="form-select" required>
                            <option value="">Sélectionner un client</option>
                            <?php foreach($clients as $id => $nom): ?>
                                <option value="<?= $id ?>">[<?= $id ?>] <?= $nom ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Date facture</label>
                        <input type="date" name="date_facture" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Libellé prestation</label>
                        <textarea name="libelle" class="form-control" rows="3" required placeholder="Détail de la prestation..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Montant HT (FCFA)</label>
                        <input type="number" name="montant" class="form-control" step="1000" required>
                    </div>
                    <button type="submit" class="btn-omega w-100">Générer la facture</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-list-check"></i> Dernières factures</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Réf</th><th>Date</th><th>Client</th><th class="text-end">Montant</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $factures = $pdo->query("SELECT * FROM ECRITURES_COMPTABLES WHERE reference_piece LIKE 'FACT%' ORDER BY date_ecriture DESC LIMIT 10")->fetchAll();
                        foreach($factures as $f): ?>
                        <tr>
                            <td><?= $f['reference_piece'] ?></td>
                            <td><?= date('d/m/Y', strtotime($f['date_ecriture'])) ?></td>
                            <td><?= substr($f['libelle'], 0, 30) ?>...\n
                            <td class="text-end"><?= number_format($f['montant'], 0, ',', ' ') ?> F</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
