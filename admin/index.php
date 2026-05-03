<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Tableau de Bord';
$pdo = getPDO();

// ── Statistiques globales ──
$today = date('Y-m-d');
$month = date('Y-m');

$stats = [
  'ventes_today'   => $pdo->query("SELECT COALESCE(SUM(total),0) as v FROM ventes WHERE DATE(date_vente)='$today'")->fetch()['v'],
  'ventes_month'   => $pdo->query("SELECT COALESCE(SUM(total),0) as v FROM ventes WHERE DATE_FORMAT(date_vente,'%Y-%m')='$month'")->fetch()['v'],
  'achats_month'   => $pdo->query("SELECT COALESCE(SUM(total),0) as v FROM approvisionnements WHERE DATE_FORMAT(date_appro,'%Y-%m')='$month'")->fetch()['v'],
  'depenses_month' => $pdo->query("SELECT COALESCE(SUM(montant),0) as v FROM depenses WHERE DATE_FORMAT(date_depense,'%Y-%m')='$month'")->fetch()['v'],
  'nb_clients'     => $pdo->query("SELECT COUNT(*) as c FROM clients")->fetch()['c'],
  'nb_produits'    => $pdo->query("SELECT COUNT(*) as c FROM produits WHERE actif=1")->fetch()['c'],
  'stock_alert'    => $pdo->query("SELECT COUNT(*) as c FROM produits WHERE stock_actuel<=stock_min AND stock_min>0")->fetch()['c'],
  'factures_impayees'=> $pdo->query("SELECT COUNT(*) as c FROM factures WHERE statut='emise'")->fetch()['c'],
];

$benefice = $stats['ventes_month'] - $stats['achats_month'] - $stats['depenses_month'];

// ── Chart ventes 7 derniers jours ──
$ventesChart = [];
for($i=6;$i>=0;$i--){
  $d = date('Y-m-d', strtotime("-$i days"));
  $v = $pdo->query("SELECT COALESCE(SUM(total),0) as s FROM ventes WHERE DATE(date_vente)='$d'")->fetch()['s'];
  $ventesChart[] = ['date'=>date('d/m', strtotime($d)),'val'=>(float)$v];
}

