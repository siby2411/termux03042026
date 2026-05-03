<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Fournisseurs';
$pdo = getPDO();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $pdo->prepare("UPDATE approvisionnements SET fournisseur_id=NULL WHERE fournisseur_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM fournisseurs WHERE id=?")->execute([$id]);
    flash('Fournisseur supprimé.', 'success');
    secureRedirect('fournisseurs.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        'nom'     => trim($_POST['nom']),
        'contact' => trim($_POST['contact']),
        'telephone'=> trim($_POST['telephone']),
        'email'   => trim($_POST['email']),
        'adresse' => trim($_POST['adresse']),
    ];
    if (!$d['nom']) { flash('Le nom est requis.', 'error'); secureRedirect('fournisseurs.php?action='.($id?'edit':'add').'&id='.$id); }
    if ($id) {
        $pdo->prepare("UPDATE fournisseurs SET nom=?,contact=?,telephone=?,email=?,adresse=? WHERE id=?")
            ->execute(array_merge(array_values($d), [$id]));
        flash('Fournisseur mis à jour.', 'success');
    } else {
        $pdo->prepare("INSERT INTO fournisseurs (nom,contact,telephone,email,adresse) VALUES (?,?,?,?,?)")
            ->execute(array_values($d));
        flash('Fournisseur ajouté.', 'success');
    }
    secureRedirect('fournisseurs.php');
}

$fourn = null;
if (in_array($action, ['edit','view']) && $id) {
    $s = $pdo->prepare("SELECT * FROM fournisseurs WHERE id=?"); $s->execute([$id]); $fourn = $s->fetch();
}

$fournisseurs = $pdo->query("SELECT f.*,
    (SELECT COUNT(*) FROM approvisionnements WHERE fournisseur_id=f.id) as nb_appro,
    (SELECT COALESCE(SUM(total),0) FROM approvisionnements WHERE fournisseur_id=f.id) as total_achats
    FROM fournisseurs f ORDER BY f.nom")->fetchAll();

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-industry" style="color:var(--or)"></i> <span>Fournisseurs</span></h1>
  <p><?= count($fournisseurs) ?> fournisseur<?= count($fournisseurs)>1?'s':'' ?> référencé<?= count($fournisseurs)>1?'s':'' ?></p>
</div>

<?php if (in_array($action, ['add','edit'])): ?>
<div class="card-omega" style="max-width:700px">
  <div class="card-head">
    <h4><i class="fas fa-<?=$id?'edit':'plus'?>"></i> <?=$id?'Modifier':'Nouveau'?> Fournisseur</h4>
    <a href="fournisseurs.php" class="btn-omega btn-omega-outline">← Retour</a>
  </div>
  <div class="card-body">
    <form method="POST" class="form-omega">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Raison Sociale *</label>
          <input type="text" name="nom" class="form-control" required placeholder="Nom du fournisseur"
            value="<?= htmlspecialchars($fourn['nom'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Personne Contact</label>
          <input type="text" name="contact" class="form-control" placeholder="M. / Mme..."
            value="<?= htmlspecialchars($fourn['contact'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Téléphone</label>
          <input type="tel" name="telephone" class="form-control" placeholder="33 XXX XX XX"
            value="<?= htmlspecialchars($fourn['telephone'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="contact@fournisseur.com"
            value="<?= htmlspecialchars($fourn['email'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Adresse</label>
          <textarea name="adresse" class="form-control" rows="2" placeholder="Adresse complète..."><?= htmlspecialchars($fourn['adresse'] ?? '') ?></textarea>
        </div>
      </div>
      <div style="margin-top:20px;display:flex;gap:10px">
        <button type="submit" class="btn-omega btn-omega-primary"><i class="fas fa-save"></i> Enregistrer</button>
        <a href="fournisseurs.php" class="btn-omega btn-omega-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-list"></i> Liste des Fournisseurs</h4>
    <a href="fournisseurs.php?action=add" class="btn-omega btn-omega-primary"><i class="fas fa-plus"></i> Nouveau fournisseur</a>
  </div>
  <div style="overflow-x:auto">
    <table class="table-omega">
      <thead><tr><th>Fournisseur</th><th>Contact</th><th>Téléphone</th><th>Email</th><th>Livraisons</th><th>Total Achats</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if(empty($fournisseurs)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px">Aucun fournisseur</td></tr>
        <?php else: foreach($fournisseurs as $f): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:38px;height:38px;border-radius:10px;
                background:linear-gradient(135deg,rgba(41,128,185,.3),rgba(41,128,185,.1));
                border:1px solid rgba(41,128,185,.3);
                display:flex;align-items:center;justify-content:center;font-size:1.2rem">🏭</div>
              <div>
                <strong><?= htmlspecialchars($f['nom']) ?></strong><br>
                <?php if($f['adresse']): ?><small style="color:var(--muted)"><?= htmlspecialchars(mb_substr($f['adresse'],0,35)) ?></small><?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($f['contact']??'—') ?></td>
          <td><?= htmlspecialchars($f['telephone']??'—') ?></td>
          <td><small><?= htmlspecialchars($f['email']??'—') ?></small></td>
          <td><span class="badge-stat b-info"><?= $f['nb_appro'] ?> livraison<?=$f['nb_appro']>1?'s':''?></span></td>
          <td><strong style="color:var(--or)"><?= number_format($f['total_achats'],0,',',' ') ?> FCFA</strong></td>
          <td>
            <div style="display:flex;gap:5px">
              <a href="fournisseurs.php?action=edit&id=<?=$f['id']?>" class="btn-omega btn-omega-gold" style="padding:5px 10px;font-size:.75rem"><i class="fas fa-edit"></i></a>
              <a href="fournisseurs.php?action=delete&id=<?=$f['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:5px 10px;font-size:.75rem"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
