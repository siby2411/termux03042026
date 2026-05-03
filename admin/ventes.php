<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Ventes & Caisse';
$pdo = getPDO();

// ── SAVE VENTE RAPIDE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodId  = (int)$_POST['produit_id'];
    $clientId= (int)($_POST['client_id'] ?: 0);
    $qte     = (float)$_POST['quantite'];
    $pu      = (float)$_POST['prix_unitaire'];
    $date    = $_POST['date_vente'] ?: date('Y-m-d H:i:s');
    if ($prodId && $qte > 0 && $pu > 0) {
        $total = $qte * $pu;
        $pdo->prepare("INSERT INTO ventes (produit_id,client_id,quantite,prix_unitaire,total,date_vente)
            VALUES (?,?,?,?,?,?)")->execute([$prodId, $clientId ?: null, $qte, $pu, $total, $date]);
        $pdo->prepare("UPDATE produits SET stock_actuel=GREATEST(0,stock_actuel-?) WHERE id=?")->execute([$qte, $prodId]);
        flash('Vente enregistrée : '.number_format($total,0,',',' ').' FCFA', 'success');
    } else {
        flash('Données invalides.', 'error');
    }
    secureRedirect('ventes.php');
}

// ── DELETE ──
if (isset($_GET['del'])) {
    $vid = (int)$_GET['del'];
    $v = $pdo->prepare("SELECT * FROM ventes WHERE id=?"); $v->execute([$vid]); $vd = $v->fetch();
    if ($vd) {
        $pdo->prepare("UPDATE produits SET stock_actuel=stock_actuel+? WHERE id=?")->execute([$vd['quantite'], $vd['produit_id']]);
        $pdo->prepare("DELETE FROM ventes WHERE id=?")->execute([$vid]);
        flash('Vente annulée, stock restauré.', 'success');
    }
    secureRedirect('ventes.php');
}

$produits = $pdo->query("SELECT * FROM produits WHERE actif=1 ORDER BY nom")->fetchAll();
$clients  = $pdo->query("SELECT * FROM clients ORDER BY nom")->fetchAll();

// ── Filtres ──
$dateFrom = $_GET['from'] ?? date('Y-m-01');
$dateTo   = $_GET['to']   ?? date('Y-m-d');
$prodFilter = (int)($_GET['prod'] ?? 0);

$sql = "SELECT v.*,p.nom as prod_nom,p.unite,COALESCE(CONCAT(c.prenom,' ',c.nom),'Comptoir') as client_nom
    FROM ventes v LEFT JOIN produits p ON v.produit_id=p.id LEFT JOIN clients c ON v.client_id=c.id
    WHERE DATE(v.date_vente) BETWEEN :from AND :to";
$params = ['from'=>$dateFrom,'to'=>$dateTo];
if ($prodFilter) { $sql .= " AND v.produit_id=:prod"; $params['prod']=$prodFilter; }
$sql .= " ORDER BY v.date_vente DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$ventes = $stmt->fetchAll();

$totalPeriode = array_sum(array_column($ventes,'total'));
$qttePeriode  = array_sum(array_column($ventes,'quantite'));
$nbVentes     = count($ventes);

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fas fa-cash-register" style="color:var(--or)"></i> <span>Ventes & Caisse</span></h1>
  <p>Enregistrement rapide des ventes et historique</p>
</div>

