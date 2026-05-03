<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Alertes & Notifications';
$pdo = getPDO();

$pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('stock','dlc','facture','objectif','systeme') DEFAULT 'systeme',
    titre VARCHAR(200) NOT NULL,
    message TEXT,
    niveau ENUM('info','warning','danger','success') DEFAULT 'info',
    lu TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Générer alertes automatiques
// Stock rupture
$ruptures = $pdo->query("SELECT nom,stock_actuel,unite FROM produits WHERE stock_actuel<=0 AND actif=1")->fetchAll();
foreach ($ruptures as $r) {
    $exists = $pdo->prepare("SELECT id FROM notifications WHERE titre LIKE ? AND DATE(created_at)=CURDATE() AND lu=0");
    $exists->execute(['%'.$r['nom'].'%rupture%']);
    if (!$exists->fetch()) {
        $pdo->prepare("INSERT INTO notifications (type,titre,message,niveau) VALUES ('stock',?,?,'danger')")
            ->execute(['🚨 RUPTURE: '.$r['nom'], 'Stock épuisé ! Stock actuel : '.$r['stock_actuel'].' '.$r['unite']]);
    }
}
// Stock faible
$faibles = $pdo->query("SELECT nom,stock_actuel,stock_min,unite FROM produits WHERE stock_actuel>0 AND stock_actuel<=stock_min AND actif=1")->fetchAll();
foreach ($faibles as $r) {
    $pdo->prepare("INSERT INTO notifications (type,titre,message,niveau) VALUES ('stock',?,?,'warning')")
        ->execute(['⚠️ Stock faible: '.$r['nom'], "Stock : {$r['stock_actuel']} {$r['unite']} (min: {$r['stock_min']})"]);
}
// Factures impayées > 7j
$retards = $pdo->query("SELECT numero,total_ttc,date_facture FROM factures WHERE statut='emise' AND date_facture < DATE_SUB(CURDATE(),INTERVAL 7 DAY)")->fetchAll();
foreach ($retards as $f) {
    $pdo->prepare("INSERT IGNORE INTO notifications (type,titre,message,niveau) VALUES ('facture',?,?,'warning')")
        ->execute(['📄 Facture en retard: '.$f['numero'], 'Échéance dépassée – Montant: '.number_format($f['total_ttc'],0,',',' ').' FCFA']);
}
// Marquer tout lu si demandé
if (isset($_GET['tout_lu'])) {
    $pdo->exec("UPDATE notifications SET lu=1");
    flash('Toutes les notifications marquées comme lues.', 'success');
    secureRedirect('alertes.php');
}
if (isset($_GET['del'])) {
    $pdo->prepare("DELETE FROM notifications WHERE id=?")->execute([(int)$_GET['del']]);
    secureRedirect('alertes.php');
}

$notifs = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 100")->fetchAll();
$nonLues = $pdo->query("SELECT COUNT(*) FROM notifications WHERE lu=0")->fetchColumn();

$niveauIcon = ['info'=>'ℹ️','warning'=>'⚠️','danger'=>'🚨','success'=>'✅'];
$niveauCss  = ['info'=>'b-info','warning'=>'b-warning','danger'=>'b-danger','success'=>'b-success'];
$typeIcon   = ['stock'=>'📦','dlc'=>'📅','facture'=>'📄','objectif'=>'🎯','systeme'=>'⚙️'];

// Stats alertes
$parNiveau = $pdo->query("SELECT niveau,COUNT(*) as nb FROM notifications WHERE lu=0 GROUP BY niveau")->fetchAll();
$statsN = array_column($parNiveau,'nb','niveau');

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-bell" style="color:var(--or)"></i> <span>Alertes & Notifications</span></h1>
  <p><?= $nonLues ?> notification<?= $nonLues>1?'s':'' ?> non lue<?= $nonLues>1?'s':'' ?></p>
</div>

<div class="row g-3 mb-4">
  <?php
  $kpis = [
    ['🚨','danger','Critiques',  $statsN['danger']??0,  '#c0392b','#e74c3c'],
    ['⚠️','warning','Avertissements',$statsN['warning']??0,'#e67e22','#f39c12'],
    ['ℹ️','info','Informations', $statsN['info']??0,    '#2980b9','#3498db'],
    ['✅','success','Succès',    $statsN['success']??0, '#27ae60','#2ecc71'],
  ];
  foreach($kpis as [$ico,$t,$lbl,$nb,$c1,$c2]):
  ?>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:<?=$c1?>;--color2:<?=$c2?>">
      <div class="label"><?=$lbl?></div>
      <div class="value" style="font-size:2rem"><?=$nb?></div>
      <div class="icon-bg"><?=$ico?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-list"></i> Centre de Notifications</h4>
    <div style="display:flex;gap:8px">
      <a href="alertes.php?tout_lu=1" class="btn-omega btn-omega-outline" style="font-size:.78rem;padding:6px 14px">
        <i class="fas fa-check-double"></i> Tout marquer lu
      </a>
      <a href="alertes.php" class="btn-omega btn-omega-gold" style="font-size:.78rem;padding:6px 14px">
        <i class="fas fa-sync"></i> Actualiser
      </a>
    </div>
  </div>
  <div class="card-body" style="padding:0">
    <?php if(empty($notifs)): ?>
      <div style="text-align:center;color:var(--muted);padding:50px">
        <div style="font-size:3rem;margin-bottom:15px">🔔</div>
        <p>Aucune notification — Tout est en ordre !</p>
      </div>
    <?php else: foreach($notifs as $n):
      $css = ['info'=>'rgba(41,128,185,.08)','warning'=>'rgba(230,126,34,.08)','danger'=>'rgba(192,57,43,.08)','success'=>'rgba(39,174,96,.08)'][$n['niveau']];
      $border = ['info'=>'#2980b9','warning'=>'#e67e22','danger'=>'#c0392b','success'=>'#27ae60'][$n['niveau']];
    ?>
    <div style="padding:15px 20px;border-bottom:1px solid var(--border);
      background:<?= $n['lu']?'transparent':$css ?>;
      border-left:4px solid <?= $n['lu']?'transparent':$border ?>;
      display:flex;align-items:flex-start;gap:15px;transition:.3s">
      <span style="font-size:1.5rem;flex-shrink:0"><?= $typeIcon[$n['type']]??'🔔' ?></span>
      <div style="flex:1">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:5px">
          <strong style="color:<?= $n['lu']?'var(--muted)':'var(--text)' ?>;font-size:.9rem"><?= htmlspecialchars($n['titre']) ?></strong>
          <?php if(!$n['lu']): ?><span style="width:8px;height:8px;border-radius:50%;background:<?=$border?>;flex-shrink:0;display:inline-block"></span><?php endif; ?>
          <span class="badge-stat <?= $niveauCss[$n['niveau']] ?>" style="font-size:.65rem"><?= $n['niveau'] ?></span>
        </div>
        <?php if($n['message']): ?>
        <p style="color:var(--muted);font-size:.82rem;margin:0"><?= htmlspecialchars($n['message']) ?></p>
        <?php endif; ?>
        <small style="color:#555"><?= date('d/m/Y H:i',strtotime($n['created_at'])) ?></small>
      </div>
      <a href="alertes.php?del=<?=$n['id']?>" style="color:#555;text-decoration:none;font-size:1rem;flex-shrink:0;padding:4px 8px;border-radius:6px;transition:.2s" title="Supprimer">✕</a>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php require_once 'footer.php'; ?>
