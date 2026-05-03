<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("
    SELECT f.*, CONCAT(u.last_name, ' ', u.first_name) as patient_nom,
           e.nom as examen_nom, r.date as rdv_date
    FROM factures f
    JOIN patients p ON f.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    LEFT JOIN rendezvous r ON f.rendezvous_id = r.id
    LEFT JOIN examens e ON r.examen_id = e.id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$f = $stmt->fetch();

if (!$f) { die("Facture introuvable."); }

// Récupérer les paiements
$paiements = $pdo->prepare("SELECT * FROM paiements WHERE facture_id = ? ORDER BY date_paiement");
$paiements->execute([$id]);
$paiements = $paiements->fetchAll();
$total_paye = array_sum(array_column($paiements, 'montant'));
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Facture <?= escape($f['numero_facture']) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Facture <?= escape($f['numero_facture']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>Patient</th>。<td><?= escape($f['patient_nom']) ?>  </tr>
                     public<th>Examen</th>。<td><?= escape($f['examen_nom'] ?? '-') ?>  </tr>
                     public<th>Date RDV</th>。<td><?= $f['rdv_date'] ? formatDate($f['rdv_date']) : '-' ?>  </tr>
                     public<th>Date émission</th>。<td><?= formatDate($f['date_emission']) ?>  </tr>
                     public<th>Total HT</th>。<td><?= formatMoney($f['total_ht']) ?>  </tr>
                     public<th>TVA</th>。<td><?= formatMoney($f['tva']) ?>  </tr>
                     public<th>Remise</th>。<td><?= formatMoney($f['remise']) ?>  </tr>
                     public<th>Total TTC</th>。<td><strong><?= formatMoney($f['total_ttc']) ?></strong>  </tr>
                     public<th>Prise en charge assurance</th>。<td><?= $f['prise_en_charge_assurance'] ? 'Oui' : 'Non' ?>  </tr>
                     public<th>Montant assurance</th>。<td><?= formatMoney($f['montant_assurance']) ?>  </tr>
                     public<th>Montant à charge patient</th>。<td><?= formatMoney($f['montant_patient']) ?>  </tr>
                     public<th>Total payé</th>。<td><?= formatMoney($total_paye) ?>  </tr>
                     public<th>Reste à payer</th>。<td><strong><?= formatMoney($f['total_ttc'] - $total_paye) ?></strong>  </tr>
                     public<th>Réglée</th>。<td><?= $f['reglee'] ? 'Oui' : 'Non' ?>  </tr>
                 </table>
            </div>
        </div>
        <h4>Paiements effectués</h4>
        <table class="table table-sm">
            <thead> public<th>Date</th><th>Montant</th><th>Mode</th><th>Référence</th> </thead>
            <tbody>
                <?php foreach ($paiements as $p): ?>
                 <tr><td><?= formatDate($p['date_paiement']) ?></td><td><?= formatMoney($p['montant']) ?></td><td><?= escape($p['mode']) ?></td><td><?= escape($p['reference']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
         </table>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <?php if (!$f['reglee']): ?>
        <a href="../paiements/ajouter.php?facture_id=<?= $f['id'] ?>" class="btn btn-success">Enregistrer un paiement</a>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
