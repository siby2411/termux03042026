<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Catégories';
$pdo = getPDO();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $nb = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE categorie_id=?");
    $nb->execute([$id]);
    if ($nb->fetchColumn() > 0) {
        flash('Impossible : des produits sont liés à cette catégorie.', 'error');
    } else {
        $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
        flash('Catégorie supprimée.', 'success');
    }
    secureRedirect('categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom']);
    $icone = trim($_POST['icone']);
    $couleur = trim($_POST['couleur']);
    $desc  = trim($_POST['description']);
    if (!$nom) { flash('Le nom est requis.', 'error'); secureRedirect('categories.php?action='.($id?'edit':'add').'&id='.$id); }
    if ($id) {
        $pdo->prepare("UPDATE categories SET nom=?,icone=?,couleur=?,description=? WHERE id=?")
            ->execute([$nom, $icone, $couleur, $desc, $id]);
        flash('Catégorie mise à jour.', 'success');
    } else {
        $pdo->prepare("INSERT INTO categories (nom,icone,couleur,description) VALUES (?,?,?,?)")
            ->execute([$nom, $icone, $couleur, $desc]);
        flash('Catégorie ajoutée.', 'success');
    }
    secureRedirect('categories.php');
}

$cat = null;
if (in_array($action, ['edit']) && $id) {
    $s = $pdo->prepare("SELECT * FROM categories WHERE id=?"); $s->execute([$id]); $cat = $s->fetch();
}

$cats = $pdo->query("SELECT c.*,(SELECT COUNT(*) FROM produits WHERE categorie_id=c.id) as nb_produits FROM categories c ORDER BY c.id")->fetchAll();

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-tags" style="color:var(--or)"></i> <span>Catégories</span></h1>
  <p>Organisation du catalogue par famille de produits</p>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="card-omega" style="max-width:600px">
  <div class="card-head">
    <h4><i class="fas fa-<?=$id?'edit':'plus'?>"></i> <?=$id?'Modifier':'Nouvelle'?> Catégorie</h4>
    <a href="categories.php" class="btn-omega btn-omega-outline">← Retour</a>
  </div>
  <div class="card-body">
    <form method="POST" class="form-omega">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Nom *</label>
          <input type="text" name="nom" class="form-control" required placeholder="Ex: Charcuterie Sèche"
            value="<?= htmlspecialchars($cat['nom'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Icône (emoji)</label>
          <input type="text" name="icone" class="form-control" placeholder="🥩" maxlength="5"
            value="<?= htmlspecialchars($cat['icone'] ?? '🥩') ?>" style="font-size:1.3rem;text-align:center">
        </div>
        <div class="col-md-2">
          <label class="form-label">Couleur</label>
          <input type="color" name="couleur" class="form-control" style="height:46px;padding:4px"
            value="<?= htmlspecialchars($cat['couleur'] ?? '#c0392b') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Description de la catégorie..."><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>
        </div>
      </div>
      <div style="margin-top:20px;display:flex;gap:10px">
        <button type="submit" class="btn-omega btn-omega-primary"><i class="fas fa-save"></i> Enregistrer</button>
        <a href="categories.php" class="btn-omega btn-omega-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-list"></i> Liste des Catégories</h4>
    <a href="categories.php?action=add" class="btn-omega btn-omega-primary"><i class="fas fa-plus"></i> Nouvelle catégorie</a>
  </div>
  <div class="row g-3" style="padding:20px">
    <?php foreach($cats as $c): ?>
    <div class="col-lg-4 col-md-6">
      <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:20px;transition:.3s;border-left:4px solid <?= htmlspecialchars($c['couleur']) ?>">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:2rem"><?= $c['icone'] ?></span>
            <div>
              <strong style="color:var(--text)"><?= htmlspecialchars($c['nom']) ?></strong><br>
              <small style="color:var(--muted)"><?= $c['nb_produits'] ?> produit<?=$c['nb_produits']>1?'s':''?></small>
            </div>
          </div>
          <div style="width:14px;height:14px;border-radius:50%;background:<?= htmlspecialchars($c['couleur']) ?>"></div>
        </div>
        <?php if($c['description']): ?>
        <p style="font-size:.8rem;color:#777;margin-bottom:15px;line-height:1.5"><?= htmlspecialchars(mb_substr($c['description'],0,80)) ?>...</p>
        <?php endif; ?>
        <div style="display:flex;gap:8px">
          <a href="categories.php?action=edit&id=<?=$c['id']?>" class="btn-omega btn-omega-gold" style="padding:6px 14px;font-size:.78rem"><i class="fas fa-edit"></i> Modifier</a>
          <a href="categories.php?action=delete&id=<?=$c['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:6px 12px;font-size:.78rem"><i class="fas fa-trash"></i></a>
          <a href="produits.php?cat=<?=$c['id']?>" class="btn-omega btn-omega-outline" style="padding:6px 12px;font-size:.78rem"><i class="fas fa-box"></i> Produits</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
