<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// Traitement ajout paiement manuel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['montant'])) {
    $stmt = $pdo->prepare("INSERT INTO paiements (facture_id, client_id, date_paiement, montant, mode_paiement) VALUES (?,?,NOW(),?,?)");
    $stmt->execute([$_POST['facture_id'], $_POST['client_id'], $_POST['montant'], $_POST['mode']]);
    setFlash('success', "Encaissement de " . $_POST['montant'] . " F enregistré.");
    header("Location: paiements.php"); exit;
}

$paiements = $pdo->query("SELECT p.*, cl.nom, cl.prenom, cl.telephone, f.numero_facture FROM paiements p JOIN clients cl ON p.client_id = cl.id JOIN factures f ON p.facture_id = f.id ORDER BY p.date_paiement DESC")->fetchAll();
$clients = $pdo->query("SELECT id, nom, prenom FROM clients")->fetchAll();
$factures = $pdo->query("SELECT id, numero_facture FROM factures WHERE reste > 0")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Journal des Encaissements</h4>
    <button class="btn btn-dark border-gold" data-bs-toggle="modal" data-bs-target="#modalPay"><i class="bi bi-plus-lg text-gold"></i> Nouvel Encaissement</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>Date</th><th>Client</th><th>Facture</th><th>Mode</th><th>Montant</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($paiements as $p): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                    <td><?= strtoupper($p['nom']) ?> <?= $p['prenom'] ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $p['numero_facture'] ?></span></td>
                    <td><?= ucfirst($p['mode_paiement']) ?></td>
                    <td class="fw-bold text-success">+ <?= number_format($p['montant'], 0, ',', ' ') ?> F</td>
                    <td class="text-end">
                        <a href="<?= lienWhatsApp($p['telephone'], "Reçu de paiement OMEGA : " . $p['montant'] . " F") ?>" target="_blank" class="btn btn-sm btn-success"><i class="bi bi-whatsapp"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalPay" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-dark text-white"><h5 class="modal-title text-gold">Enregistrer un paiement</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-3">
                <div class="col-12"><label>Client</label>
                    <select name="client_id" class="form-select" required>
                        <?php foreach($clients as $c): ?><option value="<?= $c['id'] ?>"><?= $c['prenom'] ?> <?= $c['nom'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><label>Facture associée</label>
                    <select name="facture_id" class="form-select" required>
                        <?php foreach($factures as $f): ?><option value="<?= $f['id'] ?>"><?= $f['numero_facture'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" required></div>
                <div class="col-md-6"><label>Mode</label>
                    <select name="mode" class="form-select"><option value="espèces">Espèces</option><option value="wave">Wave</option><option value="orange money">Orange Money</option></select>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-dark border-gold text-gold w-100">Confirmer l'encaissement</button></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
