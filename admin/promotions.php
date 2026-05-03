<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Promotions & Fidélité';
$pdo = getPDO();

// ── Créer tables si besoin ──
$pdo->exec("CREATE TABLE IF NOT EXISTS promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    type_remise ENUM('pourcentage','montant_fixe') DEFAULT 'pourcentage',
    valeur_remise DECIMAL(10,2) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    produit_id INT,
    categorie_id INT,
    actif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE SET NULL,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS fidelite_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    points INT DEFAULT 0,
    total_achats DECIMAL(12,2) DEFAULT 0,
    niveau ENUM('Bronze','Argent','Or','Platine') DEFAULT 'Bronze',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
)");

-- // Initialiser les points fidélité depuis les ventes
$pdo->exec("INSERT IGNORE INTO fidelite_points (client_id, points, total_achats, niveau)
    SELECT client_id,
        FLOOR(SUM(total)/1000) as points,
        SUM(total) as total_achats,
        CASE
            WHEN SUM(total) >= 2000000 THEN 'Platine'
            WHEN SUM(total) >= 1000000 THEN 'Or'
            WHEN SUM(total) >= 500000  THEN 'Argent'
            ELSE 'Bronze'
        END as niveau
    FROM ventes WHERE client_id IS NOT NULL GROUP BY client_id");

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'delete_promo' && $id) {
    $pdo->prepare("DELETE FROM promotions WHERE id=?")->execute([$id]);
    flash('Promotion supprimée.', 'success'); secureRedirect('promotions.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_promo'])) {
    $pdo->prepare("INSERT INTO promotions (titre,description,type_remise,valeur_remise,date_debut,date_fin,produit_id,categorie_id,actif)
        VALUES (?,?,?,?,?,?,?,?,1)")->execute([
        trim($_POST['titre']), trim($_POST['description']),
        $_POST['type_remise'], (float)$_POST['valeur_remise'],
        $_POST['date_debut'], $_POST['date_fin'],
        $_POST['produit_id'] ?: null, $_POST['categorie_id'] ?: null
    ]);
    flash('Promotion créée avec succès !', 'success'); secureRedirect('promotions.php');
}

