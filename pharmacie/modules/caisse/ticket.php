<?php
require_once '../../core/Auth.php';
require_once '../../core/Database.php';
Auth::check();

$id = $_GET['id'] ?? 0;

// Récupération des infos de la vente et du caissier
$sqlVente = "SELECT v.*, u.prenom, u.nom 
             FROM ventes v 
             JOIN utilisateurs u ON v.utilisateur_id = u.id 
             WHERE v.id = ?";
$vente = Database::query($sqlVente, [$id]);

if (!$vente) die("Vente introuvable.");
$v = $vente[0];

// Récupération des lignes de produits
$lignes = Database::query("SELECT vl.*, m.denomination 
                           FROM vente_lignes vl 
                           JOIN medicaments m ON vl.medicament_id = m.id 
                           WHERE vl.vente_id = ?", [$id]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $id; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; width: 58mm; margin: 0; padding: 5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .header { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px; }
        .footer { margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 5px 0; }
        th { border-bottom: 1px solid #000; text-align: left; }
        .total-row { font-size: 14px; margin-top: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="background:#eee; padding:10px; margin-bottom:10px;">
        <button onclick="window.print()">Imprimer le Ticket</button>
        <button onclick="window.close()">Fermer</button>
    </div>

    <div class="header text-center">
        <div class="bold" style="font-size:16px;">OMEGA SEN PHARMA</div>
        <div>Dakar, Sénégal</div>
        <div>Tel: +221 33 000 00 00</div>
    </div>

    <div>
        Date: <?php echo date('d/m/Y H:i', strtotime($v['date_vente'])); ?><br>
        Ticket #: <?php echo $id; ?><br>
        Caissier: <?php echo strtoupper($v['prenom'] . ' ' . $v['nom']); ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Art.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($lignes as $l): ?>
            <tr>
                <td><?php echo $l['quantite']; ?>x <?php echo substr($l['denomination'], 0, 15); ?></td>
                <td class="text-right"><?php echo number_format($l['montant_ligne'], 0, '', ' '); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-row bold">
        <div style="display:flex; justify-content: space-between;">
            <span>TOTAL:</span>
            <span><?php echo number_format($v['montant_total'], 0, '', ' '); ?> FCFA</span>
        </div>
    </div>

    <div style="margin-top:5px;">Paiement: <?php echo ucfirst($v['mode_paiement']); ?></div>

    <div class="footer text-center">
        *** MERCI DE VOTRE CONFIANCE ***<br>
        Les médicaments ne sont ni repris ni échangés.
    </div>
</body>
</html>
