<?php
if(!defined('ADMIN_PAGE')) define('ADMIN_PAGE', true);
$pdo = getPDO();
$alertCount = alertStock($pdo);
$flash = getFlash();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Stats rapides pour sidebar
$today = date('Y-m-d');
$ventesToday = $pdo->query("SELECT COALESCE(SUM(total),0) as t FROM ventes WHERE DATE(date_vente)='$today'")->fetch()['t'];
$factImpayees = $pdo->query("SELECT COUNT(*) as c FROM factures WHERE statut='emise'")->fetch()['c'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? 'Admin' ?> – OMEGA Charcuterie</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Raleway:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root{
  --bg:#0f0f0f;--sidebar:#141414;--card:#1a1a1a;--border:rgba(255,255,255,.07);
  --rouge:#c0392b;--or:#d4ac0d;--vert:#27ae60;--bleu:#2980b9;
  --text:#e0e0e0;--muted:#666;
}
*{box-sizing:border-box}
body{font-family:'Raleway',sans-serif;background:var(--bg);color:var(--text);margin:0;min-height:100vh}

/* ─ TOPBAR ─ */
.admin-topbar{
  background:linear-gradient(90deg,#1a0505,#2c0d0d,#1a0505);
  border-bottom:2px solid var(--rouge);
  height:58px;display:flex;align-items:center;justify-content:space-between;
  padding:0 20px 0 260px;position:fixed;top:0;left:0;right:0;z-index:200}
.topbar-brand{font-family:'Playfair Display',serif;font-size:.95rem;color:var(--or);
  font-weight:700;letter-spacing:1px;white-space:nowrap;overflow:hidden}
.topbar-brand small{display:block;color:#666;font-size:.65rem;font-family:'Raleway',sans-serif;letter-spacing:2px}
.topbar-right{display:flex;align-items:center;gap:15px}
.topbar-stat{background:rgba(255,255,255,.05);border:1px solid var(--border);
  border-radius:8px;padding:5px 12px;font-size:.75rem;color:var(--muted)}
.topbar-stat strong{color:var(--or);display:block;font-size:.85rem}
.topbar-user{display:flex;align-items:center;gap:8px;color:var(--text);font-size:.85rem}
.topbar-user .avatar{width:32px;height:32px;border-radius:50%;
  background:linear-gradient(135deg,var(--rouge),var(--or));
  display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem}
.btn-logout{background:rgba(192,57,43,.2);border:1px solid rgba(192,57,43,.3);
  color:var(--rouge);padding:5px 12px;border-radius:8px;font-size:.75rem;
  text-decoration:none;transition:.3s;font-weight:600}
.btn-logout:hover{background:var(--rouge);color:#fff}

/* ─ SIDEBAR ─ */
.sidebar{
  width:240px;background:var(--sidebar);border-right:1px solid var(--border);
  position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:300;
  display:flex;flex-direction:column}
.sidebar-logo{padding:18px 20px;border-bottom:1px solid var(--border);
  background:linear-gradient(135deg,rgba(192,57,43,.15),rgba(212,172,13,.05))}
.sidebar-logo h2{font-family:'Playfair Display',serif;color:var(--or);font-size:1rem;
  margin:0;display:flex;align-items:center;gap:8px}
.sidebar-logo p{color:#555;font-size:.65rem;letter-spacing:2px;text-transform:uppercase;margin:3px 0 0}
.sidebar-nav{flex:1;padding:15px 0}
.nav-section{padding:8px 20px 5px;font-size:.6rem;color:#444;letter-spacing:3px;
  text-transform:uppercase;font-weight:700;margin-top:10px}
.sidebar-nav a{
  display:flex;align-items:center;gap:12px;padding:11px 20px;
  color:#888;text-decoration:none;font-size:.85rem;font-weight:600;
  transition:.2s;border-left:3px solid transparent;position:relative}
.sidebar-nav a:hover{color:var(--text);background:rgba(255,255,255,.04)}
.sidebar-nav a.active{color:var(--or);background:rgba(212,172,13,.07);border-left-color:var(--or)}
.sidebar-nav a .icon{width:22px;text-align:center;font-size:.9rem}
.badge-alert{background:var(--rouge);color:#fff;font-size:.6rem;padding:2px 6px;
  border-radius:10px;margin-left:auto;font-weight:700}
.sidebar-footer{padding:15px 20px;border-top:1px solid var(--border);font-size:.75rem;color:#444;text-align:center}

/* ─ MAIN ─ */
.admin-main{margin-left:240px;margin-top:58px;padding:25px;min-height:calc(100vh - 58px)}
.page-header{margin-bottom:25px}
.page-header h1{font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--text);
  display:flex;align-items:center;gap:12px;margin:0 0 5px}
.page-header h1 span{background:linear-gradient(135deg,var(--rouge),var(--or));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.page-header p{color:var(--muted);font-size:.85rem;margin:0}

/* ─ CARDS ─ */
.card-omega{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:20px}
.card-omega .card-head{padding:16px 20px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between}
.card-omega .card-head h4{color:var(--text);font-size:.95rem;font-weight:700;
  margin:0;display:flex;align-items:center;gap:10px}
.card-omega .card-head h4 i{color:var(--or)}
.card-omega .card-body{padding:20px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;
  transition:.3s;position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,var(--color1),var(--color2))}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.3)}
.stat-card .label{color:var(--muted);font-size:.75rem;letter-spacing:1px;text-transform:uppercase;font-weight:600}
.stat-card .value{font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--text);
  margin:8px 0 5px;font-weight:700}
.stat-card .icon-bg{position:absolute;right:20px;top:50%;transform:translateY(-50%);
  font-size:2.5rem;opacity:.1}

/* ─ TABLES ─ */
.table-omega{width:100%;border-collapse:collapse;font-size:.85rem}
.table-omega th{background:rgba(255,255,255,.04);color:var(--muted);font-weight:700;
  font-size:.75rem;text-transform:uppercase;letter-spacing:1px;padding:12px 15px;
  border-bottom:1px solid var(--border);white-space:nowrap}
.table-omega td{padding:11px 15px;border-bottom:1px solid rgba(255,255,255,.04);
  color:var(--text);vertical-align:middle}
.table-omega tr:hover td{background:rgba(255,255,255,.02)}
.table-omega tr:last-child td{border-bottom:none}
.badge-stat{font-size:.7rem;padding:4px 10px;border-radius:20px;font-weight:700;white-space:nowrap}
.b-success{background:rgba(39,174,96,.15);color:#27ae60;border:1px solid rgba(39,174,96,.3)}
.b-warning{background:rgba(230,126,34,.15);color:#e67e22;border:1px solid rgba(230,126,34,.3)}
.b-danger{background:rgba(192,57,43,.15);color:#e74c3c;border:1px solid rgba(192,57,43,.3)}
.b-info{background:rgba(41,128,185,.15);color:#3498db;border:1px solid rgba(41,128,185,.3)}
.b-muted{background:rgba(255,255,255,.05);color:#888;border:1px solid var(--border)}

/* ─ FORMS ─ */
.form-omega .form-label{color:#ccc;font-size:.82rem;font-weight:600;margin-bottom:5px;letter-spacing:.3px}
.form-omega .form-control,.form-omega .form-select{
  background:rgba(255,255,255,.05);border:1px solid var(--border);
  border-radius:10px;color:var(--text);font-family:'Raleway',sans-serif;
  font-size:.9rem;padding:10px 14px;transition:.3s}
.form-omega .form-control:focus,.form-omega .form-select:focus{
  background:rgba(192,57,43,.08);border-color:var(--rouge);
  box-shadow:0 0 0 3px rgba(192,57,43,.12);color:var(--text)}
.form-omega .form-control::placeholder{color:#444}
.form-omega .form-select option{background:#1a1a1a;color:var(--text)}
.form-omega textarea.form-control{resize:vertical;min-height:80px}
.btn-omega{padding:10px 22px;border-radius:10px;font-weight:700;font-size:.85rem;
  letter-spacing:.5px;border:none;cursor:pointer;transition:.3s;font-family:'Raleway',sans-serif}
.btn-omega-primary{background:linear-gradient(135deg,var(--rouge),#922b21);color:#fff}
.btn-omega-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(192,57,43,.4);color:#fff}
.btn-omega-gold{background:linear-gradient(135deg,var(--or),#b7950b);color:#0d0d0d}
.btn-omega-gold:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(212,172,13,.4);color:#0d0d0d}
.btn-omega-outline{background:transparent;border:1px solid var(--border);color:var(--muted)}
.btn-omega-outline:hover{border-color:var(--or);color:var(--or)}
.btn-omega-danger{background:rgba(192,57,43,.2);border:1px solid rgba(192,57,43,.3);color:var(--rouge)}
.btn-omega-danger:hover{background:var(--rouge);color:#fff}
.btn-omega-success{background:linear-gradient(135deg,#27ae60,#1e8449);color:#fff}
.btn-omega-success:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(39,174,96,.3);color:#fff}

/* ─ IMG THUMBNAIL ─ */
.img-thumb{width:48px;height:48px;object-fit:cover;border-radius:8px;
  border:1px solid var(--border);background:rgba(255,255,255,.05)}
.img-placeholder{width:48px;height:48px;border-radius:8px;background:rgba(255,255,255,.05);
  border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.3rem}

/* ─ FLASH ─ */
.flash-success{background:rgba(39,174,96,.15);border:1px solid rgba(39,174,96,.3);
  color:#27ae60;padding:12px 16px;border-radius:10px;font-size:.85rem;
  display:flex;align-items:center;gap:10px;margin-bottom:20px}
.flash-error{background:rgba(192,57,43,.15);border:1px solid rgba(192,57,43,.3);
  color:#e74c3c;padding:12px 16px;border-radius:10px;font-size:.85rem;
  display:flex;align-items:center;gap:10px;margin-bottom:20px}

/* ─ Responsive ─ */
@media(max-width:992px){
  .sidebar{transform:translateX(-100%)}
  .admin-main{margin-left:0}
  .admin-topbar{padding-left:20px}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>🥩 OMEGA</h2>
    <p>Gestion Charcuterie</p>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Principal</div>
    <a href="index.php" class="<?= $currentPage==='index'?'active':'' ?>">
      <span class="icon"><i class="fas fa-tachometer-alt"></i></span> Tableau de bord
    </a>
    <div class="nav-section">Catalogue</div>
    <a href="categories.php" class="<?= $currentPage==='categories'?'active':'' ?>">
      <span class="icon"><i class="fas fa-tags"></i></span> Catégories
    </a>
    <a href="produits.php" class="<?= $currentPage==='produits'?'active':'' ?>">
      <span class="icon"><i class="fas fa-box-open"></i></span> Produits
    </a>
    <div class="nav-section">Commerce</div>
    <a href="ventes.php" class="<?= $currentPage==='ventes'?'active':'' ?>">
      <span class="icon"><i class="fas fa-cash-register"></i></span> Ventes / Caisse
    </a>
    <a href="factures.php" class="<?= $currentPage==='factures'?'active':'' ?>">
      <span class="icon"><i class="fas fa-file-invoice"></i></span> Facturation
      <?php if($factImpayees>0): ?><span class="badge-alert"><?=$factImpayees?></span><?php endif; ?>
    </a>
    <a href="clients.php" class="<?= $currentPage==='clients'?'active':'' ?>">
      <span class="icon"><i class="fas fa-users"></i></span> Clients
    </a>
    <div class="nav-section">Stock & Achats</div>
    <a href="stock.php" class="<?= $currentPage==='stock'?'active':'' ?>">
      <span class="icon"><i class="fas fa-warehouse"></i></span> Gestion Stock
      <?php if($alertCount>0): ?><span class="badge-alert"><?=$alertCount?></span><?php endif; ?>
    </a>
    <a href="approvisionnement.php" class="<?= $currentPage==='approvisionnement'?'active':'' ?>">
      <span class="icon"><i class="fas fa-truck-loading"></i></span> Approvisionnement
    </a>
    <a href="fournisseurs.php" class="<?= $currentPage==='fournisseurs'?'active':'' ?>">
      <span class="icon"><i class="fas fa-industry"></i></span> Fournisseurs
    </a>
    <div class="nav-section">Finance</div>
    <a href="depenses.php" class="<?= $currentPage==='depenses'?'active':'' ?>">
      <span class="icon"><i class="fas fa-money-bill-wave"></i></span> Dépenses
    </a>
    <a href="rapports.php" class="<?= $currentPage==='rapports'?'active':'' ?>">
      <span class="icon"><i class="fas fa-chart-line"></i></span> Rapports & Stats
    </a>
    <div class="nav-section">Système</div>
    <a href="../index.php" target="_blank">
      <span class="icon"><i class="fas fa-globe"></i></span> Voir le site
    </a>
    <a href="logout.php">
      <span class="icon"><i class="fas fa-sign-out-alt"></i></span> Déconnexion
    </a>
  </nav>
  <div class="sidebar-footer">OMEGA INFORMATIQUE v2.0<br>© 2026</div>
</aside>

<!-- TOPBAR -->
<header class="admin-topbar">
  <div class="topbar-brand">
    🏆 OMEGA INFORMATIQUE CONSULTING
    <small>GESTION CHARCUTERIE — ESPACE ADMINISTRATION</small>
  </div>
  <div class="topbar-right">
    <div class="topbar-stat">
      <strong><?= number_format($ventesToday,0,',',' ') ?> FCFA</strong>
      Ventes aujourd'hui
    </div>
    <?php if($alertCount>0): ?>
    <a href="stock.php" class="badge-alert" style="text-decoration:none;font-size:.75rem;padding:5px 12px;border-radius:8px">
      ⚠ <?=$alertCount?> alerte<?=$alertCount>1?'s':''?> stock
    </a>
    <?php endif; ?>
    <div class="topbar-user">
      <div class="avatar"><?= strtoupper(substr($_SESSION['admin_nom']??'A',0,1)) ?></div>
      <?= htmlspecialchars($_SESSION['admin_nom']??'Admin') ?>
    </div>
    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
  </div>
</header>

<!-- MAIN CONTENT -->
<main class="admin-main">
<?php if($flash): ?>
<div class="flash-<?= $flash['type']==='success'?'success':'error' ?>">
  <i class="fas fa-<?= $flash['type']==='success'?'check-circle':'exclamation-circle' ?>"></i>
  <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>