$promos = $pdo->query("SELECT p.*,pr.nom as prod_nom,c.nom as cat_nom
    FROM promotions p LEFT JOIN produits pr ON p.produit_id=pr.id
    LEFT JOIN categories c ON p.categorie_id=c.id ORDER BY p.date_fin DESC")->fetchAll();

$fidelite = $pdo->query("SELECT fp.*,c.nom,c.prenom,c.telephone
    FROM fidelite_points fp JOIN clients c ON fp.client_id=c.id
    ORDER BY fp.points DESC")->fetchAll();

$produits = $pdo->query("SELECT * FROM produits WHERE actif=1 ORDER BY nom")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

$today = date('Y-m-d');
$promosActives = array_filter($promos, fn($p) => $p['date_debut'] <= $today && $p['date_fin'] >= $today && $p['actif']);

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-tags" style="color:var(--or)"></i> <span>Promotions & Fidélité</span></h1>
  <p>Gestion des offres promotionnelles et programme de fidélisation clients</p>
</div>

<!-- KPI -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#c0392b;--color2:#e74c3c">
      <div class="label">Promos Actives</div>
      <div class="value"><?= count($promosActives) ?></div>
      <div class="icon-bg">🏷️</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#d4ac0d;--color2:#f1c40f">
      <div class="label">Clients Fidélisés</div>
      <div class="value"><?= count($fidelite) ?></div>
      <div class="icon-bg">⭐</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#27ae60;--color2:#2ecc71">
      <div class="label">Clients Or/Platine</div>
      <div class="value"><?= count(array_filter($fidelite, fn($f) => in_array($f['niveau'],['Or','Platine']))) ?></div>
      <div class="icon-bg">🥇</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card" style="--color1:#8e44ad;--color2:#9b59b6">
      <div class="label">Total Points Cumulés</div>
      <div class="value"><?= number_format(array_sum(array_column($fidelite,'points')),0,',',' ') ?></div>
      <div class="icon-bg">💎</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- CRÉER PROMOTION -->
  <div class="col-lg-4">
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-plus" style="color:#27ae60"></i> Nouvelle Promotion</h4></div>
      <div class="card-body">
        <form method="POST" class="form-omega">
          <div class="mb-3">
            <label class="form-label">Titre</label>
            <input type="text" name="titre" class="form-control" required placeholder="Ex: Promo Tabaski -20%">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2" placeholder="Détails de l'offre..."></textarea>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label">Type de remise</label>
              <select name="type_remise" class="form-select">
                <option value="pourcentage">Pourcentage (%)</option>
                <option value="montant_fixe">Montant fixe (FCFA)</option>
              </select>
            </div>
            <div class="col-5">
              <label class="form-label">Valeur</label>
              <input type="number" name="valeur_remise" class="form-control" min="0" step="0.1" required placeholder="20">
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Du</label>
              <input type="date" name="date_debut" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Au</label>
              <input type="date" name="date_fin" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Appliquer à un produit</label>
            <select name="produit_id" class="form-select">
              <option value="">— Tous les produits —</option>
              <?php foreach($produits as $p): ?>
              <option value="<?=$p['id']?>"><?= htmlspecialchars($p['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Ou à une catégorie</label>
            <select name="categorie_id" class="form-select">
              <option value="">— Toutes catégories —</option>
              <?php foreach($categories as $c): ?>
              <option value="<?=$c['id']?>"><?= $c['icone'].' '.htmlspecialchars($c['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" name="save_promo" class="btn-omega btn-omega-primary w-100">
            <i class="fas fa-save"></i> Créer la Promotion
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <!-- LISTE PROMOTIONS -->
    <div class="card-omega" style="margin-bottom:20px">
      <div class="card-head"><h4><i class="fas fa-list"></i> Promotions en cours et passées</h4></div>
      <div style="overflow-x:auto">
        <table class="table-omega">
          <thead><tr><th>Titre</th><th>Remise</th><th>Période</th><th>Portée</th><th>Statut</th><th></th></tr></thead>
          <tbody>
          <?php if(empty($promos)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">Aucune promotion</td></tr>
          <?php else: foreach($promos as $pr):
            $active = $pr['date_debut'] <= $today && $pr['date_fin'] >= $today && $pr['actif'];
            $expired = $pr['date_fin'] < $today;
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($pr['titre']) ?></strong><br>
              <small style="color:var(--muted)"><?= htmlspecialchars(mb_substr($pr['description']??'',0,50)) ?></small></td>
            <td style="color:var(--or);font-weight:700">
              <?= $pr['type_remise']==='pourcentage'?'-'.$pr['valeur_remise'].'%':'-'.number_format($pr['valeur_remise'],0,',',' ').' F' ?>
            </td>
            <td><small><?= date('d/m/Y',strtotime($pr['date_debut'])) ?><br>→ <?= date('d/m/Y',strtotime($pr['date_fin'])) ?></small></td>
            <td><small><?= $pr['prod_nom']??($pr['cat_nom']??'Tout le catalogue') ?></small></td>
            <td>
              <?php if($active): ?><span class="badge-stat b-success">✅ Active</span>
              <?php elseif($expired): ?><span class="badge-stat b-muted">⏰ Expirée</span>
              <?php else: ?><span class="badge-stat b-info">📅 À venir</span><?php endif; ?>
            </td>
            <td><a href="promotions.php?action=delete_promo&id=<?=$pr['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:4px 8px;font-size:.7rem"><i class="fas fa-trash"></i></a></td>
          </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PROGRAMME FIDÉLITÉ -->
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-star"></i> Programme de Fidélité Clients</h4></div>
      <div style="padding:12px 20px;background:rgba(212,172,13,.05);border-bottom:1px solid var(--border);font-size:.8rem;color:var(--muted)">
        🥉 Bronze : 0-500k FCFA &nbsp;|&nbsp; 🥈 Argent : 500k-1M &nbsp;|&nbsp; 🥇 Or : 1M-2M &nbsp;|&nbsp; 💎 Platine : 2M+
        &nbsp;|&nbsp; <strong style="color:var(--or)">1 000 FCFA = 1 point</strong>
      </div>
      <div style="overflow-x:auto">
        <table class="table-omega">
          <thead><tr><th>Client</th><th>Tél.</th><th>Niveau</th><th>Points</th><th>CA Total</th></tr></thead>
          <tbody>
          <?php
          $niveauStyle=['Bronze'=>['#cd7f32','🥉'],'Argent'=>['#aaa','🥈'],'Or'=>['#d4ac0d','🥇'],'Platine'=>['#7ec8e3','💎']];
          foreach($fidelite as $f):
            [$col,$medal] = $niveauStyle[$f['niveau']] ?? ['#888','⭐'];
          ?>
          <tr>
            <td><strong><?= htmlspecialchars(trim($f['prenom'].' '.$f['nom'])) ?></strong></td>
            <td><small><?= htmlspecialchars($f['telephone']??'—') ?></small></td>
            <td><span style="color:<?=$col?>;font-weight:700"><?=$medal?> <?=$f['niveau']?></span></td>
            <td><strong style="color:var(--or)"><?= number_format($f['points'],0,',',' ') ?> pts</strong></td>
            <td><?= number_format($f['total_achats'],0,',',' ') ?> FCFA</td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
