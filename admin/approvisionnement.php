<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Approvisionnement';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodId = (int)$_POST['produit_id'];
    $fouId  = (int)$_POST['fournisseur_id'];
    $qte    = (float)$_POST['quantite'];
    $pu     = (float)$_POST['prix_unitaire'];
    $date   = $_POST['date_appro'];
    $ref    = trim($_POST['reference'] ?? '');
    $notes  = trim($_POST['notes'] ?? '');
    if ($prodId && $qte > 0 && $pu > 0 && $date) {
        $total = $qte * $pu;
        $pdo->prepare("INSERT INTO approvisionnements (fournisseur_id,produit_id,quantite,prix_unitaire,total,date_appro,reference,notes)
            VALUES (?,?,?,?,?,?,?,?)")->execute([$fouId ?: null, $prodId, $qte, $pu, $total, $date, $ref, $notes]);
        $pdo->prepare("UPDATE produits SET stock_actuel=stock_actuel+? WHERE id=?")->execute([$qte, $prodId]);
        flash("Approvisionnement enregistré : +$qte unités, coût ".number_format($total,0,',',' ')." FCFA", 'success');
    } else { flash('Données invalides.', 'error'); }
    secureRedirect('approvisionnement.php');
}

if (isset($_GET['del'])) {
    $aid = (int)$_GET['del'];
    $a = $pdo->prepare("SELECT * FROM approvisionnements WHERE id=?"); $a->execute([$aid]); $ad = $a->fetch();
    if ($ad) {
        $pdo->prepare("UPDATE produits SET stock_actuel=GREATEST(0,stock_actuel-?) WHERE id=?")->execute([$ad['quantite'], $ad['produit_id']]);
        $pdo->prepare("DELETE FROM approvisionnements WHERE id=?")->execute([$aid]);
        flash('Approvisionnement supprimé, stock ajusté.', 'success');
    }
    secureRedirect('approvisionnement.php');
}

$produits = $pdo->query("SELECT * FROM produits WHERE actif=1 ORDER BY nom")->fetchAll();
$fournisseurs = $pdo->query("SELECT * FROM fournisseurs ORDER BY nom")->fetchAll();

