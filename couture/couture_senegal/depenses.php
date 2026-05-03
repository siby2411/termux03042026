<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    if ($d['form_action'] === 'create') {
        $pdo->prepare("INSERT INTO depenses (date_dep,categorie,description,montant,fournisseur,reference,notes) VALUES (?,?,?,?,?,?,?)")
            ->execute([$d['date_dep'],$d['categorie'],$d['description'],$d['montant'],$d['fournisseur'],$d['reference'],$d['notes']]);
        setFlash('success','Dépense enregistrée !');
        header('Location: depenses.php'); exit;
    } elseif ($d['form_action'] === 'delete') {
        $pdo->prepare("DELETE FROM depenses WHERE id=?")->execute([$d['id']]);
        setFlash('info','Dépense supprimée.');
        header('Location: depenses.php'); exit;
    }
}

$search = trim($_GET['q'] ?? '');
$filterCat = $_GET['cat'] ?? '';
$annee = (int)($_GET['annee'] ?? date('Y'));

$where = "WHERE YEAR(date_dep)=$annee";
$params = [];
if ($search) { $where .= " AND (description LIKE ? OR fournisseur LIKE ?)"; $params = ["%$search%","%$search%"]; }
if ($filterCat) { $where .= " AND categorie=?"; $params[] = $filterCat; }

$depenses = $pdo->prepare("SELECT * FROM depenses $where ORDER BY date_dep DESC");
$depenses->execute($params);
$depenses = $depenses->fetchAll();

$totalMois = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM depenses WHERE MONTH(date_dep)=MONTH(CURDATE()) AND YEAR(date_dep)=YEAR(CURDATE())")->fetchColumn();
$totalAnnee = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM depenses WHERE YEAR(date_dep)=$annee")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<div class="stats-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:24px;max-width:600px">
  <div class="stat-card red">
    <div class="stat-icon" style="color:var(--rouge)">📅</div>
    <div class="stat-value" style="color:var(--rouge);font-size:1.2rem"><?= formatMontant($totalMois) ?></div>
    <div class="stat-label">Ce Mois</div>
  </div>
  <div class="stat-card gold">
    <div class="stat-icon" style="color:var(--or)">📆</div>
    <div class="stat-value" style="color:var(--or);font-size:1.2rem"><?= formatMontant($totalAnnee) ?></div>
    <div class="stat-label">Année <?= $annee ?></div>
  </div>
</div>

<div style="display:flex;gap:12px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <input type="text" id="tableSearch" placeholder="Rechercher..." style="width:220px">
  </div>
  <select onchange="location.href='?cat='+this.value+'&annee=<?= $annee ?>'" style="max-width:180px">
    <option value="">Toutes catégories</option>
    <?php foreach (['tissu','fourniture','loyer','salaire','electricite','transport','marketing','autre'] as $cat): ?>
    <option value="<?= $cat ?>" <?= $filterCat===$cat?'selected':'' ?>><?= ucfirst($cat) ?></option>
    <?php endforeach; ?>
  </select>
  <select onchange="location.href='?annee='+this.value" style="max-width:120px">
    <?php for ($a=date('Y'); $a>=date('Y')-3; $a--): ?>
    <option value="<?= $a ?>" <?= $a==$annee?'selected':'' ?>><?= $a ?></option>
    <?php endfor; ?>
  </select>
  <div style="margin-left:auto">
    <button class="btn btn-danger" data-modal="modalDep">➕ Nouvelle Dépense</button>
  </div>
</div>

<div class="card slide-up">
  <div class="card-header">
    <div class="card-title">🧾 Dépenses <?= $annee ?> <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">(<?= count($depenses) ?>)</span></div>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Catégorie</th><th>Description</th><th>Fournisseur</th><th>Référence</th><th>Montant</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($depenses as $d): ?>
        <tr>
          <td><?= formatDate($d['date_dep']) ?></td>
          <td>
            <span class="badge" style="background:var(--or)"><?= ucfirst($d['categorie']) ?></span>
          </td>
          <td><?= htmlspecialchars($d['description']) ?></td>
          <td style="font-size:.82rem"><?= htmlspecialchars($d['fournisseur'] ?? '') ?></td>
          <td style="font-size:.78rem;font-family:monospace"><?= $d['reference'] ?? '-' ?></td>
          <td><strong style="color:var(--rouge)"><?= formatMontant($d['montant']) ?></strong></td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="form_action" value="delete">
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <button type="submit" class="action-btn danger confirm-delete" title="Supprimer">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($depenses)): ?>
        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🧾</div><p>Aucune dépense pour cette période</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL DÉPENSE -->
<div class="modal-overlay" id="modalDep">
  <div class="modal" style="max-width:520px">
    <div class="modal-header">
      <div class="modal-title">🧾 Nouvelle Dépense</div>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="form_action" value="create">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Date *</label>
            <input type="date" name="date_dep" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Catégorie *</label>
            <select name="categorie" required>
              <?php foreach (['tissu','fourniture','loyer','salaire','electricite','transport','marketing','autre'] as $cat): ?>
              <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group full">
            <label>Description *</label>
            <input type="text" name="description" required placeholder="Achat bazin blanc 20m">
          </div>
          <div class="form-group">
            <label>Montant (FCFA) *</label>
            <input type="number" name="montant" min="0" step="500" required placeholder="0">
          </div>
          <div class="form-group">
            <label>Fournisseur</label>
            <input type="text" name="fournisseur" placeholder="Marché Sandaga">
          </div>
          <div class="form-group">
            <label>Référence</label>
            <input type="text" name="reference">
          </div>
          <div class="form-group full">
            <label>Notes</label>
            <textarea name="notes" rows="2"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-close">Annuler</button>
        <button type="submit" class="btn btn-danger">💾 Enregistrer Dépense</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
