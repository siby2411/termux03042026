<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Produits';
$pdo = getPDO();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$msg = '';

// ── DELETE ──
if ($action === 'delete' && $id) {
    $p = $pdo->prepare("SELECT image FROM produits WHERE id=?");
    $p->execute([$id]);
    $prod = $p->fetch();
    if ($prod && $prod['image']) @unlink(UPLOAD_DIR . $prod['image']);
    $pdo->prepare("DELETE FROM produits WHERE id=?")->execute([$id]);
    flash('Produit supprimé.', 'success');
    secureRedirect('produits.php');
}

// ── SAVE (INSERT/UPDATE) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'categorie_id' => (int)$_POST['categorie_id'],
        'nom'          => trim($_POST['nom']),
        'description'  => trim($_POST['description']),
        'prix_vente'   => (float)$_POST['prix_vente'],
        'prix_achat'   => (float)$_POST['prix_achat'],
        'stock_actuel' => (float)$_POST['stock_actuel'],
        'stock_min'    => (float)$_POST['stock_min'],
        'unite'        => trim($_POST['unite']),
        'actif'        => isset($_POST['actif']) ? 1 : 0,
    ];
    $imgFile = $_FILES['image'] ?? null;
    $imgName = null;
    if ($imgFile && $imgFile['size'] > 0) {
        $imgName = uploadImage($imgFile, 'prod');
        if (!$imgName) { flash('Format image invalide (jpg/png/gif/webp, max 5Mo)', 'error'); secureRedirect('produits.php?action='.($id?'edit':'add').'&id='.$id); }
    }

    if ($id) {
        // UPDATE
        $sql = "UPDATE produits SET categorie_id=:cat,nom=:nom,description=:desc,prix_vente=:pv,prix_achat=:pa,stock_actuel=:sa,stock_min=:sm,unite=:un,actif=:actif";
        $params = ['cat'=>$data['categorie_id'],'nom'=>$data['nom'],'desc'=>$data['description'],
          'pv'=>$data['prix_vente'],'pa'=>$data['prix_achat'],'sa'=>$data['stock_actuel'],
          'sm'=>$data['stock_min'],'un'=>$data['unite'],'actif'=>$data['actif']];
        if ($imgName) { $sql .= ",image=:img"; $params['img'] = $imgName; }
        $sql .= " WHERE id=:id"; $params['id'] = $id;
        $pdo->prepare($sql)->execute($params);
        flash('Produit mis à jour.', 'success');
    } else {
        $pdo->prepare("INSERT INTO produits (categorie_id,nom,description,prix_vente,prix_achat,stock_actuel,stock_min,unite,actif,image)
          VALUES (:cat,:nom,:desc,:pv,:pa,:sa,:sm,:un,:actif,:img)")
          ->execute(['cat'=>$data['categorie_id'],'nom'=>$data['nom'],'desc'=>$data['description'],
            'pv'=>$data['prix_vente'],'pa'=>$data['prix_achat'],'sa'=>$data['stock_actuel'],
            'sm'=>$data['stock_min'],'un'=>$data['unite'],'actif'=>$data['actif'],'img'=>$imgName]);
        flash('Produit ajouté avec succès.', 'success');
    }
    secureRedirect('produits.php');
}

// ── LOAD FOR EDIT ──
$prod = null;
if (in_array($action, ['edit','view']) && $id) {
    $s = $pdo->prepare("SELECT * FROM produits WHERE id=?"); $s->execute([$id]); $prod = $s->fetch();
    if (!$prod) { flash('Produit introuvable.', 'error'); secureRedirect('produits.php'); }
}

$cats = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

// ── LIST ──
$search = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);
$sql = "SELECT p.*,c.nom as cat_nom,c.couleur as cat_color FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id WHERE 1";
$params = [];
if ($search) { $sql .= " AND p.nom LIKE :q"; $params['q'] = "%$search%"; }
if ($catFilter) { $sql .= " AND p.categorie_id=:cat"; $params['cat'] = $catFilter; }
$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$produits = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fas fa-box-open" style="color:var(--or)"></i> <span>Produits</span></h1>
  <p><?= count($produits) ?> produit<?= count($produits)>1?'s':'' ?> dans le catalogue</p>
</div>

