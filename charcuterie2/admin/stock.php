<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Gestion Stock';
$pdo = getPDO();

// Mise à jour rapide du stock min
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_min'])) {
    foreach ($_POST['stock_min'] as $pid => $val) {
        $pdo->prepare("UPDATE produits SET stock_min=? WHERE id=?")->execute([(float)$val, (int)$pid]);
    }
    flash('Seuils de stock mis à jour.', 'success');
    secureRedirect('stock.php');
}

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT p.*,c.nom as cat_nom,c.couleur FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id WHERE 1";
if ($filter === 'alert') $sql .= " AND p.stock_actuel<=p.stock_min AND p.stock_min>0";
elseif ($filter === 'rupture') $sql .= " AND p.stock_actuel<=0";
elseif ($filter === 'ok') $sql .= " AND p.stock_actuel>p.stock_min";
$sql .= " ORDER BY (p.stock_actuel/NULLIF(p.stock_min,0)) ASC, p.nom";
$produits = $pdo->query($sql)->fetchAll();

$stats = [
  'total'  => $pdo->query("SELECT COUNT(*) FROM produits WHERE actif=1")->fetchColumn(),
  'ok'     => $pdo->query("SELECT COUNT(*) FROM produits WHERE actif=1 AND stock_actuel>stock_min")->fetchColumn(),
  'alert'  => $pdo->query("SELECT COUNT(*) FROM produits WHERE actif=1 AND stock_actuel<=stock_min AND stock_min>0 AND stock_actuel>0")->fetchColumn(),
  'rupture'=> $pdo->query("SELECT COUNT(*) FROM produits WHERE actif=1 AND stock_actuel<=0")->fetchColumn(),
  'valeur' => $pdo->query("SELECT COALESCE(SUM(stock_actuel*prix_achat),0) FROM produits WHERE actif=1")->fetchColumn(),
];

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-warehouse" style="color:var(--or)"></i> <span>Gestion du Stock</span></h1>
  <p>Suivi en temps réel des niveaux de stock</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#27ae60;--color2:#2ecc71">
      <div class="label">Stock Normal</div><div class="value"><?=$stats['ok']?></div>
      <small style="color:var(--muted)">produits à niveau</small><div class="icon-bg">✅</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#e67e22;--color2:#f39c12">
      <div class="label">Alertes Stock</div><div class="value" style="color:#e67e22"><?=$stats['alert']?></div>
      <small style="color:var(--muted)">niveau faible</small><div class="icon-bg">⚠️</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#c0392b;--color2:#e74c3c">
      <div class="label">Ruptures</div><div class="value" style="color:#e74c3c"><?=$stats['rupture']?></div>
      <small style="color:var(--muted)">stock à 0</small><div class="icon-bg">❌</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#2980b9;--color2:#3498db">
      <div class="label">Valeur Stock</div>
      <div class="value" style="font-size:1.3rem"><?= number_format($stats['valeur'],0,',',' ') ?></div>
      <small style="color:var(--muted)">FCFA (prix achat)</small><div class="icon-bg">💰</div>
    </div>
  </div>
</div>

<!-- Filtre -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
  <a href="stock.php?filter=all" class="btn-omega <?=$filter==='all'?'btn-omega-gold':'btn-omega-outline'?>">Tous (<?=$stats['total']?>)</a>
  <a href="stock.php?filter=ok" class="btn-omega <?=$filter==='ok'?'btn-omega-success':'btn-omega-outline'?>">✅ OK (<?=$stats['ok']?>)</a>
  <a href="stock.php?filter=alert" class="btn-omega <?=$filter==='alert'?'btn-omega-primary':'btn-omega-outline'?>" style="<?=$filter==='alert'?'background:linear-gradient(135deg,#e67e22,#d35400);':'';?>">⚠️ Alertes (<?=$stats['alert']?>)</a>
  <a href="stock.php?filter=rupture" class="btn-omega <?=$filter==='rupture'?'btn-omega-danger':'btn-omega-outline'?>">❌ Ruptures (<?=$stats['rupture']?>)</a>
</div>

<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-boxes"></i> Inventaire Stock</h4>
    <a href="approvisionnement.php" class="btn-omega btn-omega-primary" style="font-size:.8rem"><i class="fas fa-plus"></i> Approvisionner</a>
  </div>
  <form method="POST">
  <div style="overflow-x:auto">
    <table class="table-omega">
      <thead>
        <tr>
          <th>Produit</th><th>Catégorie</th><th>Stock Actuel</th><th>Seuil Min.</th>
          <th>Niveau</th><th>P. Achat</th><th>Valeur Stock</th><th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($produits as $p):
          $pct = $p['stock_min'] > 0 ? min(100, ($p['stock_actuel']/$p['stock_min'])*100) : 100;
          $barColor = $p['stock_actuel'] <= 0 ? '#e74c3c' : ($pct < 100 ? '#e67e22' : '#27ae60');
          $valeur = $p['stock_actuel'] * $p['prix_achat'];
        ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($p['nom']) ?></strong>
          </td>
          <td><span style="color:<?= htmlspecialchars($p['couleur']??'#888') ?>;font-size:.8rem"><?= htmlspecialchars($p['cat_nom']??'—') ?></span></td>
          <td>
            <strong style="color:<?=$barColor?>;font-size:1rem"><?= number_format($p['stock_actuel'],3) ?></strong>
            <small style="color:var(--muted)"> <?= htmlspecialchars($p['unite']) ?></small>
          </td>
          <td>
            <input type="number" name="stock_min[<?=$p['id']?>]" value="<?=$p['stock_min']?>"
              class="form-control" step="0.01" min="0"
              style="width:80px;background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--text);border-radius:6px;padding:5px 8px;font-size:.8rem">
          </td>
          <td style="min-width:120px">
            <div style="background:rgba(255,255,255,.08);border-radius:20px;height:8px;overflow:hidden">
              <div style="height:8px;width:<?=min(100,$pct)?>%;background:<?=$barColor?>;border-radius:20px;transition:.5s"></div>
            </div>
            <small style="color:var(--muted)"><?= number_format($pct,0) ?>%</small>
          </td>
          <td><small><?= number_format($p['prix_achat'],0,',',' ') ?></small></td>
          <td><small style="color:var(--or)"><?= number_format($valeur,0,',',' ') ?> FCFA</small></td>
          <td>
            <?php if($p['stock_actuel']<=0): ?>
              <span class="badge-stat b-danger">❌ Rupture</span>
            <?php elseif($pct<100): ?>
              <span class="badge-stat b-warning">⚠️ Faible</span>
            <?php else: ?>
              <span class="badge-stat b-success">✅ OK</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:15px;border-top:1px solid var(--border)">
    <button type="submit" name="update_min" class="btn-omega btn-omega-gold">
      <i class="fas fa-save"></i> Sauvegarder les seuils
    </button>
  </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
