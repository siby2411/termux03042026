<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Dépenses';
$pdo = getPDO();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM depenses WHERE id=?")->execute([$id]);
    flash('Dépense supprimée.', 'success');
    secureRedirect('depenses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        'libelle'     => trim($_POST['libelle']),
        'montant'     => (float)$_POST['montant'],
        'categorie'   => $_POST['categorie'],
        'date_depense'=> $_POST['date_depense'],
        'description' => trim($_POST['description'] ?? ''),
    ];
    if (!$d['libelle'] || $d['montant'] <= 0) { flash('Libellé et montant requis.', 'error'); secureRedirect('depenses.php?action='.($id?'edit':'add').'&id='.$id); }
    if ($id) {
        $pdo->prepare("UPDATE depenses SET libelle=?,montant=?,categorie=?,date_depense=?,description=? WHERE id=?")
            ->execute(array_merge(array_values($d), [$id]));
        flash('Dépense mise à jour.', 'success');
    } else {
        $pdo->prepare("INSERT INTO depenses (libelle,montant,categorie,date_depense,description) VALUES (?,?,?,?,?)")
            ->execute(array_values($d));
        flash('Dépense enregistrée.', 'success');
    }
    secureRedirect('depenses.php');
}

$dep = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM depenses WHERE id=?"); $s->execute([$id]); $dep = $s->fetch();
}

$cats = ['salaire'=>'💼 Salaires','loyer'=>'🏠 Loyer','electricite'=>'⚡ Électricité',
  'eau'=>'💧 Eau','transport'=>'🚗 Transport','emballage'=>'📦 Emballages',
  'maintenance'=>'🔧 Maintenance','publicite'=>'📢 Publicité','divers'=>'🔹 Divers'];

$catColors = ['salaire'=>'#9b59b6','loyer'=>'#e67e22','electricite'=>'#f1c40f',
  'eau'=>'#3498db','transport'=>'#27ae60','emballage'=>'#95a5a6',
  'maintenance'=>'#e74c3c','publicite'=>'#1abc9c','divers'=>'#7f8c8d'];

// Filtres
$mois = $_GET['mois'] ?? date('Y-m');
$catF = $_GET['cat'] ?? '';
$sql = "SELECT * FROM depenses WHERE DATE_FORMAT(date_depense,'%Y-%m')=?";
$params = [$mois];
if ($catF) { $sql .= " AND categorie=?"; $params[] = $catF; }
$sql .= " ORDER BY date_depense DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$depenses = $stmt->fetchAll();

$totalMois = array_sum(array_column($depenses,'montant'));

// Par catégorie
$parCat = $pdo->prepare("SELECT categorie,SUM(montant) as tot FROM depenses WHERE DATE_FORMAT(date_depense,'%Y-%m')=? GROUP BY categorie ORDER BY tot DESC");
$parCat->execute([$mois]);
$parCat = $parCat->fetchAll();

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-money-bill-wave" style="color:var(--or)"></i> <span>Dépenses</span></h1>
  <p>Gestion et suivi des charges de l'entreprise</p>
</div>

