<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Recherche Avancée';
$pdo = getPDO();

// ── API JSON temps réel ──
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    $q     = trim($_GET['q'] ?? '');
    $type  = $_GET['type'] ?? 'produits';
    $limit = 20;

    if (strlen($q) < 2) { echo json_encode([]); exit; }

    if ($type === 'produits') {
        $st = $pdo->prepare("
            SELECT p.id, p.code_produit, p.nom, p.prix_vente, p.prix_achat,
                   p.stock_actuel, p.unite, p.image, c.nom as cat, c.couleur,
                   ROUND(((p.prix_vente-p.prix_achat)/NULLIF(p.prix_vente,0))*100,1) as marge_pct
            FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id
            WHERE p.actif=1 AND (
                p.nom          LIKE :q OR
                p.code_produit LIKE :q OR
                p.code_barres  LIKE :q OR
                p.description  LIKE :q OR
                c.nom          LIKE :q
            ) ORDER BY p.nom LIMIT $limit");
        $st->execute([':q' => "%$q%"]);

    } elseif ($type === 'clients') {
        $st = $pdo->prepare("
            SELECT c.id, c.nom, c.prenom, c.telephone, c.email, c.adresse,
                   COUNT(v.id) as nb_achats,
                   COALESCE(SUM(v.total),0) as ca_total
            FROM clients c LEFT JOIN ventes v ON v.client_id=c.id
            WHERE c.nom LIKE :q OR c.prenom LIKE :q2 OR
                  c.telephone LIKE :q3 OR c.email LIKE :q4
            GROUP BY c.id ORDER BY ca_total DESC LIMIT $limit");
        $st->execute([':q'=>"%$q%",':q2'=>"%$q%",':q3'=>"%$q%",':q4'=>"%$q%"]);

    } elseif ($type === 'factures') {
        $st = $pdo->prepare("
            SELECT f.id, f.numero, f.date_facture, f.total_ttc, f.statut,
                   COALESCE(CONCAT(c.prenom,' ',c.nom),'Comptoir') as client_nom,
                   COUNT(fl.id) as nb_lignes
            FROM factures f
            LEFT JOIN clients c ON f.client_id=c.id
            LEFT JOIN facture_lignes fl ON fl.facture_id=f.id
            WHERE f.numero LIKE :q OR c.nom LIKE :q2 OR c.prenom LIKE :q3
                  OR CAST(f.total_ttc AS CHAR) LIKE :q4
            GROUP BY f.id ORDER BY f.date_facture DESC LIMIT $limit");
        $st->execute([':q'=>"%$q%",':q2'=>"%$q%",':q3'=>"%$q%",':q4'=>"%$q%"]);
    }

    echo json_encode($st->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── RECHERCHE AVANCÉE COMPLÈTE ──
$module  = $_GET['module'] ?? 'produits';
$results = [];
$query   = trim($_GET['q'] ?? '');

// Filtres produits
$catFilter    = (int)($_GET['cat'] ?? 0);
$prixMin      = (float)($_GET['prix_min'] ?? 0);
$prixMax      = (float)($_GET['prix_max'] ?? 0);
$stockFilter  = $_GET['stock'] ?? '';

// Filtres factures
$statut       = $_GET['statut'] ?? '';
$dateFrom     = $_GET['date_from'] ?? '';
$dateTo       = $_GET['date_to'] ?? '';
$montantMin   = (float)($_GET['mont_min'] ?? 0);
$montantMax   = (float)($_GET['mont_max'] ?? 0);

// Filtres clients
$niveauFid    = $_GET['niveau'] ?? '';

$executed = isset($_GET['search']);

if ($executed) {
    if ($module === 'produits') {
        $sql = "SELECT p.*,c.nom as cat_nom,c.couleur,
            ROUND(((p.prix_vente-p.prix_achat)/NULLIF(p.prix_vente,0))*100,1) as marge_pct
            FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id WHERE 1";
        $params = [];
        if ($query) {
            $sql .= " AND (p.nom LIKE :q OR p.code_produit LIKE :q2 OR p.code_barres LIKE :q3 OR p.description LIKE :q4)";
            $params += [':q'=>"%$query%",':q2'=>"%$query%",':q3'=>"%$query%",':q4'=>"%$query%"];
        }
        if ($catFilter)    { $sql .= " AND p.categorie_id=:cat"; $params[':cat']=$catFilter; }
        if ($prixMin>0)    { $sql .= " AND p.prix_vente>=:pmin"; $params[':pmin']=$prixMin; }
        if ($prixMax>0)    { $sql .= " AND p.prix_vente<=:pmax"; $params[':pmax']=$prixMax; }
        if ($stockFilter==='ok')    $sql .= " AND p.stock_actuel>p.stock_min";
        if ($stockFilter==='alert') $sql .= " AND p.stock_actuel<=p.stock_min AND p.stock_actuel>0";
        if ($stockFilter==='zero')  $sql .= " AND p.stock_actuel<=0";
        $sql .= " ORDER BY p.nom";
        $st = $pdo->prepare($sql); $st->execute($params);
        $results = $st->fetchAll();

    } elseif ($module === 'factures') {
        $sql = "SELECT f.*,COALESCE(CONCAT(c.prenom,' ',c.nom),'Comptoir') as client_nom,
            COUNT(fl.id) as nb_lignes
            FROM factures f LEFT JOIN clients c ON f.client_id=c.id
            LEFT JOIN facture_lignes fl ON fl.facture_id=f.id WHERE 1";
        $params = [];
        if ($query)      { $sql .= " AND (f.numero LIKE :q OR c.nom LIKE :q2 OR c.prenom LIKE :q3)"; $params+=[ ':q'=>"%$query%",':q2'=>"%$query%",':q3'=>"%$query%"]; }
        if ($statut)     { $sql .= " AND f.statut=:s"; $params[':s']=$statut; }
        if ($dateFrom)   { $sql .= " AND f.date_facture>=:df"; $params[':df']=$dateFrom; }
        if ($dateTo)     { $sql .= " AND f.date_facture<=:dt"; $params[':dt']=$dateTo; }
        if ($montantMin) { $sql .= " AND f.total_ttc>=:mmin"; $params[':mmin']=$montantMin; }
        if ($montantMax) { $sql .= " AND f.total_ttc<=:mmax"; $params[':mmax']=$montantMax; }
        $sql .= " GROUP BY f.id ORDER BY f.date_facture DESC";
        $st = $pdo->prepare($sql); $st->execute($params);
        $results = $st->fetchAll();

    } elseif ($module === 'clients') {
        $sql = "SELECT c.*,
            COUNT(DISTINCT v.id) as nb_achats,
            COALESCE(SUM(v.total),0) as ca_total,
            COALESCE(fp.niveau,'Bronze') as niveau_fidelite,
            COALESCE(fp.points,0) as points
            FROM clients c
            LEFT JOIN ventes v ON v.client_id=c.id
            LEFT JOIN fidelite_points fp ON fp.client_id=c.id
            WHERE 1";
        $params = [];
        if ($query) { $sql .= " AND (c.nom LIKE :q OR c.prenom LIKE :q2 OR c.telephone LIKE :q3 OR c.email LIKE :q4)"; $params+=[ ':q'=>"%$query%",':q2'=>"%$query%",':q3'=>"%$query%",':q4'=>"%$query%"]; }
        if ($niveauFid) { $sql .= " AND fp.niveau=:niv"; $params[':niv']=$niveauFid; }
        $sql .= " GROUP BY c.id ORDER BY ca_total DESC";
        $st = $pdo->prepare($sql); $st->execute($params);
        $results = $st->fetchAll();
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
require_once 'header.php';
?>
<style>
.search-hero{background:linear-gradient(135deg,rgba(192,57,43,.15),rgba(212,172,13,.08));
  border:1px solid rgba(212,172,13,.2);border-radius:16px;padding:25px;margin-bottom:20px}
.search-main{position:relative}
.search-main input{width:100%;padding:16px 55px 16px 20px;font-size:1.1rem;
  background:rgba(255,255,255,.07);border:2px solid var(--border);border-radius:12px;
  color:var(--text);font-family:'Raleway',sans-serif;outline:none;transition:.3s}
.search-main input:focus{border-color:var(--or);background:rgba(212,172,13,.05);
  box-shadow:0 0 0 4px rgba(212,172,13,.1)}
.search-main .search-icon{position:absolute;right:18px;top:50%;transform:translateY(-50%);
  color:var(--or);font-size:1.2rem;pointer-events:none}
.module-tabs{display:flex;gap:8px;margin-bottom:20px}
.mod-tab{padding:10px 22px;border-radius:30px;border:1px solid var(--border);
  background:rgba(255,255,255,.04);color:#888;cursor:pointer;text-decoration:none;
  font-weight:700;font-size:.82rem;transition:.2s;display:flex;align-items:center;gap:8px}
.mod-tab.active,.mod-tab:hover{background:var(--rouge);color:#fff;border-color:var(--rouge)}
.filters-panel{background:rgba(255,255,255,.02);border:1px solid var(--border);
  border-radius:12px;padding:15px 20px;margin-bottom:15px}
.filters-panel h5{color:var(--or);font-size:.78rem;text-transform:uppercase;
  letter-spacing:2px;margin-bottom:12px;font-weight:700}
/* Résultats */
.result-card{background:rgba(255,255,255,.03);border:1px solid var(--border);
  border-radius:12px;padding:16px 20px;margin-bottom:10px;transition:.3s;
  display:flex;align-items:center;gap:15px}
.result-card:hover{border-color:var(--or);background:rgba(212,172,13,.04)}
.result-badge{font-size:.65rem;padding:3px 8px;border-radius:20px;font-weight:700;white-space:nowrap}
.code-chip{background:rgba(212,172,13,.15);color:var(--or);border:1px solid rgba(212,172,13,.3);
  font-size:.72rem;padding:3px 10px;border-radius:20px;font-family:monospace;font-weight:700}
.barcode{font-family:monospace;font-size:.68rem;color:#555;letter-spacing:1px}
/* Autocomplete */
.autocomplete-box{position:absolute;top:100%;left:0;right:0;background:#1a1a1a;
  border:1px solid var(--or);border-radius:12px;z-index:999;max-height:350px;overflow-y:auto;
  box-shadow:0 10px 30px rgba(0,0,0,.5);margin-top:5px}
.ac-item{padding:12px 16px;cursor:pointer;transition:.2s;border-bottom:1px solid rgba(255,255,255,.04);
  display:flex;align-items:center;gap:12px}
.ac-item:hover{background:rgba(212,172,13,.08)}
.ac-item:last-child{border-bottom:none}
.ac-code{font-size:.7rem;color:var(--or);font-family:monospace;background:rgba(212,172,13,.1);
  padding:2px 7px;border-radius:10px}
.ac-price{color:var(--or);font-weight:700;font-size:.85rem;margin-left:auto}
.ac-stock{font-size:.7rem;color:var(--muted)}
.highlight{color:var(--or);font-weight:700}
</style>

<div class="page-header">
  <h1><i class="fas fa-search" style="color:var(--or)"></i> <span>Recherche Avancée</span></h1>
  <p>Recherche multi-critères — Produits, Factures, Clients</p>
</div>

<!-- BARRE DE RECHERCHE PRINCIPALE -->
<div class="search-hero">
  <form method="GET" id="searchForm">
    <input type="hidden" name="search" value="1">
    <input type="hidden" name="module" id="moduleHidden" value="<?= htmlspecialchars($module) ?>">
    <div class="search-main" style="position:relative">
      <input type="text" name="q" id="mainSearch" placeholder="🔍 Rechercher par nom, code, référence, client..."
        value="<?= htmlspecialchars($query) ?>" autocomplete="off"
        oninput="autocomplete(this.value)">
      <i class="fas fa-search search-icon"></i>
      <div class="autocomplete-box" id="acBox" style="display:none"></div>
    </div>
  </form>
</div>

<!-- ONGLETS MODULE -->
<div class="module-tabs">
  <a href="?module=produits&search=1&q=<?= urlencode($query) ?>" class="mod-tab <?= $module==='produits'?'active':'' ?>">
    <i class="fas fa-box-open"></i> Produits (<?= $module==='produits'?count($results):$pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn() ?>)
  </a>
  <a href="?module=factures&search=1&q=<?= urlencode($query) ?>" class="mod-tab <?= $module==='factures'?'active':'' ?>">
    <i class="fas fa-file-invoice"></i> Factures (<?= $module==='factures'?count($results):$pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn() ?>)
  </a>
  <a href="?module=clients&search=1&q=<?= urlencode($query) ?>" class="mod-tab <?= $module==='clients'?'active':'' ?>">
    <i class="fas fa-users"></i> Clients (<?= $module==='clients'?count($results):$pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn() ?>)
  </a>
</div>

<!-- FILTRES AVANCÉS -->
<div class="filters-panel">
  <h5><i class="fas fa-filter"></i> Filtres Avancés — <?= ucfirst($module) ?></h5>
  <form method="GET" class="form-omega" id="filterForm">
    <input type="hidden" name="module" value="<?= htmlspecialchars($module) ?>">
    <input type="hidden" name="search" value="1">
    <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
    <div class="row g-2">

    <?php if ($module === 'produits'): ?>
      <div class="col-md-3">
        <label class="form-label">Catégorie</label>
        <select name="cat" class="form-select">
          <option value="">Toutes</option>
          <?php foreach($categories as $c): ?>
          <option value="<?=$c['id']?>" <?=$catFilter==$c['id']?'selected':''?>><?= $c['icone'].' '.htmlspecialchars($c['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Prix min (FCFA)</label>
        <input type="number" name="prix_min" class="form-control" value="<?=$prixMin?:''?>" placeholder="0">
      </div>
      <div class="col-md-2">
        <label class="form-label">Prix max (FCFA)</label>
        <input type="number" name="prix_max" class="form-control" value="<?=$prixMax?:''?>" placeholder="∞">
      </div>
      <div class="col-md-2">
        <label class="form-label">Stock</label>
        <select name="stock" class="form-select">
          <option value="">Tous</option>
          <option value="ok"    <?=$stockFilter==='ok'?'selected':''?>>✅ Normal</option>
          <option value="alert" <?=$stockFilter==='alert'?'selected':''?>>⚠️ Faible</option>
          <option value="zero"  <?=$stockFilter==='zero'?'selected':''?>>❌ Épuisé</option>
        </select>
      </div>

    <?php elseif ($module === 'factures'): ?>
      <div class="col-md-2">
        <label class="form-label">Statut</label>
        <select name="statut" class="form-select">
          <option value="">Tous</option>
          <option value="emise"    <?=$statut==='emise'?'selected':''?>>📤 Émise</option>
          <option value="payee"    <?=$statut==='payee'?'selected':''?>>✅ Payée</option>
          <option value="annulee"  <?=$statut==='annulee'?'selected':''?>>❌ Annulée</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Du</label>
        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Au</label>
        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Montant min (FCFA)</label>
        <input type="number" name="mont_min" class="form-control" value="<?=$montantMin?:''?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Montant max (FCFA)</label>
        <input type="number" name="mont_max" class="form-control" value="<?=$montantMax?:''?>">
      </div>

    <?php elseif ($module === 'clients'): ?>
      <div class="col-md-3">
        <label class="form-label">Niveau Fidélité</label>
        <select name="niveau" class="form-select">
          <option value="">Tous niveaux</option>
          <option value="Bronze"  <?=$niveauFid==='Bronze'?'selected':''?>>🥉 Bronze</option>
          <option value="Argent"  <?=$niveauFid==='Argent'?'selected':''?>>🥈 Argent</option>
          <option value="Or"      <?=$niveauFid==='Or'?'selected':''?>>🥇 Or</option>
          <option value="Platine" <?=$niveauFid==='Platine'?'selected':''?>>💎 Platine</option>
        </select>
      </div>
    <?php endif; ?>

      <div class="col-md-2" style="align-self:flex-end">
        <button type="submit" class="btn-omega btn-omega-gold w-100"><i class="fas fa-search"></i> Rechercher</button>
      </div>
      <div class="col-md-1" style="align-self:flex-end">
        <a href="?module=<?=$module?>" class="btn-omega btn-omega-outline w-100">Reset</a>
      </div>
    </div>
  </form>
</div>

<!-- RÉSULTATS -->
<?php if ($executed): ?>
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-list"></i> <?= count($results) ?> résultat<?= count($results)>1?'s':'' ?> trouvé<?= count($results)>1?'s':'' ?></h4>
    <?php if(!empty($query)): ?>
    <span style="color:var(--muted);font-size:.82rem">pour "<strong style="color:var(--or)"><?= htmlspecialchars($query) ?></strong>"</span>
    <?php endif; ?>
  </div>
  <div class="card-body" style="padding:15px">

  <?php if(empty($results)): ?>
    <div style="text-align:center;padding:50px;color:var(--muted)">
      <div style="font-size:3rem;margin-bottom:15px">🔍</div>
      <p>Aucun résultat — Essayez d'autres critères</p>
    </div>

  <?php elseif ($module === 'produits'): ?>
    <?php foreach($results as $p):
      $stockCss = $p['stock_actuel']<=0?'b-danger':($p['stock_actuel']<=$p['stock_min']?'b-warning':'b-success');
      $margeCss = $p['marge_pct']>=30?'#27ae60':($p['marge_pct']>=15?'#e67e22':'#e74c3c');
    ?>
    <div class="result-card">
      <div style="width:50px;height:50px;border-radius:10px;background:rgba(255,255,255,.05);
        display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0">
        <?php if($p['image']): ?><img src="../<?= UPLOAD_URL.htmlspecialchars($p['image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:10px" onerror="this.outerHTML='🥩'"><?php else: ?>🥩<?php endif; ?>
      </div>
      <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:5px">
          <strong style="color:var(--text)"><?= htmlspecialchars($p['nom']) ?></strong>
          <?php if($p['code_produit']): ?><span class="code-chip"><?= htmlspecialchars($p['code_produit']) ?></span><?php endif; ?>
          <span style="color:<?= htmlspecialchars($p['couleur']??'#888') ?>;font-size:.75rem"><?= htmlspecialchars($p['cat_nom']??'') ?></span>
        </div>
        <?php if($p['code_barres']): ?>
        <div class="barcode">📊 <?= htmlspecialchars($p['code_barres']) ?></div>
        <?php endif; ?>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="color:var(--or);font-weight:900;font-size:1rem"><?= number_format($p['prix_vente'],0,',',' ') ?> FCFA</div>
        <div style="font-size:.72rem;color:var(--muted)">Achat : <?= number_format($p['prix_achat'],0,',',' ') ?></div>
        <div style="font-size:.72rem;color:<?=$margeCss?>">Marge : <?= $p['marge_pct'] ?>%</div>
      </div>
      <div style="flex-shrink:0">
        <span class="result-badge <?=$stockCss?>">
          <?= $p['stock_actuel']<=0?'Épuisé':($p['stock_actuel']<=$p['stock_min']?'Faible':'En stock') ?>
        </span>
        <div style="font-size:.75rem;color:var(--muted);margin-top:4px;text-align:center"><?= number_format($p['stock_actuel'],2) ?> <?= htmlspecialchars($p['unite']) ?></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:5px;flex-shrink:0">
        <a href="produits.php?action=edit&id=<?=$p['id']?>" class="btn-omega btn-omega-gold" style="padding:5px 12px;font-size:.72rem"><i class="fas fa-edit"></i></a>
        <a href="ventes.php" class="btn-omega btn-omega-success" style="padding:5px 12px;font-size:.72rem"><i class="fas fa-plus"></i></a>
      </div>
    </div>
    <?php endforeach; ?>

  <?php elseif ($module === 'factures'): ?>
    <?php
    $sCls=['emise'=>'b-warning','payee'=>'b-success','annulee'=>'b-danger','brouillon'=>'b-muted'];
    $sLbl=['emise'=>'📤 Émise','payee'=>'✅ Payée','annulee'=>'❌ Annulée','brouillon'=>'✏️ Brouillon'];
    foreach($results as $f):
    ?>
    <div class="result-card">
      <div style="width:45px;height:45px;border-radius:10px;background:rgba(41,128,185,.15);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">📄</div>
      <div style="flex:1">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
          <strong><?= htmlspecialchars($f['numero']) ?></strong>
          <span class="result-badge <?=$sCls[$f['statut']]?>"><?=$sLbl[$f['statut']]?></span>
          <span style="color:var(--muted);font-size:.78rem"><?= date('d/m/Y',strtotime($f['date_facture'])) ?></span>
        </div>
        <div style="color:var(--muted);font-size:.82rem">
          👤 <?= htmlspecialchars($f['client_nom']) ?> &nbsp;|&nbsp;
          📦 <?= $f['nb_lignes'] ?> ligne<?= $f['nb_lignes']>1?'s':'' ?>
        </div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="color:var(--or);font-weight:900;font-size:1.1rem"><?= number_format($f['total_ttc'],0,',',' ') ?> FCFA</div>
        <div style="font-size:.72rem;color:var(--muted)">TTC</div>
      </div>
      <div style="display:flex;gap:5px;flex-shrink:0">
        <a href="factures.php?action=view&id=<?=$f['id']?>" class="btn-omega btn-omega-outline" style="padding:5px 12px;font-size:.72rem"><i class="fas fa-eye"></i></a>
        <a href="facture_print.php?id=<?=$f['id']?>" target="_blank" class="btn-omega btn-omega-gold" style="padding:5px 12px;font-size:.72rem"><i class="fas fa-print"></i></a>
      </div>
    </div>
    <?php endforeach; ?>

  <?php elseif ($module === 'clients'): ?>
    <?php
    $niveauC=['Bronze'=>['#cd7f32','🥉'],'Argent'=>['#aaa','🥈'],'Or'=>['#d4ac0d','🥇'],'Platine'=>['#7ec8e3','💎']];
    foreach($results as $c):
      [$col,$medal] = $niveauC[$c['niveau_fidelite']] ?? ['#888','⭐'];
    ?>
    <div class="result-card">
      <div style="width:45px;height:45px;border-radius:50%;background:linear-gradient(135deg,var(--rouge),var(--or));display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.1rem;flex-shrink:0;color:#fff">
        <?= strtoupper(substr($c['nom'],0,1)) ?>
      </div>
      <div style="flex:1">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
          <strong><?= htmlspecialchars(trim($c['prenom'].' '.$c['nom'])) ?></strong>
          <span style="color:<?=$col?>;font-size:.8rem;font-weight:700"><?=$medal?> <?=$c['niveau_fidelite']?></span>
          <span style="color:var(--or);font-size:.72rem"><?= number_format($c['points'],0,',',' ') ?> pts</span>
        </div>
        <div style="color:var(--muted);font-size:.8rem">
          <?php if($c['telephone']): ?>📞 <?= htmlspecialchars($c['telephone']) ?> &nbsp;|&nbsp;<?php endif; ?>
          <?php if($c['email']): ?>✉️ <?= htmlspecialchars($c['email']) ?><?php endif; ?>
        </div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="color:var(--or);font-weight:900"><?= number_format($c['ca_total'],0,',',' ') ?> FCFA</div>
        <div style="font-size:.72rem;color:var(--muted)"><?= $c['nb_achats'] ?> achat<?= $c['nb_achats']>1?'s':'' ?></div>
      </div>
      <div style="display:flex;gap:5px;flex-shrink:0">
        <a href="clients.php?action=view&id=<?=$c['id']?>" class="btn-omega btn-omega-outline" style="padding:5px 12px;font-size:.72rem"><i class="fas fa-eye"></i></a>
        <a href="factures.php?action=new" class="btn-omega btn-omega-primary" style="padding:5px 12px;font-size:.72rem" title="Nouvelle facture"><i class="fas fa-file-invoice"></i></a>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
</div>
<?php else: ?>
<!-- SUGGESTIONS RAPIDES -->
<div class="row g-3">
  <div class="col-md-4">
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-fire" style="color:#e74c3c"></i> Produits les plus vendus</h4></div>
      <div style="padding:0">
        <?php
        $tops = $pdo->query("SELECT p.nom,p.code_produit,SUM(v.total) as ca
            FROM ventes v JOIN produits p ON v.produit_id=p.id
            GROUP BY p.id ORDER BY ca DESC LIMIT 5")->fetchAll();
        foreach($tops as $t):
        ?>
        <div style="padding:10px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
          <span class="code-chip"><?= htmlspecialchars($t['code_produit']??'—') ?></span>
          <span style="flex:1;font-size:.83rem"><?= htmlspecialchars($t['nom']) ?></span>
          <strong style="color:var(--or);font-size:.82rem"><?= number_format($t['ca']/1000,0) ?>K</strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-clock" style="color:var(--or)"></i> Dernières factures</h4></div>
      <div style="padding:0">
        <?php
        $lf = $pdo->query("SELECT f.numero,f.total_ttc,f.statut,COALESCE(CONCAT(c.prenom,' ',c.nom),'Comptoir') as cn
            FROM factures f LEFT JOIN clients c ON f.client_id=c.id ORDER BY f.id DESC LIMIT 5")->fetchAll();
        foreach($lf as $f):
          $sc=['emise'=>'#e67e22','payee'=>'#27ae60','annulee'=>'#e74c3c','brouillon'=>'#888'][$f['statut']];
        ?>
        <div style="padding:10px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px">
          <span style="font-size:.72rem;color:var(--or);font-family:monospace"><?= htmlspecialchars($f['numero']) ?></span>
          <span style="flex:1;font-size:.8rem;color:var(--muted)"><?= htmlspecialchars(mb_substr($f['cn'],0,20)) ?></span>
          <strong style="color:<?=$sc?>;font-size:.78rem"><?= number_format($f['total_ttc']/1000,0) ?>K F</strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-star" style="color:var(--or)"></i> Meilleurs clients</h4></div>
      <div style="padding:0">
        <?php
        $mc = $pdo->query("SELECT c.nom,c.prenom,SUM(v.total) as ca
            FROM clients c JOIN ventes v ON v.client_id=c.id
            GROUP BY c.id ORDER BY ca DESC LIMIT 5")->fetchAll();
        foreach($mc as $c):
        ?>
        <div style="padding:10px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px">
          <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--rouge),var(--or));display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:900;color:#fff;flex-shrink:0"><?= strtoupper(substr($c['nom'],0,1)) ?></div>
          <span style="flex:1;font-size:.83rem"><?= htmlspecialchars(trim($c['prenom'].' '.$c['nom'])) ?></span>
          <strong style="color:var(--or);font-size:.82rem"><?= number_format($c['ca']/1000,0) ?>K</strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
let acTimer;
async function autocomplete(q) {
  clearTimeout(acTimer);
  const box = document.getElementById('acBox');
  if (q.length < 2) { box.style.display='none'; return; }
  acTimer = setTimeout(async () => {
    const mod = document.getElementById('moduleHidden').value;
    const res = await fetch(`recherche.php?api=1&type=${mod}&q=${encodeURIComponent(q)}`);
    const data = await res.json();
    if (!data.length) { box.style.display='none'; return; }
    const hl = s => String(s).replace(new RegExp(q,'gi'), m=>`<span class="highlight">${m}</span>`);
    box.innerHTML = data.slice(0,8).map(item => {
      if (mod === 'produits') return `
        <div class="ac-item" onclick="selectItem('${item.nom.replace(/'/g,"\\'")}')">
          <span style="font-size:1.2rem">🥩</span>
          <div style="flex:1">
            <div style="font-size:.85rem;color:var(--text)">${hl(item.nom)}</div>
            <div class="ac-stock">Stock: ${parseFloat(item.stock_actuel).toFixed(2)} ${item.unite}</div>
          </div>
          <span class="ac-code">${item.code_produit||''}</span>
          <span class="ac-price">${parseInt(item.prix_vente).toLocaleString('fr')} F</span>
        </div>`;
      if (mod === 'clients') return `
        <div class="ac-item" onclick="selectItem('${(item.prenom+' '+item.nom).trim().replace(/'/g,"\\'")}')">
          <span style="font-size:1.2rem">👤</span>
          <div style="flex:1">
            <div style="font-size:.85rem;color:var(--text)">${hl(item.prenom+' '+item.nom)}</div>
            <div class="ac-stock">${item.telephone||''}</div>
          </div>
          <span class="ac-price">${parseInt(item.ca_total).toLocaleString('fr')} F</span>
        </div>`;
      if (mod === 'factures') return `
        <div class="ac-item" onclick="selectItem('${item.numero}')">
          <span style="font-size:1.2rem">📄</span>
          <div style="flex:1">
            <div style="font-size:.85rem;color:var(--text)">${hl(item.numero)}</div>
            <div class="ac-stock">${item.client_nom}</div>
          </div>
          <span class="ac-price">${parseInt(item.total_ttc).toLocaleString('fr')} F</span>
        </div>`;
      return '';
    }).join('');
    box.style.display = 'block';
  }, 200);
}

function selectItem(val) {
  document.getElementById('mainSearch').value = val;
  document.getElementById('acBox').style.display = 'none';
  document.getElementById('searchForm').submit();
}

document.addEventListener('click', e => {
  if (!e.target.closest('#mainSearch') && !e.target.closest('#acBox'))
    document.getElementById('acBox').style.display = 'none';
});

// Changer module sans perdre la recherche
document.querySelectorAll('.mod-tab').forEach(tab => {
  tab.addEventListener('click', e => {
    document.getElementById('moduleHidden').value =
      new URL(tab.href).searchParams.get('module');
  });
});
</script>

<?php require_once 'footer.php'; ?>
