<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Rapports & Statistiques';
$pdo = getPDO();

$annee  = (int)($_GET['annee'] ?? date('Y'));
$mois   = $_GET['mois'] ?? date('Y-m');
$type   = $_GET['type'] ?? 'synthese';

// ═══════════════════════════════════════════════════════
// EXPORT CSV
// ═══════════════════════════════════════════════════════
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exportType = $_GET['etype'] ?? 'ventes';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="omega_'.$exportType.'_'.$mois.'.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    if ($exportType === 'ventes') {
        fputcsv($out, ['Date','Produit','Client','Quantité','Prix Unitaire','Total (FCFA)'], ';');
        $rows = $pdo->prepare("SELECT v.date_vente,p.nom,COALESCE(CONCAT(c.prenom,' ',c.nom),'Comptoir'),v.quantite,v.prix_unitaire,v.total
            FROM ventes v LEFT JOIN produits p ON v.produit_id=p.id LEFT JOIN clients c ON v.client_id=c.id
            WHERE DATE_FORMAT(v.date_vente,'%Y-%m')=? ORDER BY v.date_vente DESC");
        $rows->execute([$mois]);
        foreach ($rows->fetchAll() as $r) fputcsv($out, $r, ';');
    } elseif ($exportType === 'approvisionnements') {
        fputcsv($out, ['Date','Produit','Fournisseur','Quantité','Prix Unitaire','Total (FCFA)','Référence'], ';');
        $rows = $pdo->prepare("SELECT a.date_appro,p.nom,f.nom,a.quantite,a.prix_unitaire,a.total,a.reference
            FROM approvisionnements a LEFT JOIN produits p ON a.produit_id=p.id LEFT JOIN fournisseurs f ON a.fournisseur_id=f.id
            WHERE DATE_FORMAT(a.date_appro,'%Y-%m')=? ORDER BY a.date_appro DESC");
        $rows->execute([$mois]);
        foreach ($rows->fetchAll() as $r) fputcsv($out, $r, ';');
    } elseif ($exportType === 'depenses') {
        fputcsv($out, ['Date','Libellé','Catégorie','Montant (FCFA)','Description'], ';');
        $rows = $pdo->prepare("SELECT date_depense,libelle,categorie,montant,description FROM depenses
            WHERE DATE_FORMAT(date_depense,'%Y-%m')=? ORDER BY date_depense DESC");
        $rows->execute([$mois]);
        foreach ($rows->fetchAll() as $r) fputcsv($out, $r, ';');
    } elseif ($exportType === 'stock') {
        fputcsv($out, ['Produit','Catégorie','Stock Actuel','Stock Min','Unité','Prix Achat','Prix Vente','Valeur Stock','Statut'], ';');
        $rows = $pdo->query("SELECT p.nom,c.nom,p.stock_actuel,p.stock_min,p.unite,p.prix_achat,p.prix_vente,
            (p.stock_actuel*p.prix_achat) as valeur,
            IF(p.stock_actuel<=0,'RUPTURE',IF(p.stock_actuel<=p.stock_min,'FAIBLE','OK'))
            FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id ORDER BY p.nom")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r, ';');
    }
    fclose($out); exit;
}

// ═══════════════════════════════════════════════════════
// DONNÉES SYNTHÈSE ANNUELLE
// ═══════════════════════════════════════════════════════
$ventesAn   = $pdo->prepare("SELECT COALESCE(SUM(total),0) as v FROM ventes WHERE YEAR(date_vente)=?"); $ventesAn->execute([$annee]);
$achatsAn   = $pdo->prepare("SELECT COALESCE(SUM(total),0) as v FROM approvisionnements WHERE YEAR(date_appro)=?"); $achatsAn->execute([$annee]);
$depensesAn = $pdo->prepare("SELECT COALESCE(SUM(montant),0) as v FROM depenses WHERE YEAR(date_depense)=?"); $depensesAn->execute([$annee]);

$totVentes   = (float)$ventesAn->fetch()['v'];
$totAchats   = (float)$achatsAn->fetch()['v'];
$totDepenses = (float)$depensesAn->fetch()['v'];
$beneficeNet = $totVentes - $totAchats - $totDepenses;
$margeGrossiere = $totAchats > 0 ? (($totVentes - $totAchats) / $totVentes * 100) : 0;

// Données mensuelles pour graphiques (12 mois de l'année)
$chartMois = []; $labMois = [];
for ($m = 1; $m <= 12; $m++) {
    $mm = sprintf('%04d-%02d', $annee, $m);
    $labMois[] = date('M', mktime(0,0,0,$m,1,$annee));
    $sv = $pdo->prepare("SELECT COALESCE(SUM(total),0) as v FROM ventes WHERE DATE_FORMAT(date_vente,'%Y-%m')=?"); $sv->execute([$mm]);
    $sa = $pdo->prepare("SELECT COALESCE(SUM(total),0) as v FROM approvisionnements WHERE DATE_FORMAT(date_appro,'%Y-%m')=?"); $sa->execute([$mm]);
    $sd = $pdo->prepare("SELECT COALESCE(SUM(montant),0) as v FROM depenses WHERE DATE_FORMAT(date_depense,'%Y-%m')=?"); $sd->execute([$mm]);
    $va = (float)$sv->fetch()['v'];
    $aa = (float)$sa->fetch()['v'];
    $da = (float)$sd->fetch()['v'];
    $chartMois[] = ['ventes'=>$va,'achats'=>$aa,'depenses'=>$da,'benefice'=>$va-$aa-$da];
}

// Top produits vendus (année)
$topProd = $pdo->prepare("SELECT p.nom,p.image,c.nom as cat_nom,c.couleur,
    SUM(v.quantite) as qte_totale,SUM(v.total) as ca,COUNT(*) as nb_trans,
    AVG(v.prix_unitaire) as prix_moyen
    FROM ventes v JOIN produits p ON v.produit_id=p.id LEFT JOIN categories c ON p.categorie_id=c.id
    WHERE YEAR(v.date_vente)=? GROUP BY p.id ORDER BY ca DESC LIMIT 10");
$topProd->execute([$annee]); $topProd = $topProd->fetchAll();

// Ventes par catégorie (mois courant)
$ventesParCat = $pdo->prepare("SELECT c.nom,c.couleur,COALESCE(SUM(v.total),0) as ca,COUNT(v.id) as nb
    FROM categories c LEFT JOIN produits p ON p.categorie_id=c.id
    LEFT JOIN ventes v ON v.produit_id=p.id AND DATE_FORMAT(v.date_vente,'%Y-%m')=?
    GROUP BY c.id ORDER BY ca DESC");
$ventesParCat->execute([$mois]); $ventesParCat = $ventesParCat->fetchAll();

// Top clients
$topClients = $pdo->prepare("SELECT COALESCE(CONCAT(c.prenom,' ',c.nom),'Comptoir') as nom,
    COUNT(v.id) as nb_achats,SUM(v.total) as ca_total
    FROM ventes v LEFT JOIN clients c ON v.client_id=c.id
    WHERE YEAR(v.date_vente)=? GROUP BY v.client_id ORDER BY ca_total DESC LIMIT 5");
$topClients->execute([$annee]); $topClients = $topClients->fetchAll();

// Évolution stock (produits > 0)
$stockVal = $pdo->query("SELECT COALESCE(SUM(stock_actuel*prix_achat),0) as val,
    COALESCE(SUM(stock_actuel*prix_vente),0) as val_vente FROM produits WHERE actif=1")->fetch();

// Dépenses par catégorie (année)
$depParCat = $pdo->prepare("SELECT categorie,SUM(montant) as tot FROM depenses WHERE YEAR(date_depense)=? GROUP BY categorie ORDER BY tot DESC");
$depParCat->execute([$annee]); $depParCat = $depParCat->fetchAll();

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-chart-line" style="color:var(--or)"></i> <span>Rapports & Statistiques</span></h1>
  <p>Analyse complète de l'activité – Exercice <?= $annee ?></p>
</div>

<!-- FILTRES GLOBAUX -->
<div class="card-omega" style="margin-bottom:20px">
  <div class="card-body" style="padding:15px">
    <form method="GET" class="form-omega" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div>
        <label class="form-label">Année</label>
        <select name="annee" class="form-select" style="width:100px">
          <?php for($y=date('Y');$y>=2020;$y--): ?>
          <option <?=$annee==$y?'selected':''?>><?=$y?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div>
        <label class="form-label">Mois (exports)</label>
        <input type="month" name="mois" class="form-control" style="width:160px" value="<?=$mois?>">
      </div>
      <div style="align-self:flex-end"><button type="submit" class="btn-omega btn-omega-gold"><i class="fas fa-sync"></i> Actualiser</button></div>
      <div style="align-self:flex-end;margin-left:auto;display:flex;gap:8px;flex-wrap:wrap">
        <span style="color:var(--muted);font-size:.78rem;align-self:center">📥 Exporter <?=$mois?> :</span>
        <a href="rapports.php?mois=<?=$mois?>&annee=<?=$annee?>&export=csv&etype=ventes" class="btn-omega btn-omega-success" style="padding:7px 14px;font-size:.78rem"><i class="fas fa-file-csv"></i> Ventes CSV</a>
        <a href="rapports.php?mois=<?=$mois?>&annee=<?=$annee?>&export=csv&etype=approvisionnements" class="btn-omega btn-omega-outline" style="padding:7px 14px;font-size:.78rem"><i class="fas fa-file-csv"></i> Appros CSV</a>
        <a href="rapports.php?mois=<?=$mois?>&annee=<?=$annee?>&export=csv&etype=depenses" class="btn-omega btn-omega-outline" style="padding:7px 14px;font-size:.78rem"><i class="fas fa-file-csv"></i> Dépenses CSV</a>
        <a href="rapports.php?mois=<?=$mois?>&annee=<?=$annee?>&export=csv&etype=stock" class="btn-omega btn-omega-outline" style="padding:7px 14px;font-size:.78rem"><i class="fas fa-file-csv"></i> Stock CSV</a>
        <button onclick="window.print()" type="button" class="btn-omega btn-omega-gold" style="padding:7px 14px;font-size:.78rem"><i class="fas fa-print"></i> Imprimer</button>
      </div>
    </form>
  </div>
</div>

<!-- KPI ANNUELS -->
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="stat-card" style="--color1:#c0392b;--color2:#e74c3c">
      <div class="label">CA Annuel <?=$annee?></div>
      <div class="value" style="font-size:1.4rem"><?= number_format($totVentes,0,',',' ') ?></div>
      <small style="color:var(--muted)">FCFA de chiffre d'affaires</small>
      <div class="icon-bg">💰</div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card" style="--color1:#2980b9;--color2:#3498db">
      <div class="label">Coût des Achats</div>
      <div class="value" style="font-size:1.4rem"><?= number_format($totAchats,0,',',' ') ?></div>
      <small style="color:var(--muted)">FCFA (approvisionnements)</small>
      <div class="icon-bg">🏭</div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card" style="--color1:#e67e22;--color2:#f39c12">
      <div class="label">Charges & Dépenses</div>
      <div class="value" style="font-size:1.4rem"><?= number_format($totDepenses,0,',',' ') ?></div>
      <small style="color:var(--muted)">FCFA de charges totales</small>
      <div class="icon-bg">💸</div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card" style="--color1:<?=$beneficeNet>=0?'#27ae60':'#c0392b'?>;--color2:<?=$beneficeNet>=0?'#2ecc71':'#e74c3c'?>">
      <div class="label">Bénéfice Net <?=$annee?></div>
      <div class="value" style="font-size:1.4rem;color:<?=$beneficeNet>=0?'#2ecc71':'#e74c3c'?>"><?=($beneficeNet>=0?'+':'').number_format($beneficeNet,0,',',' ')?></div>
      <small style="color:var(--muted)">Marge brute : <?= number_format($margeGrossiere,1) ?>%</small>
      <div class="icon-bg"><?=$beneficeNet>=0?'📈':'📉'?></div>
    </div>
  </div>
</div>

<!-- COMPTE DE RÉSULTAT -->
<div class="row g-3 mb-4">
  <div class="col-md-5">
    <div class="card-omega h-100">
      <div class="card-head"><h4><i class="fas fa-balance-scale"></i> Compte de Résultat – <?=$annee?></h4></div>
      <div class="card-body">
        <?php
        $items = [
          ['+ Chiffre d\'Affaires',  $totVentes,   '#27ae60', true],
          ['− Coût d\'Achats',       $totAchats,   '#e74c3c', false],
          ['= Marge Brute',          $totVentes-$totAchats, $totVentes-$totAchats>=0?'#f1c40f':'#e74c3c', true],
          ['− Charges Fixes',        $totDepenses, '#e67e22', false],
          ['= Résultat Net',         $beneficeNet, $beneficeNet>=0?'#27ae60':'#e74c3c', true],
        ];
        foreach($items as [$lbl,$val,$col,$bold]):
        ?>
        <div style="display:flex;justify-content:space-between;align-items:center;
          padding:10px 0;border-bottom:1px solid var(--border);
          <?=$bold?'font-weight:700':''?>">
          <span style="color:<?=$bold?$col:'var(--muted)'?>"><?=$lbl?></span>
          <strong style="color:<?=$col?>;font-size:<?=$bold?'1.05rem':'.9rem'?>"><?= number_format(abs($val),0,',',' ') ?> FCFA</strong>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:15px;padding:12px;border-radius:10px;
          background:<?=$beneficeNet>=0?'rgba(39,174,96,.1)':'rgba(192,57,43,.1)'?>;
          border:1px solid <?=$beneficeNet>=0?'rgba(39,174,96,.3)':'rgba(192,57,43,.3)'?>;
          text-align:center">
          <div style="font-size:.75rem;color:var(--muted)">RÉSULTAT FINAL</div>
          <div style="font-size:1.6rem;font-weight:900;color:<?=$beneficeNet>=0?'#27ae60':'#e74c3c'?>;font-family:'Playfair Display',serif">
            <?=($beneficeNet>=0?'✅ Bénéfice':'❌ Déficit')?><br>
            <?=($beneficeNet>=0?'+':'').number_format($beneficeNet,0,',',' ')?> FCFA
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card-omega h-100">
      <div class="card-head"><h4><i class="fas fa-chart-bar"></i> Évolution Mensuelle <?=$annee?></h4></div>
      <div class="card-body"><canvas id="chartAnnuel" height="180"></canvas></div>
    </div>
  </div>
</div>

<!-- GRAPHIQUES VENTES/CATEGORIES + BÉNÉFICE -->
<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-chart-pie"></i> Ventes par Catégorie – <?= date('F Y',strtotime($mois.'-01')) ?></h4></div>
      <div class="card-body"><canvas id="chartCatVentes" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-chart-area"></i> Bénéfice Mensuel <?=$annee?></h4></div>
      <div class="card-body"><canvas id="chartBenef" height="200"></canvas></div>
    </div>
  </div>
</div>

<!-- TOP PRODUITS + TOP CLIENTS -->
<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card-omega">
      <div class="card-head">
        <h4><i class="fas fa-trophy"></i> 🏆 Top 10 Produits – <?=$annee?></h4>
      </div>
      <div style="overflow-x:auto">
        <table class="table-omega">
          <thead><tr><th>#</th><th>Produit</th><th>Catégorie</th><th>Qté vendue</th><th>Transactions</th><th>CA (FCFA)</th><th>%</th></tr></thead>
          <tbody>
            <?php if(empty($topProd)): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">Aucune vente cette année</td></tr>
            <?php else:
              $maxCA = $topProd[0]['ca'];
              foreach($topProd as $i=>$p):
                $pct = $maxCA > 0 ? ($p['ca']/$maxCA*100) : 0;
                $medals = ['🥇','🥈','🥉'];
            ?>
            <tr>
              <td style="font-size:1.1rem"><?= $medals[$i] ?? ($i+1).'' ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <?php if($p['image']): ?>
                    <img src="../<?= UPLOAD_URL.htmlspecialchars($p['image']) ?>" class="img-thumb" style="width:36px;height:36px" onerror="this.style.display='none'">
                  <?php else: ?><div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.05);display:flex;align-items:center;justify-content:center;font-size:1.1rem">🥩</div><?php endif; ?>
                  <strong style="font-size:.85rem"><?= htmlspecialchars($p['nom']) ?></strong>
                </div>
              </td>
              <td><small style="color:<?= htmlspecialchars($p['couleur']??'#888') ?>"><?= htmlspecialchars($p['cat_nom']??'—') ?></small></td>
              <td><?= number_format($p['qte_totale'],2) ?></td>
              <td><?= $p['nb_trans'] ?></td>
              <td>
                <strong style="color:var(--or)"><?= number_format($p['ca'],0,',',' ') ?></strong>
                <div style="background:rgba(255,255,255,.06);border-radius:4px;height:4px;margin-top:4px">
                  <div style="height:4px;width:<?=$pct?>%;background:linear-gradient(90deg,var(--rouge),var(--or));border-radius:4px"></div>
                </div>
              </td>
              <td><small style="color:var(--muted)"><?= $totVentes>0?number_format($p['ca']/$totVentes*100,1):0 ?>%</small></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card-omega" style="margin-bottom:20px">
      <div class="card-head"><h4><i class="fas fa-users"></i> Top 5 Clients – <?=$annee?></h4></div>
      <div class="card-body" style="padding:0">
        <table class="table-omega">
          <thead><tr><th>#</th><th>Client</th><th>Achats</th><th>CA</th></tr></thead>
          <tbody>
            <?php if(empty($topClients)): ?>
              <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:20px">Aucune donnée</td></tr>
            <?php else: foreach($topClients as $i=>$c): ?>
            <tr>
              <td><?= ['🥇','🥈','🥉','4.','5.'][$i] ?? $i+1 ?></td>
              <td><strong style="font-size:.85rem"><?= htmlspecialchars($c['nom']) ?></strong></td>
              <td><span class="badge-stat b-info"><?=$c['nb_achats']?></span></td>
              <td><strong style="color:var(--or);font-size:.85rem"><?= number_format($c['ca_total'],0,',',' ') ?></strong></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- VALEUR STOCK -->
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-warehouse"></i> Valeur du Stock Actuel</h4></div>
      <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:12px">
          <div style="background:rgba(41,128,185,.1);border:1px solid rgba(41,128,185,.2);border-radius:10px;padding:15px;text-align:center">
            <div style="font-size:.75rem;color:var(--muted)">VALEUR AU PRIX D'ACHAT</div>
            <div style="font-size:1.4rem;font-weight:900;color:#3498db"><?= number_format($stockVal['val'],0,',',' ') ?> FCFA</div>
          </div>
          <div style="background:rgba(39,174,96,.1);border:1px solid rgba(39,174,96,.2);border-radius:10px;padding:15px;text-align:center">
            <div style="font-size:.75rem;color:var(--muted)">VALEUR AU PRIX DE VENTE</div>
            <div style="font-size:1.4rem;font-weight:900;color:#27ae60"><?= number_format($stockVal['val_vente'],0,',',' ') ?> FCFA</div>
          </div>
          <div style="background:rgba(212,172,13,.1);border:1px solid rgba(212,172,13,.2);border-radius:10px;padding:15px;text-align:center">
            <div style="font-size:.75rem;color:var(--muted)">PLUS-VALUE POTENTIELLE</div>
            <div style="font-size:1.4rem;font-weight:900;color:var(--or)"><?= number_format($stockVal['val_vente']-$stockVal['val'],0,',',' ') ?> FCFA</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RÉPARTITION DÉPENSES ANNUELLES -->
<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-chart-doughnut"></i> Répartition Dépenses <?=$annee?></h4></div>
      <div class="card-body"><canvas id="chartDepAnnuel" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-table"></i> Détail Dépenses par Catégorie</h4></div>
      <div class="card-body" style="padding:0">
        <table class="table-omega">
          <thead><tr><th>Catégorie</th><th>Total</th><th>%</th></tr></thead>
          <tbody>
          <?php
          $catDepLabels=['salaire'=>'💼 Salaires','loyer'=>'🏠 Loyer','electricite'=>'⚡ Électricité','eau'=>'💧 Eau','transport'=>'🚗 Transport','emballage'=>'📦 Emballages','maintenance'=>'🔧 Maintenance','publicite'=>'📢 Publicité','divers'=>'🔹 Divers'];
          $catDepColors=['salaire'=>'#9b59b6','loyer'=>'#e67e22','electricite'=>'#f1c40f','eau'=>'#3498db','transport'=>'#27ae60','emballage'=>'#95a5a6','maintenance'=>'#e74c3c','publicite'=>'#1abc9c','divers'=>'#7f8c8d'];
          foreach($depParCat as $d):
            $pct = $totDepenses > 0 ? ($d['tot']/$totDepenses*100) : 0;
          ?>
          <tr>
            <td><?= $catDepLabels[$d['categorie']] ?? $d['categorie'] ?></td>
            <td><strong style="color:#e74c3c"><?= number_format($d['tot'],0,',',' ') ?> FCFA</strong></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="background:rgba(255,255,255,.06);border-radius:4px;height:6px;flex:1">
                  <div style="height:6px;width:<?=$pct?>%;background:<?=$catDepColors[$d['categorie']]??'#888'?>;border-radius:4px"></div>
                </div>
                <small style="color:var(--muted);width:35px"><?= number_format($pct,1) ?>%</small>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
Chart.defaults.color='#888'; Chart.defaults.borderColor='rgba(255,255,255,.05)';
const fn = v=>v.toLocaleString('fr')+' F';

// ── Évolution mensuelle ──
new Chart(document.getElementById('chartAnnuel'),{
  type:'bar',
  data:{
    labels:[<?= implode(',',array_map(fn($l)=>"'$l'",$labMois)) ?>],
    datasets:[
      {label:'Ventes',data:[<?= implode(',',array_map(fn($m)=>$m['ventes'],$chartMois)) ?>],backgroundColor:'rgba(192,57,43,.7)',borderColor:'#c0392b',borderWidth:1,borderRadius:4},
      {label:'Achats',data:[<?= implode(',',array_map(fn($m)=>$m['achats'],$chartMois)) ?>],backgroundColor:'rgba(41,128,185,.7)',borderColor:'#2980b9',borderWidth:1,borderRadius:4},
      {label:'Dépenses',data:[<?= implode(',',array_map(fn($m)=>$m['depenses'],$chartMois)) ?>],backgroundColor:'rgba(230,126,34,.7)',borderColor:'#e67e22',borderWidth:1,borderRadius:4},
    ]
  },
  options:{responsive:true,plugins:{legend:{position:'top',labels:{color:'#aaa',font:{size:11}}}},
    scales:{y:{ticks:{callback:fn,color:'#888'},grid:{color:'rgba(255,255,255,.04)'}},x:{grid:{display:false},ticks:{color:'#888'}}}}
});

// ── Bénéfice mensuel ──
const benefData=[<?= implode(',',array_map(fn($m)=>$m['benefice'],$chartMois)) ?>];
new Chart(document.getElementById('chartBenef'),{
  type:'line',
  data:{
    labels:[<?= implode(',',array_map(fn($l)=>"'$l'",$labMois)) ?>],
    datasets:[{
      label:'Bénéfice Net',data:benefData,
      borderColor:'#27ae60',backgroundColor:'rgba(39,174,96,.1)',
      borderWidth:2,fill:true,tension:.4,pointRadius:4,
      pointBackgroundColor: benefData.map(v=>v>=0?'#27ae60':'#e74c3c'),
      segment:{borderColor:ctx=>ctx.p1.parsed.y<0?'rgba(231,76,60,.9)':'rgba(39,174,96,.9)'}
    }]
  },
  options:{responsive:true,plugins:{legend:{display:false}},
    scales:{y:{ticks:{callback:fn,color:'#888'},grid:{color:'rgba(255,255,255,.04)'},
      afterBuildTicks:axis=>{axis.chart.data.datasets[0].data.forEach(v=>v<0&&axis.ticks.push({value:0}))}},
      x:{grid:{display:false},ticks:{color:'#888'}}}}
});

// ── Catégories ventes ──
const catVentesData={
  labels:[<?= implode(',',array_map(fn($c)=>"'".addslashes($c['nom'])."'",$ventesParCat)) ?>],
  datasets:[{
    data:[<?= implode(',',array_map(fn($c)=>$c['ca'],$ventesParCat)) ?>],
    backgroundColor:[<?= implode(',',array_map(fn($c)=>"'".($c['couleur']??'#888')."'",$ventesParCat)) ?>],
    borderWidth:2,borderColor:'#1a1a1a'
  }]
};
new Chart(document.getElementById('chartCatVentes'),{type:'doughnut',data:catVentesData,
  options:{responsive:true,plugins:{legend:{position:'right',labels:{color:'#aaa',font:{size:11}}}}}});

// ── Dépenses annuelles ──
new Chart(document.getElementById('chartDepAnnuel'),{
  type:'pie',
  data:{
    labels:[<?= implode(',',array_map(fn($d)=>"'".addslashes($catDepLabels[$d['categorie']]??$d['categorie'])."'",$depParCat)) ?>],
    datasets:[{
      data:[<?= implode(',',array_column($depParCat,'tot')) ?>],
      backgroundColor:[<?= implode(',',array_map(fn($d)=>"'".($catDepColors[$d['categorie']]??'#888')."'",$depParCat)) ?>],
      borderWidth:2,borderColor:'#1a1a1a'
    }]
  },
  options:{responsive:true,plugins:{legend:{position:'right',labels:{color:'#aaa',font:{size:10},padding:8}}}}
});
</script>

<style>
@media print {
  .sidebar,.admin-topbar,.card-head a,.btn-omega,form{display:none!important}
  .admin-main{margin:0!important}
  body{background:#fff!important;color:#000!important}
  .stat-card,.card-omega{background:#fff!important;border:1px solid #ddd!important;page-break-inside:avoid}
  .stat-card .value,.stat-card .label{color:#000!important}
}
</style>

<?php require_once 'footer.php'; ?>
