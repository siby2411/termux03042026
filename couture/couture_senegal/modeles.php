<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    if ($d['form_action'] === 'create_modele') {
        $code = genererCode('MOD', 'modeles', 'code_modele');
        $pdo->prepare("INSERT INTO modeles (code_modele,nom,categorie,description,prix_base) VALUES (?,?,?,?,?)")
            ->execute([$code,$d['nom'],$d['categorie'],$d['description'],$d['prix_base']]);
        setFlash('success',"Modèle $code créé !");
        header('Location: modeles.php'); exit;
    } elseif ($d['form_action'] === 'delete_modele') {
        $pdo->prepare("UPDATE modeles SET actif=0 WHERE id=?")->execute([$d['id']]);
        setFlash('info','Modèle désactivé.');
        header('Location: modeles.php'); exit;
    }
}

$modeles = $pdo->query("SELECT * FROM modeles WHERE actif=1 ORDER BY categorie,nom")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
  <div></div>
  <button class="btn btn-primary" data-modal="modalMod">👗 Nouveau Modèle</button>
</div>

<div class="card slide-up">
  <div class="card-header">
    <div class="card-title">👗 Catalogue des Modèles <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">(<?= count($modeles) ?>)</span></div>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Nom</th><th>Catégorie</th><th>Description</th><th>Prix Base</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($modeles as $m): ?>
        <tr>
          <td><span style="font-family:monospace;font-size:.8rem;color:var(--primary);font-weight:700"><?= $m['code_modele'] ?></span></td>
          <td><strong><?= htmlspecialchars($m['nom']) ?></strong></td>
          <td><span class="badge" style="background:var(--primary)"><?= ucfirst($m['categorie']) ?></span></td>
          <td style="font-size:.83rem;max-width:260px"><?= htmlspecialchars($m['description'] ?? '') ?></td>
          <td><strong style="color:var(--or)"><?= formatMontant($m['prix_base']) ?></strong></td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="form_action" value="delete_modele">
              <input type="hidden" name="id" value="<?= $m['id'] ?>">
              <button type="submit" class="action-btn danger confirm-delete">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($modeles)): ?>
        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">👗</div><p>Aucun modèle</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalMod">
  <div class="modal" style="max-width:500px">
    <div class="modal-header">
      <div class="modal-title">👗 Nouveau Modèle</div>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="form_action" value="create_modele">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full"><label>Nom du Modèle *</label><input type="text" name="nom" required placeholder="Grand Boubou Brodé"></div>
          <div class="form-group">
            <label>Catégorie *</label>
            <select name="categorie" required>
              <?php foreach (['boubou','kaftan','robe','tailleur','chemise','pantalon','jupe','ensemble','autre'] as $c): ?>
              <option value="<?= $c ?>"><?= ucfirst($c) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Prix de Base (FCFA)</label><input type="number" name="prix_base" min="0" step="1000" placeholder="50000"></div>
          <div class="form-group full"><label>Description</label><textarea name="description" rows="3"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-close">Annuler</button>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