<div class="row g-4">
  <!-- FORMULAIRE VENTE RAPIDE -->
  <div class="col-lg-4">
    <div class="card-omega" style="position:sticky;top:80px">
      <div class="card-head">
        <h4><i class="fas fa-plus-circle" style="color:#27ae60"></i> Vente Rapide</h4>
      </div>
      <div class="card-body">
        <form method="POST" class="form-omega">
          <div class="mb-3">
            <label class="form-label">Produit *</label>
            <select name="produit_id" class="form-select" id="prodSel" onchange="setPrix()" required>
              <option value="">-- Choisir un produit --</option>
              <?php foreach($produits as $p): ?>
              <option value="<?=$p['id']?>" data-prix="<?=$p['prix_vente']?>" data-stock="<?=$p['stock_actuel']?>" data-unite="<?=$p['unite']?>">
                <?= htmlspecialchars($p['nom']) ?> [Stock: <?= number_format($p['stock_actuel'],2) ?> <?=$p['unite']?>]
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Client</label>
            <select name="client_id" class="form-select">
              <option value="">Client comptoir</option>
              <?php foreach($clients as $c): ?>
              <option value="<?=$c['id']?>"><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Quantité *</label>
              <input type="number" name="quantite" id="venteQte" class="form-control" min="0.001" step="0.001" required placeholder="0.000" oninput="calcVente()">
            </div>
            <div class="col-6">
              <label class="form-label">Prix unitaire (FCFA)</label>
              <input type="number" name="prix_unitaire" id="ventePU" class="form-control" min="0" step="0.01" required placeholder="0" oninput="calcVente()">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Date / Heure</label>
            <input type="datetime-local" name="date_vente" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
          </div>
          <!-- TOTAL CALCULÉ -->
          <div style="background:linear-gradient(135deg,rgba(212,172,13,.1),rgba(192,57,43,.1));border:1px solid rgba(212,172,13,.2);border-radius:12px;padding:15px;margin-bottom:20px;text-align:center">
            <div style="font-size:.75rem;color:var(--muted);margin-bottom:5px">TOTAL VENTE</div>
            <div id="venteTotal" style="font-size:1.8rem;font-weight:900;color:var(--or);font-family:'Playfair Display',serif">0 FCFA</div>
            <div id="stockInfo" style="font-size:.75rem;color:var(--muted);margin-top:5px"></div>
          </div>
          <button type="submit" class="btn-omega btn-omega-success w-100">
            <i class="fas fa-check"></i> Enregistrer la Vente
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- HISTORIQUE -->
  <div class="col-lg-8">
    <!-- KPI PÉRIODE -->
    <div class="row g-3 mb-4">
      <div class="col-4">
        <div class="stat-card" style="--color1:#c0392b;--color2:#e74c3c">
          <div class="label">CA Période</div>
          <div class="value" style="font-size:1.3rem"><?= number_format($totalPeriode,0,',',' ') ?></div>
          <small style="color:var(--muted)">FCFA</small>
        </div>
      </div>
      <div class="col-4">
        <div class="stat-card" style="--color1:#d4ac0d;--color2:#f1c40f">
          <div class="label">Nb Transactions</div>
          <div class="value"><?= $nbVentes ?></div>
          <small style="color:var(--muted)">ventes</small>
        </div>
      </div>
      <div class="col-4">
        <div class="stat-card" style="--color1:#27ae60;--color2:#2ecc71">
          <div class="label">Panier Moyen</div>
          <div class="value" style="font-size:1.3rem"><?= $nbVentes>0?number_format($totalPeriode/$nbVentes,0,',',' '):'0' ?></div>
          <small style="color:var(--muted)">FCFA</small>
        </div>
      </div>
    </div>

    <!-- FILTRES -->
    <div class="card-omega" style="margin-bottom:15px">
      <div class="card-body" style="padding:15px">
        <form method="GET" class="form-omega" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
          <div>
            <label class="form-label">Du</label>
            <input type="date" name="from" class="form-control" value="<?=$dateFrom?>">
          </div>
          <div>
            <label class="form-label">Au</label>
            <input type="date" name="to" class="form-control" value="<?=$dateTo?>">
          </div>
          <div>
            <label class="form-label">Produit</label>
            <select name="prod" class="form-select">
              <option value="">Tous</option>
              <?php foreach($produits as $p): ?>
              <option value="<?=$p['id']?>" <?=$prodFilter==$p['id']?'selected':''?>><?= htmlspecialchars($p['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn-omega btn-omega-gold">Filtrer</button>
          <a href="ventes.php" class="btn-omega btn-omega-outline">Reset</a>
        </form>
      </div>
    </div>

    <!-- TABLE VENTES -->
    <div class="card-omega">
      <div class="card-head">
        <h4><i class="fas fa-history"></i> Historique des Ventes</h4>
        <small style="color:var(--muted)"><?= date('d/m/Y',strtotime($dateFrom)).' – '.date('d/m/Y',strtotime($dateTo)) ?></small>
      </div>
      <div style="overflow-x:auto">
        <table class="table-omega">
          <thead><tr><th>Date</th><th>Produit</th><th>Client</th><th>Qté</th><th>P.U.</th><th>Total</th><th>Action</th></tr></thead>
          <tbody>
            <?php if(empty($ventes)): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px">Aucune vente sur cette période</td></tr>
            <?php else: foreach($ventes as $v): ?>
            <tr>
              <td><small><?= date('d/m/Y H:i',strtotime($v['date_vente'])) ?></small></td>
              <td><?= htmlspecialchars($v['prod_nom']??'—') ?></td>
              <td><small><?= htmlspecialchars($v['client_nom']) ?></small></td>
              <td><?= number_format($v['quantite'],3) ?> <small style="color:var(--muted)"><?= htmlspecialchars($v['unite']??'') ?></small></td>
              <td><?= number_format($v['prix_unitaire'],0,',',' ') ?></td>
              <td><strong style="color:var(--or)"><?= number_format($v['total'],0,',',' ') ?></strong></td>
              <td><a href="ventes.php?del=<?=$v['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:4px 8px;font-size:.7rem"><i class="fas fa-times"></i></a></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <?php if(!empty($ventes)): ?>
          <tfoot>
            <tr style="background:rgba(212,172,13,.05)">
              <td colspan="3"><strong style="color:var(--or)">TOTAUX</strong></td>
              <td><strong><?= number_format($qttePeriode,3) ?></strong></td>
              <td>—</td>
              <td><strong style="color:var(--or)"><?= number_format($totalPeriode,0,',',' ') ?> FCFA</strong></td>
              <td></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function setPrix(){
  const sel=document.getElementById('prodSel');
  const opt=sel.options[sel.selectedIndex];
  document.getElementById('ventePU').value=opt.dataset.prix||'';
  const stock=opt.dataset.stock||'0';
  const unite=opt.dataset.unite||'';
  document.getElementById('stockInfo').textContent='Stock disponible : '+parseFloat(stock).toFixed(3)+' '+unite;
  calcVente();
}
function calcVente(){
  const q=parseFloat(document.getElementById('venteQte').value)||0;
  const p=parseFloat(document.getElementById('ventePU').value)||0;
  const t=q*p;
  document.getElementById('venteTotal').textContent=t.toLocaleString('fr')+' FCFA';
}
</script>

<?php require_once 'footer.php'; ?>
