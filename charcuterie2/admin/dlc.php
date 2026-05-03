<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Gestion DLC / DLUO';
$pdo = getPDO();

$pdo->exec("CREATE TABLE IF NOT EXISTS dlc_produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    lot VARCHAR(50),
    quantite DECIMAL(10,3) NOT NULL,
    date_fabrication DATE,
    date_dlc DATE NOT NULL,
    temperature_stockage VARCHAR(30) DEFAULT '0-4°C',
    statut ENUM('ok','alerte','expire','retire') DEFAULT 'ok',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
)");

// Mise à jour automatique des statuts
$pdo->exec("UPDATE dlc_produits SET statut='expire' WHERE date_dlc < CURDATE() AND statut NOT IN ('retire')");
$pdo->exec("UPDATE dlc_produits SET statut='alerte' WHERE date_dlc BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 3 DAY) AND statut='ok'");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_dlc'])) {
    $pdo->prepare("INSERT INTO dlc_produits (produit_id,lot,quantite,date_fabrication,date_dlc,temperature_stockage,notes)
        VALUES (?,?,?,?,?,?,?)")->execute([
        (int)$_POST['produit_id'], trim($_POST['lot']),
        (float)$_POST['quantite'], $_POST['date_fab'] ?: null,
        $_POST['date_dlc'], trim($_POST['temp']), trim($_POST['notes'])
    ]);
    flash('Lot DLC enregistré.', 'success'); secureRedirect('dlc.php');
}

if (isset($_GET['retirer'])) {
    $pdo->prepare("UPDATE dlc_produits SET statut='retire' WHERE id=?")->execute([(int)$_GET['retirer']]);
    flash('Lot retiré de la vente.', 'success'); secureRedirect('dlc.php');
}

$filtre = $_GET['f'] ?? 'all';
$sql = "SELECT d.*,p.nom as prod_nom,p.unite,DATEDIFF(d.date_dlc,CURDATE()) as jours_restants
    FROM dlc_produits d JOIN produits p ON d.produit_id=p.id WHERE 1";
if ($filtre==='alerte') $sql .= " AND d.statut='alerte'";
elseif ($filtre==='expire') $sql .= " AND d.statut='expire'";
elseif ($filtre==='ok') $sql .= " AND d.statut='ok'";
$sql .= " ORDER BY d.date_dlc ASC";
$lots = $pdo->query($sql)->fetchAll();

$stats = [
  'ok'     => $pdo->query("SELECT COUNT(*) FROM dlc_produits WHERE statut='ok'")->fetchColumn(),
  'alerte' => $pdo->query("SELECT COUNT(*) FROM dlc_produits WHERE statut='alerte'")->fetchColumn(),
  'expire' => $pdo->query("SELECT COUNT(*) FROM dlc_produits WHERE statut='expire'")->fetchColumn(),
];

