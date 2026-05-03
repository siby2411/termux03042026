<?php
include '../../config/database.php';
$id = $_GET['id'] ?? null;
$database = new Database();
$db = $database->getConnection();

$query = "SELECT f.*, p.nom, p.prenom, p.code_patient FROM factures f JOIN patients p ON f.id_patient = p.id WHERE f.id = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $id]);
$f = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; padding: 30px; line-height: 1.5; }
        .invoice-box { border: 1px solid #eee; padding: 30px; max-width: 800px; margin: auto; }
        table { width: 100%; text-align: left; border-collapse: collapse; }
        .total { font-weight: bold; background: #f9f9f9; }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td><h2>OMÉGA CLINIQUE</h2></td>
                <td style="text-align: right;">Facture #: <?= $f['numero_facture'] ?><br>Date: <?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
            </tr>
            <tr>
                <td colspan="2"><br><strong>FACTURÉ À :</strong><br><?= $f['nom'] ?> <?= $f['prenom'] ?> (<?= $f['code_patient'] ?>)</td>
            </tr>
        </table>
        <br>
        <table border="1" cellpadding="10">
            <tr style="background: #eee;">
                <th>Description</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>Prestations médicales et frais de dossier</td>
                <td><?= number_format($f['montant_total'], 0, ',', ' ') ?> FCFA</td>
            </tr>
            <tr class="total">
                <td>TOTAL</td>
                <td><?= number_format($f['montant_total'], 0, ',', ' ') ?> FCFA</td>
            </tr>
        </table>
        <p>Mode de paiement: <?= $f['mode_paiement'] ?> | Statut: <?= $f['statut'] ?></p>
    </div>
</body>
</html>
