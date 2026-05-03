<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    if ($d['form_action'] === 'create') {
        $pdo->prepare("INSERT INTO tissus (nom,type_tissu,couleur,stock_metres,prix_metre,fournisseur) VALUES (?,?,?,?,?,?)")
            ->execute([$d['nom'],$d['type_tissu'],$d['couleur'],$d['stock_metres'],$d['prix_metre'],$d['fournisseur']]);
        setFlash('success','Tissu ajouté au stock !');
        header('Location: tissus.php'); exit;
    } elseif ($d['form_action'] === 'delete') {
        $pdo->prepare("DELETE FROM tissus WHERE id=?")->execute([$d['id']]);
        setFlash('info','Tissu supprimé.');
        header('Location: tissus.php'); exit;
    }
}

$tissus = $pdo->query("SELECT * FROM tissus ORDER BY type_tissu, nom")->fetchAll();
$valeurStock = $pdo->query("SELECT COALESCE(SUM(stock_metres*prix_metre),0) FROM tissus")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<div style="display:flex;gap:20px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
  <div class="stat-card gold" style="padding:16px 24px;box-shadow:var(--shadow)">
    <div class="stat-label">Valeur Stock Total</div>
    <div class="stat-value" style="color:var(--or)"><?= formatMontant($valeurStock) ?></div>
  </div>
  <div style="margin-left:auto">
    <button class="btn btn-primary" data-modal="modalTissu">🎨 Ajouter Tissu</button>
  </div>
</div>

<div class="card slide-up">
  <div class="card-header">
    <div class="card-title">🎨 Tissus & Stock <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">(<?= count($tissus) ?>)</span></div>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead><tr><th>Nom</th><th>Type</th><th>Couleur</th><th>Stock (m)</th><th>Prix/m</th><th>Valeur</th><th>Fournisseur</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($tissus as $t):
          $valeur = $t['stock_metres'] * $t['prix_metre'];
          $alerte = $t['stock_metres'] < 10;
        ?>
        <tr <?= $alerte ? 'style="background:rgba(193,18,31,.04)"' : '' ?>>
          <td><strong><?= htmlspecialchars($t['nom']) ?></strong> <?= $alerte ? '⚠️' : '' ?></td>
          <td><span class="badge" style="background:var(--primary)"><?= ucfirst($t['type_tissu']) ?></span></td>
          <td><?= htmlspecialchars($t['couleur'] ?? '') ?></td>
          <td style="color:<?= $alerte ? 'var(--rouge)' : 'inherit' ?>"><strong><?= number_format($t['stock_metres'],1) ?> m</strong></td>
          <td><?= formatMontant($t['prix_metre']) ?>/m</td>
          <td><strong style="color:var(--or)"><?= formatMontant($valeur) ?></strong></td>
          <td style="font-size:.83rem"><?= htmlspecialchars($t['fournisseur'] ?? '') ?></td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="form_action" value="delete">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button type="submit" class="action-btn danger confirm-delete">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($tissus)): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🎨</div><p>Aucun tissu en stock</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalTissu">
  <div class="modal" style="max-width:520px">
    <div class="modal-header">
      <div class="modal-title">🎨 Ajouter Tissu au Stock</div>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="form_action" value="create">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full"><label>Nom *</label><input type="text" name="nom" required placeholder="Bazin Riche Blanc"></div>
          <div class="form-group">
            <label>Type *</label>
            <select name="type_tissu" required>
              <?php foreach (['bazin','wax','soie','coton','velours','dentelle','organza','autre'] as $t): ?>
              <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Couleur</label><input type="text" name="couleur" placeholder="Blanc cassé"></div>
          <div class="form-group"><label>Stock (mètres)</label><input type="number" name="stock_metres" step="0.5" min="0" placeholder="50"></div>
          <div class="form-group"><label>Prix par mètre (FCFA)</label><input type="number" name="prix_metre" min="0" step="100" placeholder="8500"></div>
          <div class="form-group full"><label>Fournisseur</label><input type="text" name="fournisseur" placeholder="Marché Sandaga"></div>
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