<?php if (in_array($action,['add','edit'])): ?>
<!-- ══ FORMULAIRE ══ -->
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-<?= $id?'edit':'plus' ?>"></i> <?= $id ? 'Modifier' : 'Ajouter' ?> un Produit</h4>
    <a href="produits.php" class="btn-omega btn-omega-outline">← Retour</a>
  </div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data" class="form-omega">
      <div class="row g-3">
        <div class="col-md-8">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Nom du produit *</label>
              <input type="text" name="nom" class="form-control" required placeholder="Ex: Saucisson Sec Artisanal"
                value="<?= htmlspecialchars($prod['nom']??'') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Catégorie</label>
              <select name="categorie_id" class="form-select">
                <option value="">-- Catégorie --</option>
                <?php foreach($cats as $c): ?>
                <option value="<?=$c['id']?>" <?= ($prod['categorie_id']??0)==$c['id']?'selected':'' ?>><?= $c['icone'].' '.htmlspecialchars($c['nom']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Description du produit..."><?= htmlspecialchars($prod['description']??'') ?></textarea>
            </div>
            <div class="col-md-3">
              <label class="form-label">Prix de vente (FCFA) *</label>
              <input type="number" name="prix_vente" class="form-control" step="0.01" min="0" required
                value="<?= $prod['prix_vente']??'' ?>" placeholder="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Prix d'achat (FCFA)</label>
              <input type="number" name="prix_achat" class="form-control" step="0.01" min="0"
                value="<?= $prod['prix_achat']??'' ?>" placeholder="0">
            </div>
            <div class="col-md-2">
              <label class="form-label">Stock actuel</label>
              <input type="number" name="stock_actuel" class="form-control" step="0.001" min="0"
                value="<?= $prod['stock_actuel']??'0' ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label">Stock min.</label>
              <input type="number" name="stock_min" class="form-control" step="0.001" min="0"
                value="<?= $prod['stock_min']??'1' ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label">Unité</label>
              <select name="unite" class="form-select">
                <?php foreach(['kg','g','litre','pièce','boite','pot','plaquette','rouleau','pack','bocal','bouteille'] as $u): ?>
                <option <?= ($prod['unite']??'kg')===$u?'selected':'' ?>><?=$u?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="actif" id="actif" <?= ($prod['actif']??1)?'checked':'' ?>>
                <label class="form-check-label" for="actif" style="color:#ccc">Produit actif (visible sur le site)</label>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Image du produit</label>
          <div style="border:2px dashed rgba(255,255,255,.1);border-radius:12px;padding:20px;text-align:center;cursor:pointer"
            onclick="document.getElementById('imgInput').click()">
            <?php if(!empty($prod['image'])): ?>
              <img src="../<?= UPLOAD_URL.htmlspecialchars($prod['image']) ?>" id="imgPreview"
                style="max-width:100%;max-height:200px;border-radius:8px;margin-bottom:10px" onerror="this.style.display='none'">
            <?php else: ?>
              <img id="imgPreview" style="max-width:100%;max-height:200px;border-radius:8px;display:none;margin-bottom:10px">
              <div id="imgPlaceholder" style="font-size:3rem;margin-bottom:10px">📷</div>
            <?php endif; ?>
            <p style="color:var(--muted);font-size:.8rem">Cliquez pour choisir une image<br>JPG, PNG, WEBP – Max 5Mo</p>
          </div>
          <input type="file" id="imgInput" name="image" accept="image/*" style="display:none"
            onchange="previewImg(this)">
          <div style="margin-top:15px;padding:15px;background:rgba(212,172,13,.08);border:1px solid rgba(212,172,13,.2);border-radius:10px;font-size:.78rem;color:#b7950b">
            <strong>💡 Marge bénéficiaire :</strong><br>
            <span id="margeInfo">Renseignez les prix pour calculer</span>
          </div>
        </div>
      </div>
      <div style="margin-top:25px;padding-top:20px;border-top:1px solid var(--border);display:flex;gap:12px">
        <button type="submit" class="btn-omega btn-omega-primary">
          <i class="fas fa-save"></i> <?= $id?'Mettre à jour':'Enregistrer' ?>
        </button>
        <a href="produits.php" class="btn-omega btn-omega-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>

<script>
function previewImg(input){
  if(input.files && input.files[0]){
    const r=new FileReader();
    r.onload=e=>{
      const pr=document.getElementById('imgPreview');
      const pl=document.getElementById('imgPlaceholder');
      pr.src=e.target.result;pr.style.display='block';
      if(pl)pl.style.display='none';
    };r.readAsDataURL(input.files[0]);
  }
}
function calcMarge(){
  const pv=parseFloat(document.querySelector('[name=prix_vente]').value)||0;
  const pa=parseFloat(document.querySelector('[name=prix_achat]').value)||0;
  const m=document.getElementById('margeInfo');
  if(pv>0&&pa>0){
    const marge=pv-pa;
    const pct=((marge/pa)*100).toFixed(1);
    m.innerHTML=`Marge: <strong style="color:${marge>=0?'#27ae60':'#e74c3c'}">${marge.toLocaleString()} FCFA (${pct}%)</strong>`;
  }
}
document.querySelector('[name=prix_vente]').addEventListener('input',calcMarge);
document.querySelector('[name=prix_achat]').addEventListener('input',calcMarge);
calcMarge();
</script>

<?php else: ?>
<!-- ══ LISTE ══ -->
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-list"></i> Liste des Produits</h4>
    <a href="produits.php?action=add" class="btn-omega btn-omega-primary">
      <i class="fas fa-plus"></i> Nouveau produit
    </a>
  </div>
  <div class="card-body" style="padding:15px">
    <form method="GET" class="form-omega" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px">
      <input type="text" name="q" class="form-control" placeholder="🔍 Rechercher un produit..." value="<?= htmlspecialchars($search) ?>" style="max-width:300px">
      <select name="cat" class="form-select" style="max-width:200px">
        <option value="">Toutes catégories</option>
        <?php foreach($cats as $c): ?>
        <option value="<?=$c['id']?>" <?=$catFilter==$c['id']?'selected':''?>><?= $c['icone'].' '.htmlspecialchars($c['nom']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-omega btn-omega-gold">Filtrer</button>
      <a href="produits.php" class="btn-omega btn-omega-outline">Reset</a>
    </form>
  </div>
  <div style="overflow-x:auto">
    <table class="table-omega">
      <thead>
        <tr><th>Image</th><th>Produit</th><th>Catégorie</th><th>P. Vente</th><th>P. Achat</th><th>Marge</th><th>Stock</th><th>Unité</th><th>Statut</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if(empty($produits)): ?>
        <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:40px">Aucun produit trouvé</td></tr>
        <?php else: foreach($produits as $p):
          $marge = $p['prix_vente'] - $p['prix_achat'];
          $margePct = $p['prix_achat'] > 0 ? ($marge/$p['prix_achat'])*100 : 0;
          $stockOk = $p['stock_actuel'] > $p['stock_min'];
          $stockLow = $p['stock_actuel'] > 0 && $p['stock_actuel'] <= $p['stock_min'];
        ?>
        <tr>
          <td>
            <?php if($p['image']): ?>
              <img src="../<?= UPLOAD_URL.htmlspecialchars($p['image']) ?>" class="img-thumb" onerror="this.outerHTML='<div class=img-placeholder>🥩</div>'">
            <?php else: ?><div class="img-placeholder">🥩</div><?php endif; ?>
          </td>
          <td>
            <strong><?= htmlspecialchars($p['nom']) ?></strong><br>
            <small style="color:var(--muted)"><?= htmlspecialchars(mb_substr($p['description']??'',0,50)) ?>...</small>
          </td>
          <td>
            <span style="color:<?= htmlspecialchars($p['cat_color']??'#888') ?>;font-size:.8rem;font-weight:600"><?= htmlspecialchars($p['cat_nom']??'—') ?></span>
          </td>
          <td><strong style="color:var(--or)"><?= number_format($p['prix_vente'],0,',',' ') ?></strong></td>
          <td><?= number_format($p['prix_achat'],0,',',' ') ?></td>
          <td style="color:<?= $marge>=0?'#27ae60':'#e74c3c' ?>">
            <?= number_format($marge,0,',',' ') ?><br>
            <small>(<?= number_format($margePct,1) ?>%)</small>
          </td>
          <td>
            <?php if($p['stock_actuel'] <= 0): ?>
              <span style="color:#e74c3c;font-weight:700">0.00</span>
            <?php elseif($stockLow): ?>
              <span style="color:#e67e22;font-weight:700"><?= number_format($p['stock_actuel'],2) ?></span>
            <?php else: ?>
              <span style="color:#27ae60"><?= number_format($p['stock_actuel'],2) ?></span>
            <?php endif; ?>
          </td>
          <td><small><?= htmlspecialchars($p['unite']) ?></small></td>
          <td>
            <?php if($p['actif']): ?>
              <span class="badge-stat b-success">✓ Actif</span>
            <?php else: ?>
              <span class="badge-stat b-muted">✗ Inactif</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px">
              <a href="produits.php?action=edit&id=<?=$p['id']?>" class="btn-omega btn-omega-gold" style="padding:6px 12px;font-size:.75rem" title="Modifier"><i class="fas fa-edit"></i></a>
              <a href="produits.php?action=delete&id=<?=$p['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:6px 12px;font-size:.75rem" title="Supprimer"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
