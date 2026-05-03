<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// Traitement du paiement d'un reliquat
if (isset($_POST['payer_reliquat'])) {
    $fac_id = $_POST['facture_id'];
    $montant = $_POST['montant_verse'];
    
    // Mise à jour de la facture
    $stmt = $pdo->prepare("UPDATE factures SET montant_paye = montant_paye + ?, reste = reste - ? WHERE id = ?");
    $stmt->execute([$montant, $montant, $fac_id]);
    
    // Enregistrement du paiement dans le journal
    $f = $pdo->query("SELECT client_id FROM factures WHERE id = $fac_id")->fetch();
    $stmtPay = $pdo->prepare("INSERT INTO paiements (facture_id, client_id, date_paiement, montant, mode_paiement) VALUES (?,?,NOW(),?,'espèces')");
    $stmtPay->execute([$fac_id, $f['client_id'], $montant]);

    setFlash('success', "Paiement de " . number_format($montant,0) . " F enregistré avec succès !");
    header("Location: factures.php"); exit;
}

$factures = $pdo->query("SELECT f.*, cl.nom, cl.prenom FROM factures f JOIN clients cl ON f.client_id = cl.id ORDER BY f.date_facture DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .gold-border { border-left: 5px solid #d4af37 !important; }
    .bg-omega-dark { background-color: #0f172a !important; color: #d4af37; }
</style>

<h4 class="fw-bold mb-4">Gestion des Factures & Reliquats</h4>

<div class="row g-3">
    <?php foreach($factures as $f): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm gold-border h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted"><?= date('d/m/Y', strtotime($f['date_facture'])) ?></span>
                    <span class="fw-bold text-dark"><?= $f['numero_facture'] ?></span>
                </div>
                <h6 class="fw-bold"><?= strtoupper($f['nom']) ?> <?= $f['prenom'] ?></h6>
                <hr>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Total Facture:</span>
                    <span class="fw-bold"><?= number_format($f['montant_ttc'], 0, ',', ' ') ?> F</span>
                </div>
                <div class="d-flex justify-content-between small mb-3 text-success">
                    <span>Déjà payé:</span>
                    <span><?= number_format($f['montant_paye'], 0, ',', ' ') ?> F</span>
                </div>
                
                <?php if($f['reste'] > 0): ?>
                    <div class="p-2 bg-danger-subtle text-danger rounded text-center fw-bold mb-3">
                        RELIQUAT : <?= number_format($f['reste'], 0, ',', ' ') ?> F
                    </div>
                    <form method="POST" class="input-group input-group-sm">
                        <input type="hidden" name="facture_id" value="<?= $f['id'] ?>">
                        <input type="number" name="montant_verse" class="form-control" placeholder="Montant..." max="<?= $f['reste'] ?>" required>
                        <button name="payer_reliquat" class="btn btn-dark" type="submit">Encaisser</button>
                    </form>
                <?php else: ?>
                    <div class="p-2 bg-success text-white rounded text-center fw-bold small">
                        <i class="bi bi-check-all"></i> FACTURE SOLDÉE
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
