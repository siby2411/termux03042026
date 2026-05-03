<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// ── TRAITEMENTS POST ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    if ($d['form_action'] === 'create_modele') {
        $code = genererCode('MOD', 'modeles', 'code_modele');
        $pdo->prepare("INSERT INTO modeles (code_modele,nom,categorie,genre,description,prix_base) VALUES (?,?,?,?,?,?)")
            ->execute([$code, $d['nom'], $d['categorie'], $d['genre'], $d['description'], $d['prix_base']]);
        setFlash('success', "Modèle $code créé avec succès !");
        header('Location: modeles.php'); exit;
    } elseif ($d['form_action'] === 'delete_modele') {
        $pdo->prepare("UPDATE modeles SET actif=0 WHERE id=?")->execute([$d['id']]);
        setFlash('info', 'Modèle retiré du catalogue.');
        header('Location: modeles.php'); exit;
    }
}

$modeles = $pdo->query("SELECT * FROM modeles WHERE actif=1 ORDER BY genre, categorie, nom")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="bi bi-journal-bookmark me-2 text-primary"></i>Catalogue Modèles</h4>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMod">
        <i class="bi bi-plus-lg me-1"></i> Nouveau Modèle
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th class="ps-4">Code</th>
                    <th>Nom du Modèle</th>
                    <th>Genre</th>
                    <th>Catégorie</th>
                    <th>Prix de Base</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modeles as $m): ?>
                <tr>
                    <td class="ps-4"><code class="fw-bold text-primary"><?= $m['code_modele'] ?></code></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($m['nom']) ?></div>
                        <div class="text-muted small text-truncate" style="max-width: 250px;">
                            <?= htmlspecialchars($m['description'] ?? 'Aucune description') ?>
                        </div>
                    </td>
                    <td>
                        <?php if($m['genre'] === 'H'): ?>
                            <span class="badge bg-info-subtle text-info border border-info px-2">♂ Homme</span>
                        <?php elseif($m['genre'] === 'F'): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger px-2">♀ Femme</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2">Mixte</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border"><?= ucfirst($m['categorie']) ?></span>
                    </td>
                    <td>
                        <strong class="text-dark"><?= number_format($m['prix_base'], 0, ',', ' ') ?></strong>
                        <small class="text-muted">FCFA</small>
                    </td>
                    <td class="text-end pe-4">
                        <form method="POST" onsubmit="return confirm('Supprimer ce modèle ?');" style="display:inline">
                            <input type="hidden" name="form_action" value="delete_modele">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalMod" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">👗 Nouveau Modèle</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="form_action" value="create_modele">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label small fw-bold">Nom du Modèle *</label>
            <input type="text" name="nom" class="form-control" required placeholder="Ex: Grand Boubou Brodé">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Genre *</label>
            <select name="genre" class="form-select border-primary" required>
              <option value="F">Femme</option>
              <option value="H">Homme</option>
              <option value="M">Mixte / Enfant</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Catégorie *</label>
            <select name="categorie" class="form-select" required>
              <?php foreach (['boubou','kaftan','robe','tailleur','chemise','pantalon','jupe','ensemble','autre'] as $cat): ?>
              <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small fw-bold">Prix de Base (FCFA)</label>
            <div class="input-group">
                <input type="number" name="prix_base" class="form-control" min="0" step="500">
                <span class="input-group-text">FCFA</span>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label small fw-bold">Description technique</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Détails sur la coupe, les finitions..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary px-4">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
