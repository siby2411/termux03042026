<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Produits';
$pdo = getPDO();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// ── DELETE ──
if ($action === 'delete' && $id) {
    $p = $pdo->prepare("SELECT image FROM produits WHERE id=?");
    $p->execute([$id]); $prod = $p->fetch();
    if ($prod && $prod['image'] && file_exists(UPLOAD_DIR.$prod['image']))
        @unlink(UPLOAD_DIR.$prod['image']);
    $pdo->prepare("DELETE FROM produits WHERE id=?")->execute([$id]);
    flash('Produit supprimé.','success');
    secureRedirect('produits.php');
}

// ── SAVE ──
if ($_SERVER['REQUEST_METHOD']==='POST') {
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
    $imgName = null;
    $imgFile = $_FILES['image'] ?? null;
    if ($imgFile && !empty($imgFile['tmp_name']) && $imgFile['size'] > 0) {
        $ext = strtolower(pathinfo($imgFile['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            flash('Format image invalide (JPG/PNG/WEBP/GIF).','error');
            secureRedirect('produits.php?action='.($id?'edit':'add').'&id='.$id);
        }
        if ($imgFile['size'] > 5*1024*1024) {
            flash('Image trop lourde (max 5Mo).','error');
            secureRedirect('produits.php?action='.($id?'edit':'add').'&id='.$id);
        }
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
        $imgName = 'prod_'.uniqid().'.'.$ext;
        if (!move_uploaded_file($imgFile['tmp_name'], UPLOAD_DIR.$imgName)) {
            flash('Erreur upload. Vérifiez permissions dossier uploads/.','error');
            $imgName = null;
        }
    }
    if ($id) {
        if ($imgName) {
            $old = $pdo->prepare("SELECT image FROM produits WHERE id=?");
            $old->execute([$id]); $oldp = $old->fetch();
            if ($oldp && $oldp['image'] && file_exists(UPLOAD_DIR.$oldp['image']))
                @unlink(UPLOAD_DIR.$oldp['image']);
        }
        $sql = "UPDATE produits SET categorie_id=:cat,nom=:nom,description=:desc,
            prix_vente=:pv,prix_achat=:pa,stock_actuel=:sa,stock_min=:sm,unite=:un,actif=:actif";
        $params = ['cat'=>$data['categorie_id'],'nom'=>$data['nom'],'desc'=>$data['description'],
            'pv'=>$data['prix_vente'],'pa'=>$data['prix_achat'],'sa'=>$data['stock_actuel'],
            'sm'=>$data['stock_min'],'un'=>$data['unite'],'actif'=>$data['actif']];
        if ($imgName) { $sql .= ",image=:img"; $params['img']=$imgName; }
        $sql .= " WHERE id=:id"; $params['id']=$id;
        $pdo->prepare($sql)->execute($params);
        flash('Produit mis a jour avec succes.','success');
    } else {
        $pdo->prepare("INSERT INTO produits
            (categorie_id,nom,description,prix_vente,prix_achat,stock_actuel,stock_min,unite,actif,image)
            VALUES (:cat,:nom,:desc,:pv,:pa,:sa,:sm,:un,:actif,:img)")
            ->execute(['cat'=>$data['categorie_id'],'nom'=>$data['nom'],'desc'=>$data['description'],
                'pv'=>$data['prix_vente'],'pa'=>$data['prix_achat'],'sa'=>$data['stock_actuel'],
                'sm'=>$data['stock_min'],'un'=>$data['unite'],'actif'=>$data['actif'],'img'=>$imgName]);
        flash('Produit ajoute avec succes.','success');
    }
    secureRedirect('produits.php');
}

$prod = null;
if (in_array($action,['edit','view']) && $id) {
    $s = $pdo->prepare("SELECT * FROM produits WHERE id=?");
    $s->execute([$id]); $prod = $s->fetch();
    if (!$prod) { flash('Produit introuvable.','error'); secureRedirect('produits.php'); }
}
$cats = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
$search    = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);
$vue       = $_GET['vue'] ?? 'grille';
$sql = "SELECT p.*,c.nom as cat_nom,c.couleur as cat_color
        FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id WHERE 1";
$params = [];
if ($search)    { $sql .= " AND (p.nom LIKE :q OR p.code_produit LIKE :q2)"; $params = [':q'=>"%$search%",':q2'=>"%$search%"]; }
if ($catFilter) { $sql .= " AND p.categorie_id=:cat"; $params[':cat']=$catFilter; }
$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$produits = $stmt->fetchAll();

$emojis = ['1'=>'🥩','2'=>'🍖','3'=>'🐓','4'=>'🍗','5'=>'🧀','6'=>'🫕','7'=>'🥚','8'=>'🥩','9'=>'🌶','10'=>'🐟','11'=>'🫙','12'=>'✨','13'=>'📦','14'=>'🧃','15'=>'🌿'];

require_once 'header.php';
?>
<style>
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.pcard{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:.3s;position:relative}
.pcard:hover{transform:translateY(-5px);box-shadow:0 15px 35px rgba(0,0,0,.35);border-color:var(--or)}
.pcard-img{width:100%;height:165px;position:relative;overflow:hidden;background:linear-gradient(135deg,rgba(192,57,43,.06),rgba(212,172,13,.04))}
.pcard-img img{width:100%;height:165px;object-fit:cover;transition:.4s}
.pcard:hover .pcard-img img{transform:scale(1.06)}
.pcard-img .emoji-placeholder{width:100%;height:165px;display:flex;align-items:center;justify-content:center;font-size:4rem}
.pcard-badge{position:absolute;top:8px;right:8px;font-size:.62rem;padding:3px 9px;border-radius:20px;font-weight:700;backdrop-filter:blur(4px)}
.pcard-actions{position:absolute;top:8px;left:8px;display:flex;gap:4px;opacity:0;transition:.3s}
.pcard:hover .pcard-actions{opacity:1}
.pcard-act-btn{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;
  font-size:.75rem;text-decoration:none;transition:.2s;backdrop-filter:blur(4px);border:none;cursor:pointer}
.pcard-body{padding:12px 14px}
.pcode{font-size:.62rem;font-family:monospace;font-weight:700;color:var(--or);
  background:rgba(212,172,13,.12);padding:2px 7px;border-radius:10px;display:inline-block;margin-bottom:5px}
.pname{font-weight:700;font-size:.88rem;margin-bottom:2px;line-height:1.3;color:var(--text);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pcat{font-size:.7rem;margin-bottom:8px}
.pprice{color:var(--or);font-weight:900;font-size:.95rem}
.punit{color:var(--muted);font-size:.65rem}
.pmarge{font-size:.65rem;padding:2px 6px;border-radius:8px;font-weight:700}
.m-ok{background:rgba(39,174,96,.15);color:#27ae60}
.m-mid{background:rgba(230,126,34,.15);color:#e67e22}
.m-bad{background:rgba(192,57,43,.15);color:#e74c3c}
.pstock{font-size:.72rem;margin-top:6px;padding-top:6px;border-top:1px solid var(--border)}
/* Upload */
.upzone{border:2px dashed rgba(255,255,255,.15);border-radius:14px;padding:25px 15px;
  text-align:center;cursor:pointer;transition:.3s;background:rgba(255,255,255,.02);min-height:200px;
  display:flex;flex-direction:column;align-items:center;justify-content:center}
.upzone:hover,.upzone.drag{border-color:var(--or);background:rgba(212,172,13,.06)}
.upzone img{max-width:100%;max-height:190px;border-radius:10px;object-fit:cover;border:2px solid var(--or)}
.vue-btn{padding:7px 16px;border-radius:8px;border:1px solid var(--border);background:rgba(255,255,255,.04);
  color:#888;cursor:pointer;font-size:.8rem;transition:.2s;text-decoration:none}
.vue-btn.on{background:var(--or);color:var(--noir);border-color:var(--or)}
.btn-add-big{background:linear-gradient(135deg,#27ae60,#1e8449);color:#fff;
  padding:12px 26px;border-radius:12px;text-decoration:none;font-weight:700;font-size:.92rem;
  display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 15px rgba(39,174,96,.3);transition:.3s}
.btn-add-big:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(39,174,96,.4);color:#fff}
.btn-facture-big{background:linear-gradient(135deg,#2980b9,#1a5276);color:#fff;
  padding:12px 26px;border-radius:12px;text-decoration:none;font-weight:700;font-size:.92rem;
  display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 15px rgba(41,128,185,.3);transition:.3s}
.btn-facture-big:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(41,128,185,.4);color:#fff}
</style>

<div class="page-header">
  <h1><i class="fas fa-box-open" style="color:var(--or)"></i> <span>Produits</span></h1>
  <p><?= count($produits) ?> produit<?= count($produits)>1?'s':'' ?> — Catalogue OMEGA Charcuterie</p>
</div>

<?php if (in_array($action,['add','edit'])): ?>
<!-- ═══════════════ FORMULAIRE ═══════════════ -->
<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;align-items:center">
  <a href="produits.php" class="btn-omega btn-omega-outline"><i class="fas fa-arrow-left"></i> Liste</a>
  <a href="factures.php?action=new" class="btn-facture-big">
    <i class="fas fa-file-invoice"></i> Nouvelle Facture
  </a>
  <?php if($id): ?>
  <a href="produits.php?action=delete&id=<?=$id?>" class="btn-omega btn-omega-danger btn-delete" style="margin-left:auto">
    <i class="fas fa-trash"></i> Supprimer
  </a>
  <?php endif; ?>
</div>

<div class="card-omega">
  <div class="card-head">
    <h4>
      <i class="fas fa-<?= $id?'edit':'plus-circle' ?>"
        style="color:<?= $id?'var(--or)':'#27ae60' ?>"></i>
      <?= $id ? 'Modifier : '.htmlspecialchars($prod['nom']??'') : 'Nouveau Produit' ?>
    </h4>
    <?php if($prod && !empty($prod['code_produit'])): ?>
    <span class="pcode" style="font-size:.82rem;padding:5px 14px"><?= htmlspecialchars($prod['code_produit']) ?></span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data" class="form-omega">
      <div class="row g-4">

        <!-- ── Infos produit ── -->
        <div class="col-lg-8">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Nom du produit *</label>
              <input type="text" name="nom" class="form-control" required
                placeholder="Ex: Saucisson Sec Artisanal..."
                value="<?= htmlspecialchars($prod['nom']??'') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Categorie</label>
              <select name="categorie_id" class="form-select">
                <option value="">-- Choisir --</option>
                <?php foreach($cats as $c): ?>
                <option value="<?=$c['id']?>" <?= ($prod['categorie_id']??0)==$c['id']?'selected':'' ?>>
                  <?= htmlspecialchars($c['nom']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"
                placeholder="Description, origine, composition, allergenes..."><?= htmlspecialchars($prod['description']??'') ?></textarea>
            </div>
            <div class="col-md-3">
              <label class="form-label">Prix vente (FCFA) *</label>
              <input type="number" name="prix_vente" id="pv" class="form-control"
                step="1" min="0" required oninput="calcM()"
                value="<?= $prod['prix_vente']??'' ?>" placeholder="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Prix achat (FCFA)</label>
              <input type="number" name="prix_achat" id="pa" class="form-control"
                step="1" min="0" oninput="calcM()"
                value="<?= $prod['prix_achat']??'' ?>" placeholder="0">
            </div>
            <div class="col-md-2">
              <label class="form-label">Stock actuel</label>
              <input type="number" name="stock_actuel" class="form-control"
                step="0.001" min="0" value="<?= $prod['stock_actuel']??'0' ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label">Stock minimum</label>
              <input type="number" name="stock_min" class="form-control"
                step="0.001" min="0" value="<?= $prod['stock_min']??'1' ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label">Unite</label>
              <select name="unite" class="form-select">
                <?php foreach(['kg','g','litre','piece','boite','pot','plaquette','rouleau','pack','bocal','bouteille','sachet'] as $u): ?>
                <option <?= ($prod['unite']??'kg')===$u?'selected':'' ?>><?=$u?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <!-- Indicateur marge -->
            <div class="col-12">
              <div id="mbox" style="background:rgba(212,172,13,.08);border:1px solid rgba(212,172,13,.2);
                border-radius:10px;padding:11px 16px;display:flex;gap:20px;flex-wrap:wrap;align-items:center">
                <span style="color:var(--muted);font-size:.78rem"><i class="fas fa-chart-line" style="color:var(--or)"></i> Analyse :</span>
                <span style="font-size:.82rem">Marge : <strong id="mv" style="color:var(--or)">—</strong></span>
                <span style="font-size:.82rem">Taux : <strong id="mp" style="color:var(--or)">—</strong></span>
                <span style="font-size:.82rem">Coeff : <strong id="mc" style="color:var(--or)">—</strong></span>
              </div>
            </div>
            <div class="col-12">
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                <input type="checkbox" name="actif" <?= ($prod['actif']??1)?'checked':'' ?>
                  style="width:18px;height:18px;accent-color:var(--or)">
                <span style="color:#ccc;font-size:.85rem">Produit actif (visible dans le catalogue et la caisse)</span>
              </label>
            </div>
          </div>
        </div>

        <!-- ── Image upload ── -->
        <div class="col-lg-4">
          <label class="form-label">Photo du produit</label>
          <div class="upzone" id="upz" onclick="document.getElementById('imgInp').click()"
            ondragover="ev(event,1)" ondragleave="ev(event,0)" ondrop="drop(event)">
            <div id="prevDiv">
              <?php if(!empty($prod['image'])): ?>
                <img id="prevImg"
                  src="/charcuterie1/<?= UPLOAD_URL.htmlspecialchars($prod['image']) ?>"
                  onerror="this.style.display='none';document.getElementById('noImg').style.display='flex'"
                  style="max-width:100%;max-height:190px;border-radius:10px;object-fit:cover;border:2px solid var(--or)">
              <?php else: ?>
                <img id="prevImg" src="" style="display:none;max-width:100%;max-height:190px;border-radius:10px;border:2px solid var(--or)">
              <?php endif; ?>
            </div>
            <div id="noImg" style="display:<?= empty($prod['image'])?'flex':'none' ?>;flex-direction:column;align-items:center;gap:10px">
              <div style="font-size:3.5rem">📷</div>
              <div style="color:var(--muted);font-size:.82rem">
                <strong style="color:var(--or)">Cliquez</strong> ou glissez une image<br>
                <small>JPG PNG WEBP GIF — Max 5 Mo</small>
              </div>
            </div>
          </div>
          <input type="file" id="imgInp" name="image" accept="image/*"
            style="display:none" onchange="showPrev(this)">
          <div id="fname" style="margin-top:6px;font-size:.72rem;color:var(--muted)"></div>
          <?php if(!empty($prod['image'])): ?>
          <div style="margin-top:8px;padding:8px 12px;background:rgba(39,174,96,.08);
            border:1px solid rgba(39,174,96,.2);border-radius:8px;font-size:.72rem;color:#27ae60">
            Image actuelle enregistree. Uploadez une nouvelle pour la remplacer.
          </div>
          <?php endif; ?>
          <?php if(!empty($prod['code_barres'])): ?>
          <div style="margin-top:12px;padding:10px;background:rgba(255,255,255,.03);
            border:1px solid var(--border);border-radius:10px;text-align:center">
            <div style="font-size:.62rem;color:var(--muted);margin-bottom:4px;letter-spacing:1px">CODE BARRES</div>
            <div style="font-family:monospace;font-size:.82rem;color:var(--or);letter-spacing:2px"><?= htmlspecialchars($prod['code_barres']) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap">
        <button type="submit" class="btn-omega btn-omega-primary" style="padding:12px 28px;font-size:.95rem">
          <i class="fas fa-save"></i> <?= $id ? 'Mettre a jour' : 'Enregistrer le Produit' ?>
        </button>
        <a href="produits.php" class="btn-omega btn-omega-outline">Annuler</a>
        <a href="produits.php?action=add" class="btn-omega btn-omega-success" style="margin-left:auto">
          <i class="fas fa-plus"></i> Autre Produit
        </a>
      </div>
    </form>
  </div>
</div>

<script>
function calcM(){
  const pv=parseFloat(document.getElementById('pv').value)||0;
  const pa=parseFloat(document.getElementById('pa').value)||0;
  if(pv>0&&pa>0){
    const m=pv-pa,p=((m/pa)*100).toFixed(1),c=(pv/pa).toFixed(2);
    const col=m>=0?'#27ae60':'#e74c3c';
    document.getElementById('mv').innerHTML='<span style="color:'+col+'">'+m.toLocaleString('fr')+' FCFA</span>';
    document.getElementById('mp').innerHTML='<span style="color:'+col+'">'+p+'%</span>';
    document.getElementById('mc').innerHTML='<span style="color:'+col+'">x'+c+'</span>';
  }else{['mv','mp','mc'].forEach(i=>document.getElementById(i).textContent='—');}
}
function showPrev(inp){
  if(inp.files&&inp.files[0]){
    const f=inp.files[0];
    const r=new FileReader();
    r.onload=e=>{
      const img=document.getElementById('prevImg');
      img.src=e.target.result;img.style.display='block';
      document.getElementById('noImg').style.display='none';
      document.getElementById('fname').textContent='Fichier: '+f.name+' ('+Math.round(f.size/1024)+' Ko)';
    };r.readAsDataURL(f);
  }
}
function ev(e,on){e.preventDefault();document.getElementById('upz').classList.toggle('drag',on);}
function drop(e){
  e.preventDefault();document.getElementById('upz').classList.remove('drag');
  if(e.dataTransfer.files.length){document.getElementById('imgInp').files=e.dataTransfer.files;showPrev(document.getElementById('imgInp'));}
}
calcM();
</script>

<?php else: ?>
<!-- ═══════════════ LISTE ═══════════════ -->
<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;align-items:center">
  <a href="produits.php?action=add" class="btn-add-big">
    <i class="fas fa-plus-circle"></i> Ajouter un Produit
  </a>
  <a href="factures.php?action=new" class="btn-facture-big">
    <i class="fas fa-file-invoice"></i> Nouvelle Facture
  </a>
  <a href="caisse_pos.php" class="btn-omega btn-omega-success" style="padding:12px 22px">
    <i class="fas fa-cash-register"></i> Caisse POS
  </a>
  <div style="margin-left:auto;display:flex;gap:6px">
    <a href="?vue=grille<?= $search?"&q=$search":''?><?= $catFilter?"&cat=$catFilter":''?>"
      class="vue-btn <?= $vue==='grille'?'on':'' ?>"><i class="fas fa-th"></i> Grille</a>
    <a href="?vue=liste<?= $search?"&q=$search":''?><?= $catFilter?"&cat=$catFilter":''?>"
      class="vue-btn <?= $vue==='liste'?'on':'' ?>"><i class="fas fa-list"></i> Liste</a>
  </div>
</div>

<!-- Filtres -->
<div class="card-omega" style="margin-bottom:15px">
  <div class="card-body" style="padding:12px 16px">
    <form method="GET" class="form-omega" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <input type="hidden" name="vue" value="<?= $vue ?>">
      <div>
        <label class="form-label" style="font-size:.72rem">Recherche</label>
        <input type="text" name="q" class="form-control"
          placeholder="Nom ou code produit..."
          value="<?= htmlspecialchars($search) ?>" style="min-width:220px">
      </div>
      <div>
        <label class="form-label" style="font-size:.72rem">Categorie</label>
        <select name="cat" class="form-select" style="min-width:180px">
          <option value="">Toutes les categories</option>
          <?php foreach($cats as $c): ?>
          <option value="<?=$c['id']?>" <?= $catFilter==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="align-self:flex-end;display:flex;gap:6px">
        <button type="submit" class="btn-omega btn-omega-gold"><i class="fas fa-filter"></i> Filtrer</button>
        <a href="produits.php?vue=<?=$vue?>" class="btn-omega btn-omega-outline">Reset</a>
      </div>
      <div style="align-self:flex-end;margin-left:auto">
        <strong style="color:var(--or)"><?= count($produits) ?></strong>
        <span style="color:var(--muted);font-size:.8rem"> produit<?= count($produits)>1?'s':''?></span>
      </div>
    </form>
  </div>
</div>

<?php if($vue==='grille'): ?>
<!-- ── GRILLE ── -->
<?php if(empty($produits)): ?>
<div style="text-align:center;padding:60px;color:var(--muted)">
  <div style="font-size:3rem;margin-bottom:15px">📦</div>
  <p>Aucun produit — catalogue vide</p>
  <a href="produits.php?action=add" class="btn-omega btn-omega-primary" style="margin-top:12px">
    <i class="fas fa-plus"></i> Ajouter le premier produit
  </a>
</div>
<?php else: ?>
<div class="prod-grid">
  <?php foreach($produits as $p):
    $marge = $p['prix_vente'] - $p['prix_achat'];
    $margePct = $p['prix_achat']>0?round(($marge/$p['prix_achat'])*100,1):0;
    $mCls = $margePct>=30?'m-ok':($margePct>=15?'m-mid':'m-bad');
    $imgSrc = $p['image'] ? '/charcuterie1/'.UPLOAD_URL.htmlspecialchars($p['image']) : null;
    $sLow = $p['stock_actuel']>0 && $p['stock_actuel']<=$p['stock_min'];
  ?>
  <div class="pcard">
    <div class="pcard-img">
      <?php if($imgSrc): ?>
        <img src="<?=$imgSrc?>" alt="<?= htmlspecialchars($p['nom']) ?>"
          onerror="this.style.display='none';this.nextSibling.style.display='flex'">
        <div class="emoji-placeholder" style="display:none"><?= $emojis[$p['categorie_id']]??'🥩' ?></div>
      <?php else: ?>
        <div class="emoji-placeholder"><?= $emojis[$p['categorie_id']]??'🥩' ?></div>
      <?php endif; ?>
      <!-- Badge -->
      <?php if(!$p['actif']): ?>
        <span class="pcard-badge" style="background:rgba(0,0,0,.75);color:#999">Inactif</span>
      <?php elseif($p['stock_actuel']<=0): ?>
        <span class="pcard-badge" style="background:rgba(192,57,43,.9);color:#fff">Rupture</span>
      <?php elseif($sLow): ?>
        <span class="pcard-badge" style="background:rgba(230,126,34,.9);color:#fff">Faible</span>
      <?php endif; ?>
      <!-- Actions au survol -->
      <div class="pcard-actions">
        <a href="produits.php?action=edit&id=<?=$p['id']?>" class="pcard-act-btn"
          style="background:rgba(212,172,13,.85);color:#000" title="Modifier"><i class="fas fa-edit"></i></a>
        <a href="produits.php?action=delete&id=<?=$p['id']?>" class="pcard-act-btn btn-delete"
          style="background:rgba(192,57,43,.85);color:#fff" title="Supprimer"><i class="fas fa-trash"></i></a>
      </div>
    </div>
    <div class="pcard-body">
      <?php if($p['code_produit']): ?><div class="pcode"><?= htmlspecialchars($p['code_produit']) ?></div><?php endif; ?>
      <div class="pname" title="<?= htmlspecialchars($p['nom']) ?>"><?= htmlspecialchars($p['nom']) ?></div>
      <div class="pcat" style="color:<?= htmlspecialchars($p['cat_color']??'#888') ?>"><?= htmlspecialchars($p['cat_nom']??'—') ?></div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
        <div><span class="pprice"><?= number_format($p['prix_vente'],0,',',' ') ?> F</span> <span class="punit">/ <?= htmlspecialchars($p['unite']) ?></span></div>
        <span class="pmarge <?= $mCls ?>"><?= $margePct ?>% marge</span>
      </div>
      <div class="pstock">
        Stock : <strong style="color:<?= $p['stock_actuel']<=0?'#e74c3c':($sLow?'#e67e22':'#27ae60') ?>">
        <?= number_format($p['stock_actuel'],2) ?></strong> <?= htmlspecialchars($p['unite']) ?>
        <span style="color:var(--muted)"> (min <?= number_format($p['stock_min'],2) ?>)</span>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php else: ?>
<!-- ── LISTE TABLEAU ── -->
<div class="card-omega">
  <div style="overflow-x:auto">
    <table class="table-omega">
      <thead><tr><th>Image</th><th>Code</th><th>Produit</th><th>Categorie</th><th>P.Vente</th><th>P.Achat</th><th>Marge</th><th>Stock</th><th>Statut</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if(empty($produits)): ?>
        <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:40px">
          Aucun produit — <a href="produits.php?action=add" style="color:var(--or)">Ajouter</a>
        </td></tr>
      <?php else: foreach($produits as $p):
        $marge = $p['prix_vente']-$p['prix_achat'];
        $margePct = $p['prix_achat']>0?round(($marge/$p['prix_achat'])*100,1):0;
        $imgSrc = $p['image']?'/charcuterie1/'.UPLOAD_URL.htmlspecialchars($p['image']):null;
        $sLow = $p['stock_actuel']>0 && $p['stock_actuel']<=$p['stock_min'];
      ?>
      <tr>
        <td>
          <?php if($imgSrc): ?>
            <img src="<?=$imgSrc?>" alt="<?= htmlspecialchars($p['nom']) ?>"
              style="width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid var(--border)"
              onerror="this.outerHTML='<div style=\'width:50px;height:50px;border-radius:8px;background:rgba(255,255,255,.05);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.4rem\'><?= $emojis[$p['categorie_id']]??'🥩' ?></div>'">
          <?php else: ?>
            <div style="width:50px;height:50px;border-radius:8px;background:rgba(255,255,255,.04);
              border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.4rem">
              <?= $emojis[$p['categorie_id']]??'🥩' ?>
            </div>
          <?php endif; ?>
        </td>
        <td><?php if($p['code_produit']): ?><span class="pcode"><?= htmlspecialchars($p['code_produit']) ?></span><?php else: ?>—<?php endif; ?></td>
        <td><strong style="font-size:.85rem"><?= htmlspecialchars($p['nom']) ?></strong><br>
          <small style="color:var(--muted)"><?= htmlspecialchars(mb_substr($p['description']??'',0,40)) ?>...</small></td>
        <td><span style="color:<?= htmlspecialchars($p['cat_color']??'#888') ?>;font-size:.78rem"><?= htmlspecialchars($p['cat_nom']??'—') ?></span></td>
        <td><strong style="color:var(--or)"><?= number_format($p['prix_vente'],0,',',' ') ?></strong></td>
        <td><?= number_format($p['prix_achat'],0,',',' ') ?></td>
        <td style="color:<?= $margePct>=30?'#27ae60':($margePct>=15?'#e67e22':'#e74c3c') ?>;font-weight:700"><?= $margePct ?>%</td>
        <td><strong style="color:<?= $p['stock_actuel']<=0?'#e74c3c':($sLow?'#e67e22':'#27ae60') ?>"><?= number_format($p['stock_actuel'],2) ?></strong>
          <small style="color:var(--muted)"> <?= htmlspecialchars($p['unite']) ?></small></td>
        <td><?php if(!$p['actif']): ?><span class="badge-stat b-muted">Inactif</span>
          <?php elseif($p['stock_actuel']<=0): ?><span class="badge-stat b-danger">Rupture</span>
          <?php elseif($sLow): ?><span class="badge-stat b-warning">Faible</span>
          <?php else: ?><span class="badge-stat b-success">OK</span><?php endif; ?></td>
        <td><div style="display:flex;gap:4px">
          <a href="produits.php?action=edit&id=<?=$p['id']?>" class="btn-omega btn-omega-gold" style="padding:5px 10px;font-size:.72rem"><i class="fas fa-edit"></i></a>
          <a href="produits.php?action=delete&id=<?=$p['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:5px 10px;font-size:.72rem"><i class="fas fa-trash"></i></a>
        </div></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; endif; ?>

<?php require_once 'footer.php'; ?>
