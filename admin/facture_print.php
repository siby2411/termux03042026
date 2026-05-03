<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: factures.php'); exit; }
$pdo = getPDO();

$s = $pdo->prepare("SELECT f.*,c.nom,c.prenom,c.telephone,c.email,c.adresse
    FROM factures f LEFT JOIN clients c ON f.client_id=c.id WHERE f.id=?");
$s->execute([$id]); $facture = $s->fetch();
if (!$facture) { header('Location: factures.php'); exit; }

$sl = $pdo->prepare("SELECT fl.*,p.nom as prod_nom FROM facture_lignes fl LEFT JOIN produits p ON fl.produit_id=p.id WHERE fl.facture_id=?");
$sl->execute([$id]); $lignes = $sl->fetchAll();

$statuts = ['emise'=>'Émise','payee'=>'Payée','annulee'=>'Annulée','brouillon'=>'Brouillon'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facture <?= htmlspecialchars($facture['numero']) ?> – OMEGA Charcuterie</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Raleway',sans-serif;background:#fff;color:#1a1a1a;padding:30px;font-size:13px}
.no-print{margin-bottom:20px;display:flex;gap:10px}
.btn{padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:.85rem}
.btn-print{background:#c0392b;color:#fff}
.btn-back{background:#eee;color:#333;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.facture-doc{max-width:800px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden}
.facture-header{background:linear-gradient(135deg,#c0392b,#922b21);color:#fff;padding:30px;display:flex;justify-content:space-between;align-items:flex-start}
.facture-header h1{font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:8px}
.facture-header p{font-size:.82rem;opacity:.9;line-height:1.6}
.facture-num{text-align:right}
.facture-num h2{font-size:2rem;font-family:'Playfair Display',serif;color:#f1c40f;letter-spacing:2px}
.facture-num p{font-size:.8rem;opacity:.9}
.facture-body{padding:30px}
.facture-parties{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:25px}
.partie{padding:15px;border:1px solid #eee;border-radius:8px}
.partie h4{color:#c0392b;font-size:.75rem;text-transform:uppercase;letter-spacing:2px;margin-bottom:10px;font-weight:700}
.partie p{font-size:.85rem;line-height:1.6;color:#444}
.table-fact{width:100%;border-collapse:collapse;margin-bottom:20px}
.table-fact th{background:#f8f8f8;color:#666;font-size:.75rem;text-transform:uppercase;letter-spacing:1px;padding:10px 12px;border-bottom:2px solid #e0e0e0;text-align:left}
.table-fact td{padding:10px 12px;border-bottom:1px solid #f0f0f0;font-size:.85rem}
.table-fact tr:last-child td{border-bottom:none}
.table-fact .right{text-align:right}
.totaux{display:flex;justify-content:flex-end;margin-bottom:20px}
.totaux-box{width:280px;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden}
.totaux-row{display:flex;justify-content:space-between;padding:8px 15px;font-size:.85rem}
.totaux-row:nth-child(odd){background:#fafafa}
.totaux-row.total-ttc{background:linear-gradient(135deg,#c0392b,#922b21);color:#fff;font-weight:700;font-size:1rem;padding:12px 15px}
.totaux-row.total-ttc span:last-child{color:#f1c40f}
.statut-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700}
.s-payee{background:#d5f4e6;color:#27ae60}
.s-emise{background:#fef9e7;color:#d4ac0d}
.s-annulee{background:#fdf2f2;color:#c0392b}
.facture-footer{background:#f8f8f8;padding:20px 30px;border-top:2px solid #c0392b;text-align:center;font-size:.78rem;color:#888}
.facture-footer strong{color:#c0392b}
.notes-box{background:#fef9e7;border:1px solid #f1c40f;border-radius:6px;padding:12px 15px;margin-bottom:20px;font-size:.82rem;color:#666}
@media print{
  .no-print{display:none!important}
  body{padding:0}
  .facture-doc{border:none;border-radius:0}
}
</style>
</head>
<body>
<div class="no-print">
  <button class="btn btn-print" onclick="window.print()">🖨️ Imprimer / PDF</button>
  <a href="factures.php?action=view&id=<?=$id?>" class="btn btn-back">← Retour</a>
</div>

<div class="facture-doc">
  <!-- EN-TÊTE -->
  <div class="facture-header">
    <div>
      <h1>🥩 OMEGA CHARCUTERIE</h1>
      <p>OMEGA INFORMATIQUE CONSULTING<br>
      Zone Commerciale, Dakar, Sénégal<br>
      Tél : +221 33 XXX XX XX<br>
      Email : info@omega-charcuterie.sn<br>
      NINEA : XXXXXXXXX | RC : XXXXXXXXX</p>
    </div>
    <div class="facture-num">
      <h2>FACTURE</h2>
      <p><?= htmlspecialchars($facture['numero']) ?><br>
      Date : <?= date('d/m/Y', strtotime($facture['date_facture'])) ?><br>
      <span class="statut-badge s-<?= $facture['statut'] ?>"><?= $statuts[$facture['statut']] ?></span></p>
    </div>
  </div>

  <div class="facture-body">
    <!-- PARTIES -->
    <div class="facture-parties">
      <div class="partie">
        <h4>Émetteur</h4>
        <p><strong>OMEGA INFORMATIQUE CONSULTING</strong><br>
        Gestion Charcuterie<br>
        Zone Commerciale, Dakar<br>
        Sénégal</p>
      </div>
      <div class="partie">
        <h4>Facturé à</h4>
        <p>
          <strong><?= htmlspecialchars(trim(($facture['prenom']??'').' '.($facture['nom']??'')) ?: 'Client Comptoir') ?></strong><br>
          <?php if($facture['telephone']): ?><?= htmlspecialchars($facture['telephone']) ?><br><?php endif; ?>
          <?php if($facture['email']): ?><?= htmlspecialchars($facture['email']) ?><br><?php endif; ?>
          <?php if($facture['adresse']): ?><?= htmlspecialchars($facture['adresse']) ?><?php endif; ?>
        </p>
      </div>
    </div>

    <!-- LIGNES -->
    <table class="table-fact">
      <thead>
        <tr>
          <th style="width:30px">#</th>
          <th>Désignation</th>
          <th style="width:80px">Qté</th>
          <th style="width:60px">Unité</th>
          <th style="width:100px" class="right">P.U. (FCFA)</th>
          <th style="width:110px" class="right">Total (FCFA)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($lignes as $i=>$l): ?>
        <tr>
          <td><?=$i+1?></td>
          <td><strong><?= htmlspecialchars($l['designation']?:$l['prod_nom']??'—') ?></strong></td>
          <td><?= number_format($l['quantite'],3) ?></td>
          <td><?= htmlspecialchars($l['unite']) ?></td>
          <td class="right"><?= number_format($l['prix_unitaire'],0,',',' ') ?></td>
          <td class="right"><strong><?= number_format($l['total_ligne'],0,',',' ') ?></strong></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- TOTAUX -->
    <div class="totaux">
      <div class="totaux-box">
        <div class="totaux-row"><span>Sous-total HT</span><span><?= number_format($facture['total_ht'],0,',',' ') ?> FCFA</span></div>
        <div class="totaux-row"><span>TVA (<?= $facture['tva'] ?>%)</span><span><?= number_format($facture['total_ht']*$facture['tva']/100,0,',',' ') ?> FCFA</span></div>
        <div class="totaux-row total-ttc"><span>TOTAL TTC</span><span><?= number_format($facture['total_ttc'],0,',',' ') ?> FCFA</span></div>
      </div>
    </div>

    <!-- NOTES -->
    <?php if($facture['notes']): ?>
    <div class="notes-box"><strong>Notes :</strong> <?= htmlspecialchars($facture['notes']) ?></div>
    <?php endif; ?>

    <!-- MENTION PAIEMENT -->
    <p style="font-size:.78rem;color:#aaa;border-top:1px solid #eee;padding-top:15px">
      Paiement à effectuer par virement bancaire ou espèces à la réception de la facture.<br>
      Tout retard de paiement entraîne des pénalités de 1,5% par mois.
    </p>
  </div>

  <div class="facture-footer">
    <strong>OMEGA INFORMATIQUE CONSULTING</strong> – GESTION CHARCUTERIE<br>
    Zone Commerciale, Dakar, Sénégal | Tél : +221 33 XXX XX XX | info@omega.sn<br>
    Merci de votre confiance — Qualité · Service · Excellence
  </div>
</div>
</body>
</html>
