<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Inventaire & Traçabilité';
$pdo = getPDO();

// ── Créer table inventaire ──
$pdo->exec("CREATE TABLE IF NOT EXISTS inventaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    stock_theorique DECIMAL(10,3),
    stock_physique DECIMAL(10,3),
    ecart DECIMAL(10,3),
    motif VARCHAR(200),
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
)");

// ── Ajustement stock ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajuster'])) {
    foreach ($_POST['stock_physique'] as $pid => $val) {
        $pid = (int)$pid; $physique = (float)$val;
        $theorique = (float)$_POST['stock_theorique'][$pid];
        $ecart = $physique - $theorique;
        $motif = trim($_POST['motif'][$pid] ?? 'Inventaire physique');
        if ($val !== '') {
            $pdo->prepare("INSERT INTO inventaires (produit_id,stock_theorique,stock_physique,ecart,motif,user_id)
                VALUES (?,?,?,?,?,?)")->execute([$pid,$theorique,$physique,$ecart,$motif,$_SESSION['admin_id']]);
            $pdo->prepare("UPDATE produits SET stock_actuel=? WHERE id=?")->execute([$physique,$pid]);
        }
    }
    flash('Inventaire enregistré et stocks ajustés.', 'success');
    secureRedirect('inventaire.php');
}

$catFilter = (int)($_GET['cat'] ?? 0);
$sql = "SELECT p.*,c.nom as cat_nom,c.couleur FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id WHERE p.actif=1";
if ($catFilter) { $sql .= " AND p.categorie_id=$catFilter"; }
$sql .= " ORDER BY c.nom,p.nom";
$produits = $pdo->query($sql)->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

