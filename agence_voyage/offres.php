<?php
require 'config/db.php';
$page_title = 'Offres Promotionnelles';
require 'includes/header.php';

// Ajouter offre
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['titre'])) { $error = 'Titre obligatoire.'; }
    else {
        try {
            $pdo->prepare("INSERT INTO offres_promotionnelles (titre,description,destination_id,prix_promo,prix_original,date_debut,date_fin,actif)
            VALUES(:t,:d,:dest,:pp,:po,:db,:df,1)")->execute([
                ':t'=>$f['titre'],':d'=>$f['description']??null,
                ':dest'=>$f['destination_id']??null,
                ':pp'=>(float)($f['prix_promo']??0),':po'=>(float)($f['prix_original']??0),
                ':db'=>$f['date_debut']??null,':df'=>$f['date_fin']??null,
            ]);
            $success = 'Offre créée avec succès.';
        } catch(PDOException $e){ $error='Erreur: '.$e->getMessage(); }
    }
}

$offres = $pdo->query("SELECT o.*, d.ville, d.code_iata, d.continent
    FROM offres_promotionnelles o LEFT JOIN destinations d ON o.destination_id=d.id
    WHERE o.actif=1 ORDER BY o.id DESC")->fetchAll();
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY ville")->fetchAll();

$emojis_continent = ['Afrique'=>'🌍','Europe'=>'🏰','Asie'=>'🌏','Amérique'=>'🗽'];
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Marketing</div>
      <h1>Offres Promotionnelles</h1>
      <p><?= count($offres) ?> offre(s) active(s)</p>
    </div>
  </div>

  <?php if($success) echo "<div class='alert alert-success'>✓ $success</div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <div class="grid-2" style="gap:24px;align-items:start">

    <!-- Grille des offres -->
    <div>
      <div class="grid-2" style="gap:14px">
        <?php if(empty($offres)): ?>
        <div class="card" style="grid-column:span 2"><div class="empty-state"><div class="empty-icon">🏷</div>Aucune offre active</div></div>
        <?php else: foreach($offres as $o):
          $emoji = $emojis_continent[$o['continent']??''] ?? '✈';
          $pct = $o['prix_original'] > 0 ? round((1 - $o['prix_promo']/$o['prix_original'])*100) : 0;
        ?>
        <div class="offer-card">
          <div class="offer-card-img"><?= $emoji ?></div>
          <div class="offer-card-body">
            <div style="font-weight:700;font-size:0.88rem;color:var(--text)"><?= htmlspecialchars($o['titre']) ?></div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:3px">
              📍 <?= htmlspecialchars($o['ville']??'Toutes destinations') ?>
            </div>
            <?php if($o['description']): ?>
            <div style="font-size:0.72rem;color:var(--muted);margin-top:6px;line-height:1.5"><?= htmlspecialchars(mb_substr($o['description'],0,70)).'…' ?></div>
            <?php endif; ?>
            <div class="offer-price-row">
              <span class="offer-price"><?= money($o['prix_promo']) ?></span>
              <?php if($o['prix_original']): ?>
              <span class="offer-price-old"><?= money($o['prix_original']) ?></span>
              <?php if($pct > 0): ?><span class="offer-discount">-<?= $pct ?>%</span><?php endif; ?>
              <?php endif; ?>
            </div>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:6px">
              Du <?= $o['date_debut'] ? date('d/m/Y',strtotime($o['date_debut'])) : '?' ?>
              au <?= $o['date_fin'] ? date('d/m/Y',strtotime($o['date_fin'])) : '?' ?>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Formulaire ajout -->
    <div>
      <div class="card">
        <div class="card-header"><span class="card-title">+ Nouvelle Offre</span></div>
        <div class="card-body">
          <form method="POST">
            <div class="form-grid" style="gap:14px">
              <div class="form-group">
                <label class="form-label">Titre <span class="req">*</span></label>
                <input type="text" name="titre" class="form-control" placeholder="Ex: Paris Printemps 2026" value="<?= htmlspecialchars($_POST['titre']??'') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Destination</label>
                <select name="destination_id" class="form-control">
                  <option value="">— Toutes destinations —</option>
                  <?php foreach($destinations as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= (($_POST['destination_id']??'')==$d['id'])?'selected':'' ?>>
                    [<?= $d['code_iata'] ?>] <?= htmlspecialchars($d['ville']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-grid form-grid-2">
                <div class="form-group">
                  <label class="form-label">Prix promo (FCFA)</label>
                  <input type="number" name="prix_promo" class="form-control" value="<?= htmlspecialchars($_POST['prix_promo']??'') ?>" step="1000" min="0">
                </div>
                <div class="form-group">
                  <label class="form-label">Prix original (FCFA)</label>
                  <input type="number" name="prix_original" class="form-control" value="<?= htmlspecialchars($_POST['prix_original']??'') ?>" step="1000" min="0">
                </div>
              </div>
              <div class="form-grid form-grid-2">
                <div class="form-group">
                  <label class="form-label">Date début</label>
                  <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($_POST['date_debut']??'') ?>">
                </div>
                <div class="form-group">
                  <label class="form-label">Date fin</label>
                  <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($_POST['date_fin']??'') ?>">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Description de l'offre…"><?= htmlspecialchars($_POST['description']??'') ?></textarea>
              </div>
              <button type="submit" class="btn btn-gold" style="width:100%">🏷 Créer l'offre</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