// ── Top 5 produits vendus ──
$topProd = $pdo->query("SELECT p.nom,p.image,SUM(v.quantite) as qte,SUM(v.total) as ca
  FROM ventes v JOIN produits p ON v.produit_id=p.id
  GROUP BY p.id ORDER BY ca DESC LIMIT 5")->fetchAll();

// ── Ventes par catégorie ──
$catVentes = $pdo->query("SELECT c.nom,c.couleur,COALESCE(SUM(v.total),0) as ca
  FROM categories c LEFT JOIN produits p ON p.categorie_id=c.id
  LEFT JOIN ventes v ON v.produit_id=p.id
  GROUP BY c.id ORDER BY ca DESC LIMIT 8")->fetchAll();

// ── Dernières ventes ──
$lastVentes = $pdo->query("SELECT v.*,p.nom as prod_nom,
  COALESCE(CONCAT(cl.prenom,' ',cl.nom),'Client comptoir') as client_nom
  FROM ventes v LEFT JOIN produits p ON v.produit_id=p.id
  LEFT JOIN clients cl ON v.client_id=cl.id
  ORDER BY v.date_vente DESC LIMIT 8")->fetchAll();

// ── Alertes stock ──
$alertes = $pdo->query("SELECT p.*,c.nom as cat_nom FROM produits p
  LEFT JOIN categories c ON p.categorie_id=c.id
  WHERE p.stock_actuel<=p.stock_min AND p.stock_min>0
  ORDER BY (p.stock_actuel/NULLIF(p.stock_min,0)) ASC LIMIT 5")->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fas fa-tachometer-alt" style="color:var(--or)"></i> <span>Tableau de Bord</span></h1>
  <p>Vue d'ensemble de votre activité – <?= date('d F Y') ?></p>
</div>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="stat-card" style="--color1:#c0392b;--color2:#e74c3c">
      <div class="label">Ventes Aujourd'hui</div>
      <div class="value"><?= number_format($stats['ventes_today'],0,',',' ') ?></div>
      <small style="color:var(--muted)">FCFA encaissés</small>
      <div class="icon-bg">💰</div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card" style="--color1:#d4ac0d;--color2:#f1c40f">
      <div class="label">CA du Mois</div>
      <div class="value"><?= number_format($stats['ventes_month'],0,',',' ') ?></div>
      <small style="color:var(--muted)">FCFA ce mois</small>
      <div class="icon-bg">📈</div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card" style="--color1:<?= $benefice>=0?'#27ae60':'#c0392b' ?>;--color2:<?= $benefice>=0?'#2ecc71':'#e74c3c' ?>">
      <div class="label">Bénéfice / Perte du Mois</div>
      <div class="value" style="color:<?= $benefice>=0?'#27ae60':'#e74c3c' ?>"><?= ($benefice>=0?'+':'').number_format($benefice,0,',',' ') ?></div>
      <small style="color:var(--muted)">Ventes – Achats – Dépenses</small>
      <div class="icon-bg"><?= $benefice>=0?'✅':'⚠️' ?></div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card" style="--color1:#2980b9;--color2:#3498db">
      <div class="label">Factures Impayées</div>
      <div class="value" style="color:<?= $stats['factures_impayees']>0?'#e67e22':'#27ae60' ?>"><?= $stats['factures_impayees'] ?></div>
      <small style="color:var(--muted)">en attente de règlement</small>
      <div class="icon-bg">📄</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#8e44ad;--color2:#9b59b6">
      <div class="label">Clients</div><div class="value"><?= $stats['nb_clients'] ?></div>
      <small style="color:var(--muted)">enregistrés</small><div class="icon-bg">👥</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#16a085;--color2:#1abc9c">
      <div class="label">Produits Actifs</div><div class="value"><?= $stats['nb_produits'] ?></div>
      <small style="color:var(--muted)">en catalogue</small><div class="icon-bg">📦</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#e67e22;--color2:#f39c12">
      <div class="label">Dépenses du Mois</div><div class="value"><?= number_format($stats['depenses_month'],0,',',' ') ?></div>
      <small style="color:var(--muted)">FCFA de charges</small><div class="icon-bg">💸</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:<?= $stats['stock_alert']>0?'#c0392b':'#27ae60' ?>;--color2:<?= $stats['stock_alert']>0?'#e74c3c':'#2ecc71' ?>">
      <div class="label">Alertes Stock</div>
      <div class="value" style="color:<?= $stats['stock_alert']>0?'#e74c3c':'#27ae60' ?>"><?= $stats['stock_alert'] ?></div>
      <small style="color:var(--muted)">produits en rupture</small><div class="icon-bg">⚠️</div>
    </div>
  </div>
</div>

<!-- CHARTS ROW -->
<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card-omega">
      <div class="card-head">
        <h4><i class="fas fa-chart-bar"></i> Ventes – 7 derniers jours</h4>
      </div>
      <div class="card-body"><canvas id="chartVentes" height="100"></canvas></div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card-omega">
      <div class="card-head">
        <h4><i class="fas fa-chart-pie"></i> CA par Catégorie</h4>
      </div>
      <div class="card-body"><canvas id="chartCat" height="150"></canvas></div>
    </div>
  </div>
</div>

<!-- TOP PRODUITS + ALERTES STOCK -->
<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="card-omega">
      <div class="card-head">
        <h4><i class="fas fa-trophy"></i> Top 5 Produits Vendus</h4>
        <a href="rapports.php" style="font-size:.75rem;color:var(--or);text-decoration:none">Voir plus →</a>
      </div>
      <div class="card-body" style="padding:0">
        <?php if(empty($topProd)): ?>
          <p style="text-align:center;color:var(--muted);padding:30px">Aucune vente enregistrée</p>
        <?php else: ?>
        <table class="table-omega">
          <thead><tr><th>#</th><th>Produit</th><th>Qté vendue</th><th>CA</th></tr></thead>
          <tbody>
          <?php foreach($topProd as $i=>$p): ?>
          <tr>
            <td>
              <?php $medals=['🥇','🥈','🥉','4.','5.']; echo $medals[$i]??($i+1).'.'; ?>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <?php if($p['image']): ?>
                  <img src="../<?= UPLOAD_URL.htmlspecialchars($p['image']) ?>" class="img-thumb" onerror="this.style.display='none'">
                <?php else: ?><div class="img-placeholder">🥩</div><?php endif; ?>
                <strong><?= htmlspecialchars($p['nom']) ?></strong>
              </div>
            </td>
            <td><?= number_format($p['qte'],2) ?></td>
            <td><strong style="color:var(--or)"><?= number_format($p['ca'],0,',',' ') ?> FCFA</strong></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card-omega">
      <div class="card-head">
        <h4><i class="fas fa-exclamation-triangle" style="color:#e67e22"></i> Alertes Stock</h4>
        <a href="stock.php" style="font-size:.75rem;color:var(--or);text-decoration:none">Gérer →</a>
      </div>
      <div class="card-body" style="padding:0">
        <?php if(empty($alertes)): ?>
          <p style="text-align:center;color:#27ae60;padding:30px">✅ Tous les stocks sont à niveau</p>
        <?php else: ?>
        <table class="table-omega">
          <thead><tr><th>Produit</th><th>Stock</th><th>Min</th><th>Statut</th></tr></thead>
          <tbody>
          <?php foreach($alertes as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['nom']) ?><br><small style="color:var(--muted)"><?= htmlspecialchars($a['cat_nom']??'') ?></small></td>
            <td><strong style="color:<?= $a['stock_actuel']<=0?'#e74c3c':'#e67e22' ?>"><?= number_format($a['stock_actuel'],2) ?> <?= htmlspecialchars($a['unite']) ?></strong></td>
            <td><?= number_format($a['stock_min'],2) ?></td>
            <td>
              <?php if($a['stock_actuel']<=0): ?>
                <span class="badge-stat b-danger">⚠ Rupture</span>
              <?php else: ?>
                <span class="badge-stat b-warning">📉 Faible</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- DERNIÈRES VENTES -->
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-history"></i> Dernières Ventes</h4>
    <a href="ventes.php" style="font-size:.75rem;color:var(--or);text-decoration:none">Voir toutes →</a>
  </div>
  <div class="card-body" style="padding:0;overflow-x:auto">
    <?php if(empty($lastVentes)): ?>
      <p style="text-align:center;color:var(--muted);padding:30px">Aucune vente enregistrée</p>
    <?php else: ?>
    <table class="table-omega">
      <thead><tr><th>Date & Heure</th><th>Produit</th><th>Client</th><th>Qté</th><th>P.U.</th><th>Total</th></tr></thead>
      <tbody>
      <?php foreach($lastVentes as $v): ?>
      <tr>
        <td><small style="color:var(--muted)"><?= date('d/m/Y H:i',strtotime($v['date_vente'])) ?></small></td>
        <td><?= htmlspecialchars($v['prod_nom']??'—') ?></td>
        <td><?= htmlspecialchars($v['client_nom']) ?></td>
        <td><?= number_format($v['quantite'],2) ?></td>
        <td><?= number_format($v['prix_unitaire'],0,',',' ') ?></td>
        <td><strong style="color:var(--or)"><?= number_format($v['total'],0,',',' ') ?> FCFA</strong></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
const chartColors = {
  red:'rgba(192,57,43,.8)',redB:'rgba(192,57,43,1)',
  or:'rgba(212,172,13,.8)',orB:'rgba(212,172,13,1)',
  grid:'rgba(255,255,255,.05)',text:'#888'
};
Chart.defaults.color = chartColors.text;
Chart.defaults.borderColor = chartColors.grid;

// ── Ventes 7 jours ──
new Chart(document.getElementById('chartVentes'),{
  type:'bar',
  data:{
    labels:[<?= implode(',',array_map(fn($d)=>"'".$d['date']."'",$ventesChart)) ?>],
    datasets:[{
      label:'Ventes (FCFA)',
      data:[<?= implode(',',array_map(fn($d)=>$d['val'],$ventesChart)) ?>],
      backgroundColor:'rgba(192,57,43,.7)',
      borderColor:'rgba(192,57,43,1)',
      borderWidth:2,borderRadius:6,
    }]
  },
  options:{responsive:true,plugins:{legend:{display:false}},
    scales:{y:{ticks:{callback:v=>v.toLocaleString()+' F'},grid:{color:chartColors.grid}},
      x:{grid:{display:false}}}}
});

// ── Catégories ──
new Chart(document.getElementById('chartCat'),{
  type:'doughnut',
  data:{
    labels:[<?= implode(',',array_map(fn($c)=>"'".addslashes($c['nom'])."'",$catVentes)) ?>],
    datasets:[{
      data:[<?= implode(',',array_map(fn($c)=>$c['ca'],$catVentes)) ?>],
      backgroundColor:[<?= implode(',',array_map(fn($c)=>"'".($c['couleur']??'#888')."'",$catVentes)) ?>],
      borderWidth:2,borderColor:'#141414'
    }]
  },
  options:{responsive:true,plugins:{legend:{position:'right',labels:{font:{size:11},padding:10}}}}
});
</script>

<?php require_once 'footer.php'; ?>
