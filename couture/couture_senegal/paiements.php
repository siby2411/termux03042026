<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'create') {
    $d = $_POST;
    $fac = $pdo->prepare("SELECT * FROM factures WHERE id=?");
    $fac->execute([$d['facture_id']]);
    $fac = $fac->fetch();
    if ($fac) {
        $montant = (float)$d['montant'];
        $pdo->prepare("INSERT INTO paiements (facture_id,client_id,date_paiement,montant,mode_paiement,reference,notes) VALUES (?,?,?,?,?,?,?)")
            ->execute([$d['facture_id'],$fac['client_id'],$d['date_paiement'],$montant,$d['mode_paiement'],$d['reference'],$d['notes']]);
        $newPaye  = $fac['montant_paye'] + $montant;
        $newReste = max(0, $fac['montant_ttc'] - $newPaye);
        $pdo->prepare("UPDATE factures SET montant_paye=?,reste=?,statut=? WHERE id=?")
            ->execute([$newPaye,$newReste,$newReste<=0?'payée':'payée_partiel',$fac['id']]);
        setFlash('success','Paiement enregistré !');
    }
    header('Location: paiements.php'); exit;
}

$search = trim($_GET['q'] ?? '');
$where = "WHERE 1=1"; $params = [];
if ($search) {
    $where .= " AND (cl.nom LIKE ? OR cl.prenom LIKE ? OR f.numero_facture LIKE ?)";
    $params = ["%$search%","%$search%","%$search%"];
}

$paiements = $pdo->prepare("
  SELECT p.*, CONCAT(cl.prenom,' ',cl.nom) AS client_nom, f.numero_facture
  FROM paiements p
  JOIN clients cl ON cl.id = p.client_id
  JOIN factures f ON f.id = p.facture_id
  $where ORDER BY p.date_paiement DESC
");
$paiements->execute($params);
$paiements = $paiements->fetchAll();

$totalEnc = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE MONTH(date_paiement)=MONTH(CURDATE()) AND YEAR(date_paiement)=YEAR(CURDATE())")->fetchColumn();
$totalAll = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM paiements")->fetchColumn();

$allFacs = $pdo->query("
  SELECT f.id, f.numero_facture, f.reste, f.montant_ttc,
         CONCAT(cl.prenom,' ',cl.nom) AS client_nom
  FROM factures f JOIN clients cl ON cl.id=f.client_id
  WHERE f.statut IN ('émise','payée_partiel')
  ORDER BY f.date_facture DESC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="stats-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:24px;max-width:600px">
  <div class="stat-card green">
    <div class="stat-icon" style="color:var(--vert)">📅</div>
    <div class="stat-value" style="color:var(--vert);font-size:1.2rem"><?= formatMontant($totalEnc) ?></div>
    <div class="stat-label">Encaissé Ce Mois</div>
  </div>
  <div class="stat-card gold">
    <div class="stat-icon" style="color:var(--or)">💰</div>
    <div class="stat-value" style="color:var(--or);font-size:1.2rem"><?= formatMontant($totalAll) ?></div>
    <div class="stat-label">Total Encaissé</div>
  </div>
</div>

<div style="display:flex;gap:12px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <input type="text" id="tableSearch" placeholder="Rechercher..." style="width:240px">
  </div>
  <div style="margin-left:auto">
    <button class="btn btn-gold" data-modal="modalPay">💰 Nouveau Paiement</button>
  </div>
</div>

<div class="card slide-up">
  <div class="card-header">
    <div class="card-title">💰 Historique des Paiements <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">(<?= count($paiements) ?>)</span></div>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Client</th><th>Facture</th><th>Mode</th><th>Référence</th><th>Montant</th></tr></thead>
      <tbody>
        <?php foreach ($paiements as $p): ?>
        <tr>
          <td><?= formatDate($p['date_paiement']) ?></td>
          <td><?= htmlspecialchars($p['client_nom']) ?></td>
          <td style="color:var(--or);font-family:monospace;font-size:.85rem"><?= $p['numero_facture'] ?></td>
          <td>
            <?php $mIcons = ['espèces'=>'💵','wave'=>'📱','orange_money'=>'🟠','free_money'=>'🔵','virement'=>'🏦','chèque'=>'📝','autre'=>'💳']; ?>
            <span class="badge" style="background:var(--info)">
              <?= $mIcons[$p['mode_paiement']] ?? '' ?> <?= ucfirst(str_replace('_',' ',$p['mode_paiement'])) ?>
            </span>
          </td>
          <td style="font-size:.8rem;font-family:monospace"><?= $p['reference'] ?? '-' ?></td>
          <td><strong style="color:var(--success)"><?= formatMontant($p['montant']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($paiements)): ?>
        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">💰</div><p>Aucun paiement</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL PAIEMENT -->
<div class="modal-overlay" id="modalPay">
  <div class="modal" style="max-width:500px">
    <div class="modal-header">
      <div class="modal-title">💰 Nouveau Paiement</div>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="form_action" value="create">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full">
            <label>Facture *</label>
            <select name="facture_id" required onchange="updateReste(this)">
              <option value="">-- Sélectionner une facture --</option>
              <?php foreach ($allFacs as $f): ?>
              <option value="<?= $f['id'] ?>" data-reste="<?= $f['reste'] ?>">
                <?= $f['numero_facture'] ?> — <?= htmlspecialchars($f['client_nom']) ?> (Reste: <?= formatMontant($f['reste']) ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Date *</label>
            <input type="date" name="date_paiement" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Montant (FCFA) *</label>
            <input type="number" name="montant" id="payMontantNew" min="1" step="500" required>
          </div>
          <div class="form-group full">
            <label>Mode de Paiement</label>
            <select name="mode_paiement">
              <?php foreach (['espèces','wave','orange_money','free_money','virement','chèque','autre'] as $m): ?>
              <option value="<?= $m ?>"><?= ucfirst(str_replace('_',' ',$m)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Référence</label><input type="text" name="reference" placeholder="WAVE-XXXXX"></div>
          <div class="form-group"><label>Notes</label><input type="text" name="notes"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-close">Annuler</button>
        <button type="submit" class="btn btn-gold">✅ Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
function updateReste(sel) {
  const opt = sel.options[sel.selectedIndex];
  const reste = parseFloat(opt.dataset.reste) || 0;
  document.getElementById('payMontantNew').value = reste || '';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