$produits = $pdo->query("SELECT * FROM produits WHERE actif=1 ORDER BY nom")->fetchAll();

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-calendar-times" style="color:var(--or)"></i> <span>Gestion DLC / DLUO</span></h1>
  <p>Suivi des dates limite de consommation — Traçabilité HACCP</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card" style="--color1:#27ae60;--color2:#2ecc71">
      <div class="label">Lots valides</div><div class="value"><?=$stats['ok']?></div>
      <div class="icon-bg">✅</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card" style="--color1:#e67e22;--color2:#f39c12">
      <div class="label">Expirent dans 3 jours</div>
      <div class="value" style="color:#e67e22"><?=$stats['alerte']?></div>
      <div class="icon-bg">⚠️</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card" style="--color1:#c0392b;--color2:#e74c3c">
      <div class="label">Lots expirés</div>
      <div class="value" style="color:#e74c3c"><?=$stats['expire']?></div>
      <div class="icon-bg">❌</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card-omega" style="position:sticky;top:80px">
      <div class="card-head"><h4><i class="fas fa-plus"></i> Nouveau Lot DLC</h4></div>
      <div class="card-body">
        <form method="POST" class="form-omega">
          <div class="mb-3">
            <label class="form-label">Produit *</label>
            <select name="produit_id" class="form-select" required>
              <option value="">-- Produit --</option>
              <?php foreach($produits as $p): ?>
              <option value="<?=$p['id']?>"><?= htmlspecialchars($p['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">N° Lot</label>
              <input type="text" name="lot" class="form-control" placeholder="LOT-2024-001">
            </div>
            <div class="col-6">
              <label class="form-label">Quantité *</label>
              <input type="number" name="quantite" class="form-control" min="0.001" step="0.001" required>
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Date Fabrication</label>
              <input type="date" name="date_fab" class="form-control">
            </div>
            <div class="col-6">
              <label class="form-label">DLC / DLUO *</label>
              <input type="date" name="date_dlc" class="form-control" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Température stockage</label>
            <select name="temp" class="form-select">
              <option>0-4°C (Réfrigéré)</option>
              <option>-18°C (Congelé)</option>
              <option>Ambiant (15-20°C)</option>
              <option>Cave (10-14°C)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Observations..."></textarea>
          </div>
          <button type="submit" name="save_dlc" class="btn-omega btn-omega-primary w-100">
            <i class="fas fa-save"></i> Enregistrer le Lot
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div style="display:flex;gap:8px;margin-bottom:15px">
      <a href="dlc.php?f=all"    class="btn-omega <?=$filtre==='all'?'btn-omega-gold':'btn-omega-outline'?>">Tous (<?=array_sum($stats)?>)</a>
      <a href="dlc.php?f=ok"     class="btn-omega btn-omega-outline" style="<?=$filtre==='ok'?'border-color:#27ae60;color:#27ae60':''?>">✅ OK (<?=$stats['ok']?>)</a>
      <a href="dlc.php?f=alerte" class="btn-omega btn-omega-outline" style="<?=$filtre==='alerte'?'border-color:#e67e22;color:#e67e22':''?>">⚠️ Alertes (<?=$stats['alerte']?>)</a>
      <a href="dlc.php?f=expire" class="btn-omega btn-omega-danger">❌ Expirés (<?=$stats['expire']?>)</a>
    </div>

    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-list"></i> Suivi des Lots</h4></div>
      <div style="overflow-x:auto">
        <table class="table-omega">
          <thead><tr><th>Produit</th><th>Lot</th><th>Qté</th><th>DLC</th><th>Restant</th><th>Temp.</th><th>Statut</th><th></th></tr></thead>
          <tbody>
          <?php if(empty($lots)): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">Aucun lot enregistré</td></tr>
          <?php else: foreach($lots as $l):
            $jr = (int)$l['jours_restants'];
            $sc = $l['statut']==='ok'?'b-success':($l['statut']==='alerte'?'b-warning':($l['statut']==='retire'?'b-muted':'b-danger'));
            $sl = ['ok'=>'✅ Valide','alerte'=>'⚠️ Alerte','expire'=>'❌ Expiré','retire'=>'🚫 Retiré'][$l['statut']];
          ?>
          <tr style="<?= $l['statut']==='expire'?'opacity:.6':'' ?>">
            <td><strong style="font-size:.85rem"><?= htmlspecialchars($l['prod_nom']) ?></strong></td>
            <td><small style="color:var(--or)"><?= htmlspecialchars($l['lot']??'—') ?></small></td>
            <td><?= number_format($l['quantite'],2) ?> <small><?= htmlspecialchars($l['unite']) ?></small></td>
            <td><strong><?= date('d/m/Y',strtotime($l['date_dlc'])) ?></strong></td>
            <td>
              <span style="color:<?= $jr<0?'#e74c3c':($jr<=3?'#e67e22':'#27ae60') ?>;font-weight:700">
                <?= $jr<0 ? abs($jr).'j dépassé' : ($jr==0?'Aujourd\'hui':$jr.'j') ?>
              </span>
            </td>
            <td><small><?= htmlspecialchars($l['temperature_stockage']) ?></small></td>
            <td><span class="badge-stat <?=$sc?>"><?=$sl?></span></td>
            <td>
              <?php if($l['statut']!=='retire'): ?>
              <a href="dlc.php?retirer=<?=$l['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:4px 8px;font-size:.7rem">Retirer</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
