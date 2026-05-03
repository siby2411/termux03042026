<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Accès Rapide';
$pdo = getPDO();

// Compteurs pour les badges
$nbProd    = $pdo->query("SELECT COUNT(*) FROM produits WHERE actif=1")->fetchColumn();
$nbClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$nbFact    = $pdo->query("SELECT COUNT(*) FROM factures WHERE statut='emise'")->fetchColumn();
$nbAlerts  = $pdo->query("SELECT COUNT(*) FROM produits WHERE stock_actuel<=stock_min AND stock_min>0")->fetchColumn();
$nbFourn   = $pdo->query("SELECT COUNT(*) FROM fournisseurs")->fetchColumn();
$caJour    = $pdo->query("SELECT COALESCE(SUM(total),0) FROM ventes WHERE DATE(date_vente)=CURDATE()")->fetchColumn();

require_once 'header.php';
?>
<style>
.acces-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:30px}
.acces-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:0;overflow:hidden;transition:.3s;text-decoration:none;display:block}
.acces-card:hover{transform:translateY(-5px);box-shadow:0 15px 35px rgba(0,0,0,.3);border-color:var(--card-accent)}
.acces-card-header{padding:20px 20px 15px;background:linear-gradient(135deg,var(--c1),var(--c2));position:relative;overflow:hidden}
.acces-card-header::after{content:var(--ico);position:absolute;right:-5px;top:-5px;font-size:3.5rem;opacity:.15}
.acces-card-header h3{color:#fff;font-size:1rem;font-weight:700;margin:0 0 4px;position:relative;z-index:1}
.acces-card-header p{color:rgba(255,255,255,.8);font-size:.75rem;margin:0;position:relative;z-index:1}
.acces-card-body{padding:15px 20px}
.acces-card-body .count{font-size:1.6rem;font-weight:900;color:var(--card-accent);font-family:'Playfair Display',serif}
.acces-card-body .count-label{font-size:.72rem;color:var(--muted);margin-bottom:12px}
.acces-btns{display:flex;gap:6px;flex-wrap:wrap}
.acces-btn{padding:7px 14px;border-radius:8px;font-size:.75rem;font-weight:700;text-decoration:none;transition:.2s;display:inline-flex;align-items:center;gap:5px;border:none;cursor:pointer;font-family:'Raleway',sans-serif}
.acces-btn-add{background:linear-gradient(135deg,var(--c1),var(--c2));color:#fff}
.acces-btn-add:hover{opacity:.9;color:#fff}
.acces-btn-list{background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text)}
.acces-btn-list:hover{border-color:var(--card-accent);color:var(--card-accent)}
.section-title{font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--or);margin:25px 0 12px;display:flex;align-items:center;gap:10px}
.section-title::after{content:'';flex:1;height:1px;background:var(--border)}
/* Raccourcis clavier */
.kbd{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:4px;padding:2px 6px;font-size:.7rem;color:#aaa;font-family:monospace}
</style>

<div class="page-header">
  <h1><i class="fas fa-th" style="color:var(--or)"></i> <span>Accès Rapide</span></h1>
  <p>Tous les formulaires et modules en un clic — CA aujourd'hui : <strong style="color:var(--or)"><?= number_format($caJour,0,',',' ') ?> FCFA</strong></p>
</div>

<!-- ════════════════ CATALOGUE ════════════════ -->
<div class="section-title"><i class="fas fa-box-open"></i> Catalogue</div>
<div class="acces-grid">

  <a href="produits.php?action=add" class="acces-card" style="--c1:#c0392b;--c2:#922b21;--ico:'📦';--card-accent:var(--rouge)">
    <div class="acces-card-header">
      <h3><i class="fas fa-plus-circle"></i> Nouveau Produit</h3>
      <p>Ajouter au catalogue avec image</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $nbProd ?></div>
      <div class="count-label">produits actifs</div>
      <div class="acces-btns">
        <a href="produits.php?action=add" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Ajouter</a>
        <a href="produits.php" class="acces-btn acces-btn-list"><i class="fas fa-list"></i> Liste</a>
      </div>
    </div>
  </a>

  <a href="categories.php?action=add" class="acces-card" style="--c1:#8e44ad;--c2:#6c3483;--ico:'🏷️';--card-accent:#9b59b6">
    <div class="acces-card-header">
      <h3><i class="fas fa-tags"></i> Nouvelle Catégorie</h3>
      <p>Organiser le catalogue</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() ?></div>
      <div class="count-label">catégories</div>
      <div class="acces-btns">
        <a href="categories.php?action=add" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Ajouter</a>
        <a href="categories.php" class="acces-btn acces-btn-list"><i class="fas fa-list"></i> Liste</a>
      </div>
    </div>
  </a>

</div>

<!-- ════════════════ VENTES & FACTURATION ════════════════ -->
<div class="section-title"><i class="fas fa-cash-register"></i> Ventes & Facturation</div>
<div class="acces-grid">

  <a href="caisse_pos.php" class="acces-card" style="--c1:#27ae60;--c2:#1e8449;--ico:'🖥️';--card-accent:#27ae60">
    <div class="acces-card-header">
      <h3><i class="fas fa-cash-register"></i> Caisse POS</h3>
      <p>Vente rapide tactile</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $pdo->query("SELECT COUNT(*) FROM ventes WHERE DATE(date_vente)=CURDATE()")->fetchColumn() ?></div>
      <div class="count-label">ventes aujourd'hui</div>
      <div class="acces-btns">
        <a href="caisse_pos.php" class="acces-btn acces-btn-add"><i class="fas fa-play"></i> Ouvrir Caisse</a>
        <a href="ventes.php" class="acces-btn acces-btn-list"><i class="fas fa-history"></i> Historique</a>
      </div>
    </div>
  </a>

  <a href="factures.php?action=new" class="acces-card" style="--c1:#2980b9;--c2:#1a5276;--ico:'📄';--card-accent:#3498db">
    <div class="acces-card-header">
      <h3><i class="fas fa-file-invoice"></i> Nouvelle Facture</h3>
      <p>10+ produits, TVA, multi-client</p>
    </div>
    <div class="acces-card-body">
      <div class="count" style="color:<?= $nbFact>0?'#e67e22':'#27ae60' ?>"><?= $nbFact ?></div>
      <div class="count-label">facture<?= $nbFact>1?'s':'' ?> impayée<?= $nbFact>1?'s':'' ?></div>
      <div class="acces-btns">
        <a href="factures.php?action=new" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Créer</a>
        <a href="factures.php" class="acces-btn acces-btn-list"><i class="fas fa-list"></i> Liste</a>
      </div>
    </div>
  </a>

  <a href="clients.php?action=add" class="acces-card" style="--c1:#d4ac0d;--c2:#b7950b;--ico:'👥';--card-accent:var(--or)">
    <div class="acces-card-header">
      <h3><i class="fas fa-user-plus"></i> Nouveau Client</h3>
      <p>Particulier ou professionnel</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $nbClients ?></div>
      <div class="count-label">clients enregistrés</div>
      <div class="acces-btns">
        <a href="clients.php?action=add" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Ajouter</a>
        <a href="clients.php" class="acces-btn acces-btn-list"><i class="fas fa-list"></i> Liste</a>
      </div>
    </div>
  </a>

  <a href="ventes.php" class="acces-card" style="--c1:#16a085;--c2:#0e6655;--ico:'💰';--card-accent:#1abc9c">
    <div class="acces-card-header">
      <h3><i class="fas fa-chart-line"></i> Vente Directe</h3>
      <p>Enregistrement rapide sans facture</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $pdo->query("SELECT COUNT(*) FROM ventes")->fetchColumn() ?></div>
      <div class="count-label">ventes totales</div>
      <div class="acces-btns">
        <a href="ventes.php" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Enregistrer</a>
        <a href="ventes.php" class="acces-btn acces-btn-list"><i class="fas fa-history"></i> Historique</a>
      </div>
    </div>
  </a>

</div>

<!-- ════════════════ STOCK & ACHATS ════════════════ -->
<div class="section-title"><i class="fas fa-warehouse"></i> Stock & Approvisionnement</div>
<div class="acces-grid">

  <a href="approvisionnement.php" class="acces-card" style="--c1:#e67e22;--c2:#ca6f1e;--ico:'🚚';--card-accent:#f39c12">
    <div class="acces-card-header">
      <h3><i class="fas fa-truck-loading"></i> Nouvel Appro.</h3>
      <p>Entrée stock fournisseur</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $pdo->query("SELECT COUNT(*) FROM approvisionnements WHERE DATE_FORMAT(date_appro,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn() ?></div>
      <div class="count-label">entrées ce mois</div>
      <div class="acces-btns">
        <a href="approvisionnement.php" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Approvisionner</a>
        <a href="approvisionnement.php" class="acces-btn acces-btn-list"><i class="fas fa-list"></i> Historique</a>
      </div>
    </div>
  </a>

  <a href="fournisseurs.php?action=add" class="acces-card" style="--c1:#2980b9;--c2:#1f618d;--ico:'🏭';--card-accent:#3498db">
    <div class="acces-card-header">
      <h3><i class="fas fa-industry"></i> Nouveau Fournisseur</h3>
      <p>Référencer un fournisseur</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $nbFourn ?></div>
      <div class="count-label">fournisseurs actifs</div>
      <div class="acces-btns">
        <a href="fournisseurs.php?action=add" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Ajouter</a>
        <a href="fournisseurs.php" class="acces-btn acces-btn-list"><i class="fas fa-list"></i> Liste</a>
      </div>
    </div>
  </a>

  <a href="stock.php" class="acces-card" style="--c1:<?= $nbAlerts>0?'#c0392b':'#27ae60' ?>;--c2:<?= $nbAlerts>0?'#922b21':'#1e8449' ?>;--ico:'📦';--card-accent:<?= $nbAlerts>0?'#e74c3c':'#27ae60' ?>">
    <div class="acces-card-header">
      <h3><i class="fas fa-boxes"></i> État du Stock</h3>
      <p><?= $nbAlerts>0?"⚠️ $nbAlerts alerte(s) de rupture":'✅ Stocks à niveau' ?></p>
    </div>
    <div class="acces-card-body">
      <div class="count" style="color:<?= $nbAlerts>0?'#e74c3c':'#27ae60' ?>"><?= $nbAlerts ?></div>
      <div class="count-label">alerte<?= $nbAlerts>1?'s':'' ?> stock</div>
      <div class="acces-btns">
        <a href="stock.php?filter=alert" class="acces-btn acces-btn-add"><i class="fas fa-exclamation-triangle"></i> Alertes</a>
        <a href="stock.php" class="acces-btn acces-btn-list"><i class="fas fa-list"></i> Inventaire</a>
      </div>
    </div>
  </a>

  <a href="inventaire.php" class="acces-card" style="--c1:#7f8c8d;--c2:#616a6b;--ico:'📋';--card-accent:#95a5a6">
    <div class="acces-card-header">
      <h3><i class="fas fa-clipboard-list"></i> Inventaire Physique</h3>
      <p>Comptage et ajustements</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $pdo->query("SELECT COUNT(*) FROM produits WHERE actif=1")->fetchColumn() ?></div>
      <div class="count-label">produits à inventorier</div>
      <div class="acces-btns">
        <a href="inventaire.php" class="acces-btn acces-btn-add"><i class="fas fa-play"></i> Lancer</a>
      </div>
    </div>
  </a>

  <a href="dlc.php" class="acces-card" style="--c1:#e74c3c;--c2:#c0392b;--ico:'📅';--card-accent:#e74c3c">
    <div class="acces-card-header">
      <h3><i class="fas fa-calendar-times"></i> DLC / DLUO</h3>
      <p>Dates péremption & traçabilité</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $pdo->query("SELECT COUNT(*) FROM dlc_produits WHERE statut IN ('alerte','expire') AND created_at IS NOT NULL")->fetchColumn() ?></div>
      <div class="count-label">lots à surveiller</div>
      <div class="acces-btns">
        <a href="dlc.php" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Nouveau Lot</a>
        <a href="dlc.php?f=alerte" class="acces-btn acces-btn-list"><i class="fas fa-bell"></i> Alertes</a>
      </div>
    </div>
  </a>

</div>

<!-- ════════════════ FINANCES ════════════════ -->
<div class="section-title"><i class="fas fa-coins"></i> Finances</div>
<div class="acces-grid">

  <a href="depenses.php?action=add" class="acces-card" style="--c1:#c0392b;--c2:#922b21;--ico:'💸';--card-accent:#e74c3c">
    <div class="acces-card-header">
      <h3><i class="fas fa-money-bill-wave"></i> Nouvelle Dépense</h3>
      <p>Loyer, salaires, charges...</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= number_format($pdo->query("SELECT COALESCE(SUM(montant),0) FROM depenses WHERE DATE_FORMAT(date_depense,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn()/1000,0) ?>K</div>
      <div class="count-label">FCFA dépensés ce mois</div>
      <div class="acces-btns">
        <a href="depenses.php?action=add" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Ajouter</a>
        <a href="depenses.php" class="acces-btn acces-btn-list"><i class="fas fa-list"></i> Liste</a>
      </div>
    </div>
  </a>

  <a href="rapports.php" class="acces-card" style="--c1:#1a5276;--c2:#154360;--ico:'📊';--card-accent:#2980b9">
    <div class="acces-card-header">
      <h3><i class="fas fa-chart-bar"></i> Rapports & Stats</h3>
      <p>Compte de résultat, exports CSV</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= date('Y') ?></div>
      <div class="count-label">exercice en cours</div>
      <div class="acces-btns">
        <a href="rapports.php" class="acces-btn acces-btn-add"><i class="fas fa-eye"></i> Voir</a>
        <a href="rapports.php?export=csv&etype=ventes&mois=<?= date('Y-m') ?>" class="acces-btn acces-btn-list"><i class="fas fa-download"></i> CSV</a>
      </div>
    </div>
  </a>

  <a href="promotions.php" class="acces-card" style="--c1:#d4ac0d;--c2:#b7950b;--ico:'🏷️';--card-accent:var(--or)">
    <div class="acces-card-header">
      <h3><i class="fas fa-tags"></i> Promotions</h3>
      <p>Offres & programme fidélité</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $pdo->query("SELECT COUNT(*) FROM promotions WHERE date_debut<=CURDATE() AND date_fin>=CURDATE() AND actif=1")->fetchColumn() ?></div>
      <div class="count-label">promo<?= ''>1?'s':'' ?> active<?= ''>1?'s':'' ?></div>
      <div class="acces-btns">
        <a href="promotions.php" class="acces-btn acces-btn-add"><i class="fas fa-plus"></i> Créer Promo</a>
        <a href="promotions.php" class="acces-btn acces-btn-list"><i class="fas fa-star"></i> Fidélité</a>
      </div>
    </div>
  </a>

  <a href="alertes.php" class="acces-card" style="--c1:#8e44ad;--c2:#6c3483;--ico:'🔔';--card-accent:#9b59b6">
    <div class="acces-card-header">
      <h3><i class="fas fa-bell"></i> Alertes</h3>
      <p>Notifications automatiques</p>
    </div>
    <div class="acces-card-body">
      <div class="count"><?= $pdo->query("SELECT COUNT(*) FROM notifications WHERE lu=0")->fetchColumn() ?></div>
      <div class="count-label">notification(s) non lue(s)</div>
      <div class="acces-btns">
        <a href="alertes.php" class="acces-btn acces-btn-add"><i class="fas fa-eye"></i> Voir tout</a>
        <a href="alertes.php?tout_lu=1" class="acces-btn acces-btn-list"><i class="fas fa-check"></i> Tout lire</a>
      </div>
    </div>
  </a>

</div>

<!-- ════════════════ RACCOURCIS CLAVIER ════════════════ -->
<div class="card-omega" style="margin-top:10px">
  <div class="card-head"><h4><i class="fas fa-keyboard"></i> Raccourcis Clavier</h4></div>
  <div class="card-body" style="display:flex;gap:20px;flex-wrap:wrap">
    <?php
    $raccourcis = [
      ['P','Nouveau Produit','produits.php?action=add'],
      ['F','Nouvelle Facture','factures.php?action=new'],
      ['V','Caisse POS','caisse_pos.php'],
      ['A','Approvisionnement','approvisionnement.php'],
      ['C','Nouveau Client','clients.php?action=add'],
      ['D','Nouvelle Dépense','depenses.php?action=add'],
      ['R','Rapports','rapports.php'],
      ['S','Stock','stock.php'],
    ];
    foreach($raccourcis as [$k,$lbl,$url]):
    ?>
    <div style="display:flex;align-items:center;gap:8px;font-size:.82rem;color:var(--muted)">
      <span class="kbd">Alt+<?= $k ?></span> <?= $lbl ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
// Raccourcis clavier Alt+lettre
const shortcuts = {
  'p': 'produits.php?action=add',
  'f': 'factures.php?action=new',
  'v': 'caisse_pos.php',
  'a': 'approvisionnement.php',
  'c': 'clients.php?action=add',
  'd': 'depenses.php?action=add',
  'r': 'rapports.php',
  's': 'stock.php',
};
document.addEventListener('keydown', e => {
  if (e.altKey && shortcuts[e.key.toLowerCase()]) {
    e.preventDefault();
    window.location.href = shortcuts[e.key.toLowerCase()];
  }
});
</script>

<?php require_once 'footer.php'; ?>
