#!/bin/bash
# ================================================================
# CRÉATION FICHIERS PHP MANQUANTS
# Serveur PHP tourne sur port 8000 depuis pharmacie/
# ================================================================
PHA="/root/shared/htdocs/apachewsl2026/pharmacie"
RMD="/root/shared/htdocs/apachewsl2026/revendeur_medical"

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
log() { echo -e "${GREEN}[✓]${NC} $1"; }

echo -e "${YELLOW}Création fichiers PHP manquants...${NC}"
echo "Répertoire cible : $PHA"
echo ""

# Vérifier les 9 fichiers existants
echo "=== Fichiers PHP existants ==="
find "$PHA" -name "*.php" | sort
echo ""

# ────────────────────────────────────────────────────────────────
# login.php — PRIORITÉ 1 (404 sur ce fichier)
# ────────────────────────────────────────────────────────────────
cat > "$PHA/login.php" << 'PHP'
<?php
require_once __DIR__.'/core/Auth.php';
require_once __DIR__.'/config/config.php';

// Déjà connecté → rediriger
if(session_status()===PHP_SESSION_NONE) session_start();
if(!empty($_SESSION['user'])){
    header('Location: /index.php'); exit;
}

$err = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(Auth::login(trim($_POST['login']??''), $_POST['password']??'')){
        header('Location: /index.php'); exit;
    }
    $err = 'Identifiants incorrects. Vérifiez votre login et mot de passe.';
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PharmaSen — Connexion</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Roboto,sans-serif;}
body{
  background:linear-gradient(135deg,#1a7f5a 0%,#0d4f38 100%);
  min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;
}
.wrap{background:#fff;border-radius:18px;width:100%;max-width:400px;
  box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;}
.top{background:linear-gradient(135deg,#1a7f5a,#0d4f38);
  padding:32px;text-align:center;color:#fff;}
.top .ico{font-size:3.5rem;margin-bottom:10px;}
.top h1{font-size:1.5rem;font-weight:700;}
.top p{font-size:.85rem;opacity:.8;margin-top:4px;}
.body{padding:28px 32px 32px;}
.err{background:#fef0f0;border:1px solid #fcc;border-radius:8px;
  color:#c0392b;padding:11px 14px;font-size:.85rem;margin-bottom:16px;
  display:flex;align-items:center;gap:8px;}
.grp{margin-bottom:16px;}
.grp label{display:block;font-size:.82rem;font-weight:600;color:#555;margin-bottom:6px;}
.grp input{width:100%;padding:11px 14px;border:1.5px solid #ddd;border-radius:9px;
  font-size:.95rem;outline:none;transition:.2s;background:#fafafa;}
.grp input:focus{border-color:#1a7f5a;background:#fff;box-shadow:0 0 0 3px rgba(26,127,90,.12);}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a7f5a,#0d4f38);
  color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;
  cursor:pointer;transition:.2s;margin-top:4px;}
.btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(26,127,90,.4);}
.btn:active{transform:none;}
.info{text-align:center;margin-top:18px;font-size:.78rem;color:#aaa;}
.ver{text-align:center;padding:12px;background:#f9f9f9;font-size:.72rem;color:#bbb;
  border-top:1px solid #eee;}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div class="ico">💊</div>
    <h1>PharmaSen</h1>
    <p>Gestion Pharmaceutique — Sénégal</p>
  </div>
  <div class="body">
    <?php if($err): ?>
    <div class="err">⚠️ <?=htmlspecialchars($err)?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
      <div class="grp">
        <label>Identifiant</label>
        <input type="text" name="login" placeholder="Votre identifiant" required autofocus
               value="<?=htmlspecialchars($_POST['login']??'')?>">
      </div>
      <div class="grp">
        <label>Mot de passe</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button class="btn" type="submit">🔐 Se connecter</button>
    </form>
    <div class="info">
      Compte par défaut : <strong>admin</strong> / <strong>password</strong>
    </div>
  </div>
  <div class="ver">PharmaSen v<?=APP_VERSION?> — <?=APP_TIMEZONE?></div>
</div>
</body>
</html>
PHP
log "login.php ✓"

# ────────────────────────────────────────────────────────────────
# logout.php
# ────────────────────────────────────────────────────────────────
cat > "$PHA/logout.php" << 'PHP'
<?php
require_once __DIR__.'/core/Auth.php';
Auth::logout();
PHP
log "logout.php ✓"

# ────────────────────────────────────────────────────────────────
# index.php — Dashboard principal
# ────────────────────────────────────────────────────────────────
cat > "$PHA/index.php" << 'PHP'
<?php
require_once __DIR__.'/core/Auth.php';
require_once __DIR__.'/core/Helper.php';
require_once __DIR__.'/core/Database.php';
require_once __DIR__.'/config/config.php';
Auth::check();
$user = Auth::getUser();

// KPI
$vj  = Database::queryOne("SELECT COUNT(*) n, IFNULL(SUM(net_a_payer),0) ca FROM ventes WHERE DATE(date_vente)=CURDATE() AND statut='validee'");
$cm  = Database::queryOne("SELECT IFNULL(SUM(net_a_payer),0) ca FROM ventes WHERE MONTH(date_vente)=MONTH(CURDATE()) AND YEAR(date_vente)=YEAR(CURDATE()) AND statut='validee'");
$sc  = Database::queryOne("SELECT COUNT(*) n FROM medicaments WHERE actif=1 AND stock_actuel<=stock_min");
$per = Database::queryOne("SELECT COUNT(*) n FROM lots_medicaments WHERE quantite_restante>0 AND date_peremption BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY)");
$ord = Database::queryOne("SELECT COUNT(*) n FROM ordonnances WHERE statut='en_attente'");

$ventes   = Database::query("SELECT v.reference,v.date_vente,v.net_a_payer,v.mode_paiement,
    CONCAT(IFNULL(c.prenom,''),' ',IFNULL(c.nom,'')) client
    FROM ventes v LEFT JOIN clients c ON c.id=v.client_id
    WHERE v.statut='validee' ORDER BY v.date_vente DESC LIMIT 10");

$alertes  = Database::query("SELECT denomination,nom_commercial,stock_actuel,stock_min
    FROM medicaments WHERE actif=1 AND stock_actuel<=stock_min ORDER BY stock_actuel LIMIT 10");

$perempts = Database::query("SELECT * FROM v_peremptions_proches WHERE jours_restants<=30 LIMIT 6");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tableau de bord — PharmaSen</title>
<link rel="stylesheet" href="assets/css/main.css">
<style>
.kpi{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;margin-bottom:22px;}
.k{background:#fff;border-radius:12px;padding:18px 14px;box-shadow:0 2px 10px rgba(0,0,0,.08);
   border-left:5px solid var(--primary);display:flex;align-items:center;gap:12px;transition:.2s;}
.k:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.12);}
.ki{font-size:2rem;}.kv{font-size:1.5rem;font-weight:800;color:var(--primary);}
.kl{font-size:.75rem;color:#888;margin-top:2px;}
.k.w{border-color:#f0a500;}.k.w .kv{color:#b37800;}
.k.d{border-color:#dc3545;}.k.d .kv{color:#dc3545;}
.k.s{border-color:#28a745;}.k.s .kv{color:#28a745;}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
@media(max-width:800px){.g2{grid-template-columns:1fr;}}
</style>
</head>
<body>
<?php include 'templates/partials/sidebar.php'; ?>
<main class="main-content">
  <div class="topbar">
    <h1 class="page-title">📊 Tableau de bord</h1>
    <div class="topbar-right">
      <span id="clk" style="font-size:.82rem;color:#888;"></span>
      <a href="modules/caisse/pos.php" class="btn btn-primary btn-sm">🛒 Ouvrir Caisse</a>
    </div>
  </div>

  <div class="content-area">
    <!-- KPI CARDS -->
    <div class="kpi">
      <div class="k s">
        <div class="ki">💰</div>
        <div><div class="kv"><?=Helper::fcfa($vj['ca'])?></div>
             <div class="kl">CA aujourd'hui (<?=$vj['n']?> ventes)</div></div>
      </div>
      <div class="k s">
        <div class="ki">📅</div>
        <div><div class="kv"><?=Helper::fcfa($cm['ca'])?></div>
             <div class="kl">CA du mois en cours</div></div>
      </div>
      <div class="k <?=$sc['n']>0?'d':''?>">
        <div class="ki">📦</div>
        <div><div class="kv"><?=$sc['n']?></div>
             <div class="kl">Stocks critique(s)</div></div>
      </div>
      <div class="k <?=$per['n']>0?'w':''?>">
        <div class="ki">⏰</div>
        <div><div class="kv"><?=$per['n']?></div>
             <div class="kl">Péremptions &lt; 30 jours</div></div>
      </div>
      <div class="k <?=$ord['n']>0?'w':''?>">
        <div class="ki">📋</div>
        <div><div class="kv"><?=$ord['n']?></div>
             <div class="kl">Ordonnances en attente</div></div>
      </div>
    </div>

    <div class="g2">
      <!-- Ventes récentes -->
      <div class="card">
        <div class="card-title">🧾 Ventes récentes</div>
        <div class="table-wrap"><table>
          <thead><tr><th>Réf.</th><th>Client</th><th>Montant</th><th>Mode</th><th>Heure</th></tr></thead>
          <tbody>
          <?php if(!$ventes): ?>
            <tr><td colspan="5" style="text-align:center;color:#ccc;padding:24px;">Aucune vente aujourd'hui</td></tr>
          <?php else: foreach($ventes as $v): ?>
            <tr>
              <td style="font-size:.78rem;font-weight:600;color:var(--primary)"><?=htmlspecialchars($v['reference'])?></td>
              <td style="font-size:.8rem;"><?=htmlspecialchars(trim($v['client']))?:'-'?></td>
              <td style="font-weight:700;color:var(--primary)"><?=Helper::fcfa($v['net_a_payer'])?></td>
              <td><span class="badge badge-ok" style="font-size:.7rem;"><?=htmlspecialchars($v['mode_paiement'])?></span></td>
              <td style="font-size:.78rem;color:#888;"><?=date('H:i',strtotime($v['date_vente']))?></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div>

      <!-- Alertes stock -->
      <div class="card">
        <div class="card-title">⚠️ Alertes stock</div>
        <?php if(!$alertes): ?>
          <div style="text-align:center;color:#28a745;padding:24px;font-size:1.1rem;">✅ Tous les stocks sont suffisants</div>
        <?php else: ?>
        <div class="table-wrap"><table>
          <thead><tr><th>Médicament</th><th>Stock</th><th>Seuil</th><th>Statut</th></tr></thead>
          <tbody>
          <?php foreach($alertes as $a): ?>
            <tr>
              <td><div style="font-weight:600;font-size:.82rem;"><?=htmlspecialchars($a['denomination'])?></div></td>
              <td style="font-weight:800;color:<?=$a['stock_actuel']<=0?'#dc3545':'#856404'?>;font-size:1rem;"><?=$a['stock_actuel']?></td>
              <td style="color:#888;"><?=$a['stock_min']?></td>
              <td>
                <?php if($a['stock_actuel']<=0): ?>
                  <span class="badge badge-danger">🔴 Rupture</span>
                <?php else: ?>
                  <span class="badge badge-warn">🟠 Critique</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
        <?php endif; ?>
        <div style="text-align:right;margin-top:10px;">
          <a href="modules/stock/" class="btn btn-sm" style="background:#fff3cd;color:#856404;">Voir tout le stock →</a>
        </div>
      </div>

      <!-- Péremptions -->
      <?php if($perempts): ?>
      <div class="card">
        <div class="card-title">⏰ Lots expirant bientôt</div>
        <div class="table-wrap"><table>
          <thead><tr><th>Médicament</th><th>N° Lot</th><th>Expiration</th><th>Jours</th><th>Qté</th></tr></thead>
          <tbody>
          <?php foreach($perempts as $p): ?>
            <tr>
              <td style="font-size:.82rem;"><?=htmlspecialchars($p['denomination'])?></td>
              <td style="font-size:.78rem;color:#888;"><?=htmlspecialchars($p['numero_lot'])?></td>
              <td style="font-size:.82rem;"><?=Helper::dateFr($p['date_peremption'])?></td>
              <td><span class="badge <?=$p['jours_restants']<=7?'badge-danger':'badge-warn'?>"><?=$p['jours_restants']?>j</span></td>
              <td><?=$p['quantite_restante']?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>
      <?php endif; ?>

    </div><!-- .g2 -->
  </div><!-- .content-area -->
</main>
<script>
setInterval(()=>{
  document.getElementById('clk').textContent = new Date().toLocaleString('fr-SN');
},1000);
</script>
</body>
</html>
PHP
log "index.php (dashboard) ✓"

# ────────────────────────────────────────────────────────────────
# Sidebar partial (nécessaire pour index.php et les modules)
# ────────────────────────────────────────────────────────────────
mkdir -p "$PHA/templates/partials"
cat > "$PHA/templates/partials/sidebar.php" << 'PHP'
<?php
$uri = $_SERVER['REQUEST_URI'];
function nav_active(string $kw): string {
    global $uri;
    return strpos($uri, $kw) !== false ? 'active' : '';
}
?>
<nav class="sidebar">
  <div class="sidebar-logo">
    <span style="font-size:1.6rem;">💊</span>
    <span>PharmaSen</span>
  </div>
  <ul class="nav-menu">
    <li class="<?=($uri==='/'||strpos($uri,'index.php')!==false&&strpos($uri,'modules')===false)?'active':''?>">
      <a href="/index.php">📊 Tableau de bord</a></li>
    <li class="<?=nav_active('caisse')?>">
      <a href="/modules/caisse/pos.php">🛒 Point de Vente</a></li>
    <li class="<?=nav_active('medicaments')?>">
      <a href="/modules/medicaments/">💊 Médicaments</a></li>
    <li class="<?=nav_active('/stock')?>">
      <a href="/modules/stock/">📦 Stock</a></li>
    <li class="<?=nav_active('ordonnances')?>">
      <a href="/modules/ordonnances/">📋 Ordonnances</a></li>
    <li class="<?=nav_active('clients')?>">
      <a href="/modules/clients/">👥 Clients</a></li>
    <li class="<?=nav_active('fournisseurs')?>">
      <a href="/modules/fournisseurs/">🏭 Fournisseurs</a></li>
    <li class="<?=nav_active('achats')?>">
      <a href="/modules/achats/">📥 Achats</a></li>
    <li class="<?=nav_active('rapports')?>">
      <a href="/modules/rapports/">📈 Rapports</a></li>
    <?php if(Auth::hasRole('admin')): ?>
    <li class="<?=nav_active('utilisateurs')?>">
      <a href="/modules/utilisateurs/">⚙️ Utilisateurs</a></li>
    <?php endif; ?>
  </ul>
  <div class="sidebar-footer">
    <div class="user-info">👤 <?=htmlspecialchars(Auth::getUser()['nom']??'')?></div>
    <div class="user-role"><?=htmlspecialchars(Auth::getUser()['role']??'')?></div>
    <a href="/logout.php" class="btn-logout">🚪 Déconnexion</a>
  </div>
</nav>
PHP
log "templates/partials/sidebar.php ✓"

# ────────────────────────────────────────────────────────────────
# modules/stock/index.php
# ────────────────────────────────────────────────────────────────
mkdir -p "$PHA/modules/stock"
cat > "$PHA/modules/stock/index.php" << 'PHP'
<?php
require_once dirname(__DIR__,2).'/core/Auth.php';
require_once dirname(__DIR__,2).'/core/Helper.php';
require_once dirname(__DIR__,2).'/core/Database.php';
require_once dirname(__DIR__,2).'/config/config.php';
Auth::check();
$stock  = Database::query("SELECT * FROM v_stock_critique ORDER BY FIELD(statut_stock,'🔴 RUPTURE','🟠 CRITIQUE','🟢 OK'), denomination");
$perems = Database::query("SELECT * FROM v_peremptions_proches ORDER BY jours_restants LIMIT 50");
$mvts   = Database::query("SELECT ms.*,m.denomination FROM mouvements_stock ms JOIN medicaments m ON m.id=ms.medicament_id ORDER BY ms.date_mouvement DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Stock — PharmaSen</title>
<link rel="stylesheet" href="/assets/css/main.css">
</head><body>
<?php include dirname(__DIR__,2).'/templates/partials/sidebar.php'; ?>
<main class="main-content">
  <div class="topbar">
    <h1 class="page-title">📦 Gestion du Stock</h1>
  </div>
  <div class="content-area">

    <!-- Onglets -->
    <div style="display:flex;gap:8px;margin-bottom:16px;">
      <button class="btn btn-primary btn-sm" onclick="showTab('stock')">📊 État stock</button>
      <button class="btn btn-sm" style="background:#fff3cd;color:#856404;" onclick="showTab('perems')">⏰ Péremptions</button>
      <button class="btn btn-sm" style="background:#e3f2fd;color:#1565c0;" onclick="showTab('mvts')">📋 Mouvements</button>
    </div>

    <!-- Stock -->
    <div id="tab-stock" class="card">
      <div class="card-title">📊 État du stock par médicament</div>
      <div class="table-wrap"><table>
        <thead><tr><th>Médicament</th><th>Fournisseur</th><th>Stock actuel</th><th>Stock min</th><th>À commander</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach($stock as $s): ?>
          <tr>
            <td><div style="font-weight:600;"><?=htmlspecialchars($s['denomination'])?></div>
                <div style="font-size:.72rem;color:#888;"><?=htmlspecialchars($s['nom_commercial']??'')?></div></td>
            <td style="font-size:.8rem;"><?=htmlspecialchars($s['fournisseur']??'-')?></td>
            <td style="font-weight:800;font-size:1.05rem;color:<?=$s['stock_actuel']<=0?'#dc3545':($s['stock_actuel']<=$s['stock_min']?'#856404':'#28a745')?>"><?=$s['stock_actuel']?></td>
            <td><?=$s['stock_min']?></td>
            <td style="font-weight:700;color:#dc3545;"><?=$s['quantite_a_commander']>0?$s['quantite_a_commander']:'-'?></td>
            <td><?=$s['statut_stock']?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>

    <!-- Péremptions -->
    <div id="tab-perems" class="card" style="display:none;">
      <div class="card-title">⏰ Lots proches de la péremption</div>
      <div class="table-wrap"><table>
        <thead><tr><th>Médicament</th><th>Lot</th><th>Date péremption</th><th>Jours restants</th><th>Quantité restante</th></tr></thead>
        <tbody>
        <?php foreach($perems as $p): ?>
          <tr>
            <td><?=htmlspecialchars($p['denomination'])?></td>
            <td style="font-family:monospace;"><?=htmlspecialchars($p['numero_lot'])?></td>
            <td><?=Helper::dateFr($p['date_peremption'])?></td>
            <td><span class="badge <?=$p['jours_restants']<=7?'badge-danger':($p['jours_restants']<=30?'badge-warn':'badge-ok')?>"><?=$p['jours_restants']?> jours</span></td>
            <td><?=$p['quantite_restante']?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$perems): ?><tr><td colspan="5" style="text-align:center;color:#aaa;padding:24px;">✅ Aucune péremption proche</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </div>

    <!-- Mouvements -->
    <div id="tab-mvts" class="card" style="display:none;">
      <div class="card-title">📋 Derniers mouvements de stock</div>
      <div class="table-wrap"><table>
        <thead><tr><th>Date</th><th>Médicament</th><th>Type</th><th>Qté</th><th>Avant</th><th>Après</th><th>Note</th></tr></thead>
        <tbody>
        <?php foreach($mvts as $m): ?>
          <tr>
            <td style="font-size:.78rem;"><?=Helper::datetimeFr($m['date_mouvement'])?></td>
            <td style="font-size:.82rem;"><?=htmlspecialchars($m['denomination'])?></td>
            <td><span class="badge <?=str_contains($m['type_mouvement'],'entree')?'badge-ok':'badge-warn'?>"><?=str_replace('_',' ',$m['type_mouvement'])?></span></td>
            <td style="font-weight:700;"><?=($m['type_mouvement']!=='entree'?'-':'+')?><?=$m['quantite']?></td>
            <td style="color:#888;"><?=$m['stock_avant']?></td>
            <td style="font-weight:600;"><?=$m['stock_apres']?></td>
            <td style="font-size:.78rem;color:#888;"><?=htmlspecialchars($m['notes']??'')?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>

  </div>
</main>
<script>
function showTab(t){
  ['stock','perems','mvts'].forEach(id=>{
    document.getElementById('tab-'+id).style.display = id===t?'block':'none';
  });
}
</script>
</body></html>
PHP
log "modules/stock/index.php ✓"

# ────────────────────────────────────────────────────────────────
# modules/clients/index.php
# ────────────────────────────────────────────────────────────────
mkdir -p "$PHA/modules/clients"
cat > "$PHA/modules/clients/index.php" << 'PHP'
<?php
require_once dirname(__DIR__,2).'/core/Auth.php';
require_once dirname(__DIR__,2).'/core/Helper.php';
require_once dirname(__DIR__,2).'/core/Database.php';
require_once dirname(__DIR__,2).'/config/config.php';
Auth::check();
$s = trim($_GET['s']??'');
$clients = $s
    ? Database::query("SELECT * FROM clients WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ? OR cni LIKE ? ORDER BY nom",['%'.$s.'%','%'.$s.'%','%'.$s.'%','%'.$s.'%'])
    : Database::query("SELECT * FROM clients ORDER BY nom LIMIT 100");
?>
<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8"><title>Clients — PharmaSen</title>
<link rel="stylesheet" href="/assets/css/main.css">
</head><body>
<?php include dirname(__DIR__,2).'/templates/partials/sidebar.php'; ?>
<main class="main-content">
  <div class="topbar">
    <h1 class="page-title">👥 Clients</h1>
    <div class="topbar-right">
      <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal').style.display='flex'">+ Nouveau client</button>
    </div>
  </div>
  <div class="content-area">
    <div class="card" style="padding:12px;">
      <form method="GET" style="display:flex;gap:10px;">
        <input class="form-control" name="s" value="<?=htmlspecialchars($s)?>" placeholder="🔍 Nom, téléphone, CNI…" style="max-width:300px;">
        <button class="btn btn-primary btn-sm" type="submit">Rechercher</button>
        <?php if($s): ?><a href="?" class="btn btn-sm" style="background:#eee;">Réinitialiser</a><?php endif; ?>
      </form>
    </div>
    <div class="card">
      <div style="font-size:.85rem;color:#888;margin-bottom:10px;"><?=count($clients)?> client(s)</div>
      <div class="table-wrap"><table>
        <thead><tr><th>Nom</th><th>Téléphone</th><th>CNI</th><th>Mutuelle</th><th>Crédit autorisé</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($clients as $c): ?>
          <tr>
            <td><div style="font-weight:600;"><?=htmlspecialchars($c['nom'].' '.$c['prenom'])?></div>
                <div style="font-size:.72rem;color:#888;"><?=htmlspecialchars($c['adresse']??'')?></div></td>
            <td><?=htmlspecialchars($c['telephone'])?></td>
            <td style="font-family:monospace;font-size:.82rem;"><?=htmlspecialchars($c['cni']??'-')?></td>
            <td><?=htmlspecialchars($c['mutuelle']??'-')?></td>
            <td style="font-weight:600;color:var(--primary)"><?=Helper::fcfa($c['credit_autorise'])?></td>
            <td>
              <button class="btn btn-sm" style="background:#e3f2fd;color:#1565c0;"
                onclick='editClient(<?=htmlspecialchars(json_encode($c))?>)'>✏️</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$clients): ?><tr><td colspan="6" style="text-align:center;color:#aaa;padding:24px;">Aucun client trouvé</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </div>
  </div>
</main>
<!-- MODAL -->
<div id="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:14px;padding:24px;width:500px;max-height:90vh;overflow-y:auto;">
    <h3 id="mtitle" style="color:var(--primary);margin-bottom:18px;">Nouveau client</h3>
    <form id="cliForm">
      <input type="hidden" id="cli_id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group"><label>Nom *</label><input class="form-control" id="cli_nom" required></div>
        <div class="form-group"><label>Prénom</label><input class="form-control" id="cli_prenom"></div>
        <div class="form-group"><label>Téléphone *</label><input class="form-control" id="cli_tel" required></div>
        <div class="form-group"><label>CNI</label><input class="form-control" id="cli_cni"></div>
        <div class="form-group" style="grid-column:1/-1"><label>Adresse</label><input class="form-control" id="cli_adresse"></div>
        <div class="form-group"><label>Mutuelle</label>
          <select class="form-control" id="cli_mutuelle">
            <option value="">— Aucune —</option>
            <option>IPM</option><option>IPRES</option><option>CSS</option>
            <option>MSAS</option><option>Privée</option>
          </select>
        </div>
        <div class="form-group"><label>N° Assurance</label><input class="form-control" id="cli_assur"></div>
        <div class="form-group"><label>Crédit autorisé (FCFA)</label><input class="form-control" type="number" id="cli_credit" value="0" min="0"></div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
        <button type="button" class="btn" style="background:#eee;" onclick="document.getElementById('modal').style.display='none'">Annuler</button>
        <button type="button" class="btn btn-primary" onclick="saveCli()">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>
<script>
function editClient(c){
  document.getElementById('modal').style.display='flex';
  document.getElementById('mtitle').textContent='Modifier client';
  document.getElementById('cli_id').value=c.id;
  document.getElementById('cli_nom').value=c.nom||'';
  document.getElementById('cli_prenom').value=c.prenom||'';
  document.getElementById('cli_tel').value=c.telephone||'';
  document.getElementById('cli_cni').value=c.cni||'';
  document.getElementById('cli_adresse').value=c.adresse||'';
  document.getElementById('cli_mutuelle').value=c.mutuelle||'';
  document.getElementById('cli_assur').value=c.num_assurance||'';
  document.getElementById('cli_credit').value=c.credit_autorise||0;
}
async function saveCli(){
  const id=document.getElementById('cli_id').value;
  const b={nom:document.getElementById('cli_nom').value,
    prenom:document.getElementById('cli_prenom').value,
    telephone:document.getElementById('cli_tel').value,
    cni:document.getElementById('cli_cni').value,
    adresse:document.getElementById('cli_adresse').value,
    mutuelle:document.getElementById('cli_mutuelle').value,
    num_assurance:document.getElementById('cli_assur').value,
    credit_autorise:parseFloat(document.getElementById('cli_credit').value)||0};
  const url=id?`clients_api.php?action=update&id=${id}`:'clients_api.php?action=create';
  const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(b)});
  const j=await r.json();
  j.success?location.reload():alert('Erreur: '+j.message);
}
</script>
</body></html>
PHP
log "modules/clients/index.php ✓"

# ── clients_api.php ────────────────────────────────────────────
cat > "$PHA/modules/clients/clients_api.php" << 'PHP'
<?php
require_once dirname(__DIR__,2).'/core/Auth.php';
require_once dirname(__DIR__,2).'/core/Helper.php';
require_once dirname(__DIR__,2).'/core/Database.php';
header('Content-Type: application/json; charset=utf-8');
Auth::check();
$a=$_GET['action']??'list';
try {
    switch($a){
        case 'list':
            $s='%'.(Helper::sanitize($_GET['s']??'')).'%';
            Helper::jsonResponse(true,Database::query("SELECT * FROM clients WHERE nom LIKE ? OR telephone LIKE ? ORDER BY nom LIMIT 200",[$s,$s]));
        case 'get':
            Helper::jsonResponse(true,Database::queryOne("SELECT * FROM clients WHERE id=?",[(int)$_GET['id']]));
        case 'create':
            $b=json_decode(file_get_contents('php://input'),true)??[];
            Database::execute("INSERT INTO clients(nom,prenom,telephone,cni,adresse,mutuelle,num_assurance,credit_autorise) VALUES(?,?,?,?,?,?,?,?)",
                [$b['nom'],$b['prenom']??null,$b['telephone'],$b['cni']??null,$b['adresse']??null,$b['mutuelle']??null,$b['num_assurance']??null,$b['credit_autorise']??0]);
            Helper::jsonResponse(true,['id'=>Database::lastId()],'Client créé');
        case 'update':
            $b=json_decode(file_get_contents('php://input'),true)??[];
            Database::execute("UPDATE clients SET nom=?,prenom=?,telephone=?,cni=?,adresse=?,mutuelle=?,credit_autorise=? WHERE id=?",
                [$b['nom'],$b['prenom']??null,$b['telephone'],$b['cni']??null,$b['adresse']??null,$b['mutuelle']??null,$b['credit_autorise']??0,(int)$_GET['id']]);
            Helper::jsonResponse(true,null,'Modifié');
        default: Helper::jsonResponse(false,null,'Action inconnue');
    }
} catch(Throwable $e){ Helper::jsonResponse(false,null,$e->getMessage()); }
PHP
log "modules/clients/clients_api.php ✓"

# ── modules/rapports/index.php ────────────────────────────────
mkdir -p "$PHA/modules/rapports"
cat > "$PHA/modules/rapports/index.php" << 'PHP'
<?php
require_once dirname(__DIR__,2).'/core/Auth.php';
require_once dirname(__DIR__,2).'/core/Helper.php';
require_once dirname(__DIR__,2).'/core/Database.php';
require_once dirname(__DIR__,2).'/config/config.php';
Auth::check();
$ca  = Database::query("SELECT * FROM v_ca_journalier LIMIT 30");
$top = Database::query("SELECT * FROM v_top_medicaments LIMIT 15");
?>
<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8"><title>Rapports — PharmaSen</title>
<link rel="stylesheet" href="/assets/css/main.css">
</head><body>
<?php include dirname(__DIR__,2).'/templates/partials/sidebar.php'; ?>
<main class="main-content">
  <div class="topbar"><h1 class="page-title">📈 Rapports & Statistiques</h1></div>
  <div class="content-area">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div class="card">
        <div class="card-title">💰 CA Journalier (30 derniers jours)</div>
        <div class="table-wrap"><table>
          <thead><tr><th>Jour</th><th>Ventes</th><th>CA Total</th><th>Espèces</th><th>Mobile</th><th>Mutuelle</th></tr></thead>
          <tbody>
          <?php foreach($ca as $r): ?>
            <tr>
              <td style="font-weight:600;"><?=Helper::dateFr($r['jour'])?></td>
              <td style="text-align:center;"><?=$r['nb_ventes']?></td>
              <td style="font-weight:700;color:var(--primary)"><?=Helper::fcfa($r['ca_total'])?></td>
              <td style="font-size:.82rem;"><?=Helper::fcfa($r['especes'])?></td>
              <td style="font-size:.82rem;"><?=Helper::fcfa($r['mobile_money'])?></td>
              <td style="font-size:.82rem;"><?=Helper::fcfa($r['mutuelle'])?></td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$ca): ?><tr><td colspan="6" style="text-align:center;color:#aaa;padding:24px;">Aucune donnée</td></tr><?php endif; ?>
          </tbody>
        </table></div>
      </div>
      <div class="card">
        <div class="card-title">🏆 Top médicaments vendus</div>
        <div class="table-wrap"><table>
          <thead><tr><th>Rang</th><th>Médicament</th><th>Qté vendue</th><th>CA généré</th></tr></thead>
          <tbody>
          <?php foreach($top as $i=>$r): ?>
            <tr>
              <td style="font-weight:700;color:<?=$i===0?'#f0a500':($i===1?'#888':($i===2?'#cd7f32':'#aaa'))?>;font-size:1.1rem;"><?=$i===0?'🥇':($i===1?'🥈':($i===2?'🥉':($i+1)))?></td>
              <td><div style="font-weight:600;font-size:.85rem;"><?=htmlspecialchars($r['denomination'])?></div></td>
              <td style="font-weight:700;"><?=$r['total_vendu']?></td>
              <td style="font-weight:700;color:var(--primary)"><?=Helper::fcfa($r['ca_genere'])?></td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$top): ?><tr><td colspan="4" style="text-align:center;color:#aaa;padding:24px;">Aucune donnée</td></tr><?php endif; ?>
          </tbody>
        </table></div>
      </div>
    </div>
  </div>
</main>
</body></html>
PHP
log "modules/rapports/index.php ✓"

# ────────────────────────────────────────────────────────────────
# RÉSUMÉ
# ────────────────────────────────────────────────────────────────
echo ""
NB=$(find "$PHA" -name "*.php" | wc -l)
echo -e "${GREEN}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅  TOUS LES FICHIERS PHP SONT CRÉÉS                ║${NC}"
echo -e "${GREEN}╠══════════════════════════════════════════════════════╣${NC}"
printf "${GREEN}║${NC}  📄 Total fichiers PHP : %-5s                        ${GREEN}║${NC}\n" "$NB"
echo -e "${GREEN}╠══════════════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║  🌐  Accès immédiat (serveur déjà démarré) :          ║${NC}"
echo -e "${GREEN}║  👉  http://127.0.0.1:8000/login.php                 ║${NC}"
echo -e "${GREEN}║  👉  http://127.0.0.1:8000/index.php                 ║${NC}"
echo -e "${GREEN}║  🛒  http://127.0.0.1:8000/modules/caisse/pos.php    ║${NC}"
echo -e "${GREEN}╠══════════════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║  🔐  admin  /  password                               ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════╝${NC}"
echo ""
echo "Liste complète des PHP :"
find "$PHA" -name "*.php" | sort | sed "s|$PHA||"