// Derniers ajustements
$derniers = $pdo->query("SELECT i.*,p.nom as prod_nom,p.unite
    FROM inventaires i JOIN produits p ON i.produit_id=p.id
    ORDER BY i.created_at DESC LIMIT 15")->fetchAll();

// Stats écarts
$totalEcarts = $pdo->query("SELECT COUNT(*) FROM inventaires WHERE ecart != 0")->fetchColumn();
$perteValeur = $pdo->query("SELECT COALESCE(SUM(ABS(i.ecart)*p.prix_achat),0)
    FROM inventaires i JOIN produits p ON i.produit_id=p.id WHERE i.ecart < 0")->fetchColumn();

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-clipboard-list" style="color:var(--or)"></i> <span>Inventaire Physique</span></h1>
  <p>Comptage physique du stock et ajustement des écarts – <?= date('d/m/Y H:i') ?></p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card" style="--color1:#2980b9;--color2:#3498db">
      <div class="label">Produits à inventorier</div>
      <div class="value"><?= count($produits) ?></div>
      <div class="icon-bg">📋</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card" style="--color1:#e67e22;--color2:#f39c12">
      <div class="label">Ajustements effectués</div>
      <div class="value"><?= $totalEcarts ?></div>
      <div class="icon-bg">⚖️</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card" style="--color1:#c0392b;--color2:#e74c3c">
      <div class="label">Pertes valorisées</div>
      <div class="value" style="font-size:1.2rem"><?= number_format($perteValeur,0,',',' ') ?></div>
      <small style="color:var(--muted)">FCFA</small>
      <div class="icon-bg">💸</div>
    </div>
  </div>
</div>

<!-- FORMULAIRE INVENTAIRE -->
<div class="card-omega" style="margin-bottom:20px">
  <div class="card-head">
    <h4><i class="fas fa-edit"></i> Saisie Inventaire Physique</h4>
    <div style="display:flex;gap:8px">
      <form method="GET">
        <select name="cat" class="form-select form-omega" style="font-size:.8rem;padding:6px 10px" onchange="this.form.submit()">
          <option value="">Toutes catégories</option>
          <?php foreach($categories as $c): ?>
          <option value="<?=$c['id']?>" <?=$catFilter==$c['id']?'selected':''?>><?= $c['icone'].' '.htmlspecialchars($c['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
  <form method="POST">
  <div style="overflow-x:auto">
    <table class="table-omega">
      <thead>
        <tr>
          <th>Produit</th><th>Catégorie</th>
          <th>Stock Théorique</th><th>Stock Physique Compté</th>
          <th>Écart Prévu</th><th>Motif</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($produits as $p): ?>
      <tr>
        <td><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
        <td><small style="color:<?= htmlspecialchars($p['couleur']??'#888') ?>"><?= htmlspecialchars($p['cat_nom']??'') ?></small></td>
        <td>
          <input type="hidden" name="stock_theorique[<?=$p['id']?>]" value="<?=$p['stock_actuel']?>">
          <strong style="color:var(--or)"><?= number_format($p['stock_actuel'],3) ?></strong>
          <small style="color:var(--muted)"> <?= htmlspecialchars($p['unite']) ?></small>
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="stock_physique[<?=$p['id']?>]"
              class="form-control form-omega stock-physique"
              data-theorique="<?=$p['stock_actuel']?>"
              data-id="<?=$p['id']?>"
              step="0.001" min="0" placeholder="Comptage..."
              style="width:120px;font-size:.85rem"
              oninput="calcEcart(this)">
            <small><?= htmlspecialchars($p['unite']) ?></small>
          </div>
        </td>
        <td id="ecart<?=$p['id']?>" style="font-weight:700;color:var(--muted)">—</td>
        <td>
          <input type="text" name="motif[<?=$p['id']?>]" class="form-control form-omega"
            placeholder="Casse, vol, erreur..." style="width:160px;font-size:.8rem"
            value="Inventaire physique <?= date('d/m/Y') ?>">
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:15px;border-top:1px solid var(--border);display:flex;gap:10px;align-items:center">
    <button type="submit" name="ajuster" class="btn-omega btn-omega-primary">
      <i class="fas fa-save"></i> Valider l'Inventaire et Ajuster les Stocks
    </button>
    <span style="color:var(--muted);font-size:.8rem">⚠️ Cette action met à jour définitivement les stocks</span>
  </div>
  </form>
</div>

<!-- HISTORIQUE AJUSTEMENTS -->
<div class="card-omega">
  <div class="card-head"><h4><i class="fas fa-history"></i> Historique des Ajustements</h4></div>
  <div style="overflow-x:auto">
    <table class="table-omega">
      <thead><tr><th>Date</th><th>Produit</th><th>Théorique</th><th>Physique</th><th>Écart</th><th>Motif</th></tr></thead>
      <tbody>
      <?php if(empty($derniers)): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">Aucun inventaire enregistré</td></tr>
      <?php else: foreach($derniers as $d): ?>
      <tr>
        <td><small><?= date('d/m/Y H:i',strtotime($d['created_at'])) ?></small></td>
        <td><?= htmlspecialchars($d['prod_nom']) ?></td>
        <td><?= number_format($d['stock_theorique'],3) ?> <?= htmlspecialchars($d['unite']) ?></td>
        <td><?= number_format($d['stock_physique'],3) ?></td>
        <td style="font-weight:700;color:<?= $d['ecart']>0?'#27ae60':($d['ecart']<0?'#e74c3c':'var(--muted)') ?>">
          <?= $d['ecart']>0?'+':'' ?><?= number_format($d['ecart'],3) ?>
        </td>
        <td><small style="color:var(--muted)"><?= htmlspecialchars($d['motif']??'—') ?></small></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function calcEcart(input) {
  const theorique = parseFloat(input.dataset.theorique) || 0;
  const physique  = parseFloat(input.value) || 0;
  const ecart = physique - theorique;
  const cell = document.getElementById('ecart' + input.dataset.id);
  cell.textContent = (ecart >= 0 ? '+' : '') + ecart.toFixed(3);
  cell.style.color = ecart > 0 ? '#27ae60' : ecart < 0 ? '#e74c3c' : 'var(--muted)';
}
</script>

<?php require_once 'footer.php'; ?>