$dateFrom = $_GET['from'] ?? date('Y-m-01');
$dateTo   = $_GET['to']   ?? date('Y-m-d');
$appros = $pdo->prepare("SELECT a.*,p.nom as prod_nom,p.unite,f.nom as fourn_nom
    FROM approvisionnements a LEFT JOIN produits p ON a.produit_id=p.id
    LEFT JOIN fournisseurs f ON a.fournisseur_id=f.id
    WHERE a.date_appro BETWEEN ? AND ? ORDER BY a.date_appro DESC");
$appros->execute([$dateFrom,$dateTo]);
$appros = $appros->fetchAll();
$totalAchats = array_sum(array_column($appros,'total'));

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-truck-loading" style="color:var(--or)"></i> <span>Approvisionnement</span></h1>
  <p>Gestion des entrées de stock et des achats fournisseurs</p>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card-omega" style="position:sticky;top:80px">
      <div class="card-head"><h4><i class="fas fa-plus-circle" style="color:var(--bleu)"></i> Nouvelle Entrée Stock</h4></div>
      <div class="card-body">
        <form method="POST" class="form-omega">
          <div class="mb-3">
            <label class="form-label">Produit *</label>
            <select name="produit_id" class="form-select" required onchange="setAppPrix(this)">
              <option value="">-- Produit --</option>
              <?php foreach($produits as $p): ?>
              <option value="<?=$p['id']?>" data-pa="<?=$p['prix_achat']?>" data-unite="<?=$p['unite']?>"><?= htmlspecialchars($p['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Fournisseur</label>
            <select name="fournisseur_id" class="form-select">
              <option value="">-- Fournisseur --</option>
              <?php foreach($fournisseurs as $f): ?>
              <option value="<?=$f['id']?>"><?= htmlspecialchars($f['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Quantité *</label>
              <input type="number" name="quantite" id="appQte" class="form-control" min="0.001" step="0.001" required oninput="calcApp()">
            </div>
            <div class="col-6">
              <label class="form-label">P.U. Achat (FCFA)</label>
              <input type="number" name="prix_unitaire" id="appPU" class="form-control" min="0" step="0.01" oninput="calcApp()">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Date *</label>
            <input type="date" name="date_appro" class="form-control" required value="<?= date('Y-m-d') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Référence BL</label>
            <input type="text" name="reference" class="form-control" placeholder="N° bon de livraison">
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Observations..."></textarea>
          </div>
          <div style="background:rgba(41,128,185,.1);border:1px solid rgba(41,128,185,.2);border-radius:10px;padding:12px;margin-bottom:15px;text-align:center">
            <div style="font-size:.75rem;color:var(--muted)">COÛT TOTAL</div>
            <div id="appTotal" style="font-size:1.6rem;font-weight:900;color:#3498db;font-family:'Playfair Display',serif">0 FCFA</div>
          </div>
          <button type="submit" class="btn-omega btn-omega-primary w-100"><i class="fas fa-warehouse"></i> Enregistrer l'Entrée</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="row g-3 mb-4">
      <div class="col-6">
        <div class="stat-card" style="--color1:#2980b9;--color2:#3498db">
          <div class="label">Total Achats Période</div>
          <div class="value" style="font-size:1.3rem"><?= number_format($totalAchats,0,',',' ') ?></div>
          <small style="color:var(--muted)">FCFA</small><div class="icon-bg">🏭</div>
        </div>
      </div>
      <div class="col-6">
        <div class="stat-card" style="--color1:#8e44ad;--color2:#9b59b6">
          <div class="label">Entrées</div>
          <div class="value"><?= count($appros) ?></div>
          <small style="color:var(--muted)">approvisionnements</small><div class="icon-bg">📦</div>
        </div>
      </div>
    </div>

    <div class="card-omega" style="margin-bottom:15px">
      <div class="card-body" style="padding:15px">
        <form method="GET" class="form-omega" style="display:flex;gap:10px;flex-wrap:wrap">
          <div><label class="form-label">Du</label><input type="date" name="from" class="form-control" value="<?=$dateFrom?>"></div>
          <div><label class="form-label">Au</label><input type="date" name="to" class="form-control" value="<?=$dateTo?>"></div>
          <div style="align-self:flex-end"><button type="submit" class="btn-omega btn-omega-gold">Filtrer</button></div>
          <div style="align-self:flex-end"><a href="approvisionnement.php" class="btn-omega btn-omega-outline">Reset</a></div>
        </form>
      </div>
    </div>

    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-list"></i> Historique Approvisionnements</h4></div>
      <div style="overflow-x:auto">
        <table class="table-omega">
          <thead><tr><th>Date</th><th>Produit</th><th>Fournisseur</th><th>Qté</th><th>P.U.</th><th>Total</th><th>Réf.</th><th></th></tr></thead>
          <tbody>
            <?php if(empty($appros)): ?>
              <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">Aucun approvisionnement</td></tr>
            <?php else: foreach($appros as $a): ?>
            <tr>
              <td><small><?= date('d/m/Y',strtotime($a['date_appro'])) ?></small></td>
              <td><?= htmlspecialchars($a['prod_nom']??'—') ?></td>
              <td><small><?= htmlspecialchars($a['fourn_nom']??'—') ?></small></td>
              <td><strong style="color:#3498db">+<?= number_format($a['quantite'],3) ?></strong> <small style="color:var(--muted)"><?= htmlspecialchars($a['unite']??'') ?></small></td>
              <td><?= number_format($a['prix_unitaire'],0,',',' ') ?></td>
              <td><strong><?= number_format($a['total'],0,',',' ') ?></strong> FCFA</td>
              <td><small style="color:var(--muted)"><?= htmlspecialchars($a['reference']??'—') ?></small></td>
              <td><a href="approvisionnement.php?del=<?=$a['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:4px 8px;font-size:.7rem"><i class="fas fa-times"></i></a></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <?php if(!empty($appros)): ?>
          <tfoot>
            <tr style="background:rgba(41,128,185,.05)">
              <td colspan="5"><strong>TOTAL ACHATS</strong></td>
              <td colspan="3"><strong style="color:#3498db"><?= number_format($totalAchats,0,',',' ') ?> FCFA</strong></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function setAppPrix(sel){
  const opt=sel.options[sel.selectedIndex];
  document.getElementById('appPU').value=opt.dataset.pa||'';
  calcApp();
}
function calcApp(){
  const q=parseFloat(document.getElementById('appQte').value)||0;
  const p=parseFloat(document.getElementById('appPU').value)||0;
  document.getElementById('appTotal').textContent=(q*p).toLocaleString('fr')+' FCFA';
}
</script>
<?php require_once 'footer.php'; ?>