<?php if (in_array($action, ['add','edit'])): ?>
<div class="card-omega" style="max-width:650px">
  <div class="card-head">
    <h4><i class="fas fa-<?=$id?'edit':'plus'?>"></i> <?=$id?'Modifier':'Nouvelle'?> Dépense</h4>
    <a href="depenses.php" class="btn-omega btn-omega-outline">← Retour</a>
  </div>
  <div class="card-body">
    <form method="POST" class="form-omega">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Libellé *</label>
          <input type="text" name="libelle" class="form-control" required placeholder="Ex: Loyer mensuel local"
            value="<?= htmlspecialchars($dep['libelle'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Montant (FCFA) *</label>
          <input type="number" name="montant" class="form-control" min="1" step="1" required
            value="<?= $dep['montant'] ?? '' ?>" placeholder="0">
        </div>
        <div class="col-md-6">
          <label class="form-label">Catégorie</label>
          <select name="categorie" class="form-select">
            <?php foreach($cats as $k=>$v): ?>
            <option value="<?=$k?>" <?= ($dep['categorie']??'divers')===$k?'selected':'' ?>><?=$v?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Date *</label>
          <input type="date" name="date_depense" class="form-control" required
            value="<?= $dep['date_depense'] ?? date('Y-m-d') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Détails supplémentaires..."><?= htmlspecialchars($dep['description'] ?? '') ?></textarea>
        </div>
      </div>
      <div style="margin-top:20px;display:flex;gap:10px">
        <button type="submit" class="btn-omega btn-omega-primary"><i class="fas fa-save"></i> Enregistrer</button>
        <a href="depenses.php" class="btn-omega btn-omega-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<div class="row g-4">
  <div class="col-lg-4">
    <!-- RÉSUMÉ PAR CATÉGORIE -->
    <div class="card-omega" style="margin-bottom:20px">
      <div class="card-head"><h4><i class="fas fa-chart-pie"></i> Répartition <?= $mois ?></h4></div>
      <div class="card-body">
        <canvas id="chartDep" height="200"></canvas>
        <?php if(empty($parCat)): ?>
          <p style="text-align:center;color:var(--muted);padding:20px">Aucune dépense ce mois</p>
        <?php else: ?>
        <div style="margin-top:15px">
          <?php foreach($parCat as $pc): $pct = $totalMois>0?($pc['tot']/$totalMois*100):0; ?>
          <div style="margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px">
              <span><?= $cats[$pc['categorie']] ?? $pc['categorie'] ?></span>
              <strong style="color:var(--or)"><?= number_format($pc['tot'],0,',',' ') ?> FCFA</strong>
            </div>
            <div style="background:rgba(255,255,255,.08);border-radius:20px;height:6px">
              <div style="height:6px;width:<?=$pct?>%;background:<?=$catColors[$pc['categorie']]??'#888'?>;border-radius:20px"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div style="margin-top:15px;padding:12px;background:rgba(192,57,43,.1);border:1px solid rgba(192,57,43,.2);border-radius:10px;text-align:center">
          <div style="font-size:.75rem;color:var(--muted)">TOTAL DÉPENSES</div>
          <div style="font-size:1.5rem;font-weight:900;color:#e74c3c;font-family:'Playfair Display',serif"><?= number_format($totalMois,0,',',' ') ?> FCFA</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <!-- FILTRES + LISTE -->
    <div class="card-omega" style="margin-bottom:15px">
      <div class="card-body" style="padding:15px">
        <form method="GET" class="form-omega" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
          <div>
            <label class="form-label">Mois</label>
            <input type="month" name="mois" class="form-control" value="<?=$mois?>">
          </div>
          <div>
            <label class="form-label">Catégorie</label>
            <select name="cat" class="form-select">
              <option value="">Toutes</option>
              <?php foreach($cats as $k=>$v): ?>
              <option value="<?=$k?>" <?=$catF===$k?'selected':''?>><?=$v?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="align-self:flex-end"><button type="submit" class="btn-omega btn-omega-gold">Filtrer</button></div>
          <div style="align-self:flex-end"><a href="depenses.php" class="btn-omega btn-omega-outline">Reset</a></div>
          <div style="align-self:flex-end;margin-left:auto">
            <a href="depenses.php?action=add" class="btn-omega btn-omega-primary"><i class="fas fa-plus"></i> Nouvelle dépense</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-list"></i> Dépenses – <?= $mois ?></h4></div>
      <div style="overflow-x:auto">
        <table class="table-omega">
          <thead><tr><th>Date</th><th>Libellé</th><th>Catégorie</th><th>Montant</th><th>Description</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if(empty($depenses)): ?>
              <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:40px">Aucune dépense ce mois</td></tr>
            <?php else: foreach($depenses as $d): ?>
            <tr>
              <td><small><?= date('d/m/Y',strtotime($d['date_depense'])) ?></small></td>
              <td><strong><?= htmlspecialchars($d['libelle']) ?></strong></td>
              <td>
                <span style="background:<?=$catColors[$d['categorie']]??'#888'?>22;color:<?=$catColors[$d['categorie']]??'#888'?>;
                  border:1px solid <?=$catColors[$d['categorie']]??'#888'?>44;
                  font-size:.72rem;padding:3px 10px;border-radius:20px;font-weight:700">
                  <?= $cats[$d['categorie']] ?? $d['categorie'] ?>
                </span>
              </td>
              <td><strong style="color:#e74c3c"><?= number_format($d['montant'],0,',',' ') ?> FCFA</strong></td>
              <td><small style="color:var(--muted)"><?= htmlspecialchars(mb_substr($d['description']??'—',0,40)) ?></small></td>
              <td>
                <div style="display:flex;gap:5px">
                  <a href="depenses.php?action=edit&id=<?=$d['id']?>" class="btn-omega btn-omega-gold" style="padding:4px 9px;font-size:.72rem"><i class="fas fa-edit"></i></a>
                  <a href="depenses.php?action=delete&id=<?=$d['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:4px 9px;font-size:.72rem"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <?php if(!empty($depenses)): ?>
          <tfoot>
            <tr style="background:rgba(192,57,43,.05)">
              <td colspan="3"><strong>TOTAL DU MOIS</strong></td>
              <td colspan="3"><strong style="color:#e74c3c"><?= number_format($totalMois,0,',',' ') ?> FCFA</strong></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
<?php if(!empty($parCat)): ?>
new Chart(document.getElementById('chartDep'),{
  type:'doughnut',
  data:{
    labels:[<?= implode(',',array_map(fn($c)=>"'".addslashes($cats[$c['categorie']]??$c['categorie'])."'",$parCat)) ?>],
    datasets:[{
      data:[<?= implode(',',array_column($parCat,'tot')) ?>],
      backgroundColor:[<?= implode(',',array_map(fn($c)=>"'".($catColors[$c['categorie']]??'#888')."'",$parCat)) ?>],
      borderWidth:2,borderColor:'#1a1a1a'
    }]
  },
  options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:10},padding:8,color:'#888'}}}}
});
<?php endif; ?>
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
