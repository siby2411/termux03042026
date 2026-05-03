<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();
$action    = $_GET['action'] ?? 'list';
$id        = (int)($_GET['id'] ?? 0);
$clientFlt = (int)($_GET['client_id'] ?? 0);

// ── TRAITEMENTS POST ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;

    if ($d['form_action'] === 'create') {
        $num = genererCode('CMD', 'commandes', 'numero_commande');
        $stmt = $pdo->prepare("INSERT INTO commandes
          (numero_commande,client_id,date_commande,date_livraison,statut,priorite,notes,acompte_verse,total_ht,total_ttc,reste_a_payer)
          VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $ht = (float)($d['total_ht'] ?? 0);
        $acc = (float)($d['acompte_verse'] ?? 0);
        $stmt->execute([$num, $d['client_id'], $d['date_commande'], $d['date_livraison'] ?: null,
          $d['statut'], $d['priorite'], $d['notes'], $acc, $ht, $ht, $ht - $acc]);
        setFlash('success', "Commande $num créée avec succès !");
        header('Location: commandes.php'); exit;

    } elseif ($d['form_action'] === 'update') {
        $ht  = (float)($d['total_ht'] ?? 0);
        $acc = (float)($d['acompte_verse'] ?? 0);
        $stmt = $pdo->prepare("UPDATE commandes SET client_id=?,date_commande=?,date_livraison=?,statut=?,priorite=?,notes=?,acompte_verse=?,total_ht=?,total_ttc=?,reste_a_payer=? WHERE id=?");
        $stmt->execute([$d['client_id'], $d['date_commande'], $d['date_livraison'] ?: null,
          $d['statut'], $d['priorite'], $d['notes'], $acc, $ht, $ht, $ht - $acc, $d['id']]);
        setFlash('success', 'Commande mise à jour !');
        header('Location: commandes.php'); exit;

    } elseif ($d['form_action'] === 'delete') {
        $pdo->prepare("UPDATE commandes SET statut='annulée' WHERE id=?")->execute([$d['id']]);
        setFlash('info', 'Commande annulée.');
        header('Location: commandes.php'); exit;

    } elseif ($d['form_action'] === 'change_statut') {
        $pdo->prepare("UPDATE commandes SET statut=? WHERE id=?")->execute([$d['statut'], $d['id']]);
        setFlash('success', 'Statut mis à jour !');
        header('Location: commandes.php'); exit;
    }
}

// ── DONNÉES ───────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$filterStatut = $_GET['statut'] ?? '';

$where  = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (c.numero_commande LIKE ? OR cl.nom LIKE ? OR cl.prenom LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%"]);
}
if ($filterStatut) { $where .= " AND c.statut=?"; $params[] = $filterStatut; }
if ($clientFlt)    { $where .= " AND c.client_id=?"; $params[] = $clientFlt; }

$commandes = $pdo->prepare("
  SELECT c.*, CONCAT(cl.prenom,' ',cl.nom) AS client_nom, cl.telephone AS client_tel
  FROM commandes c
  JOIN clients cl ON cl.id = c.client_id
  $where ORDER BY c.date_commande DESC
");
$commandes->execute($params);
$commandes = $commandes->fetchAll();

// Tous les clients pour le select
$allClients = $pdo->query("SELECT id, CONCAT(prenom,' ',nom) AS label, telephone FROM clients WHERE statut='actif' ORDER BY nom")->fetchAll();

$commande = null;
if ($id) {
    $commande = $pdo->prepare("SELECT c.*, CONCAT(cl.prenom,' ',cl.nom) AS client_nom FROM commandes c JOIN clients cl ON cl.id=c.client_id WHERE c.id=?");
    $commande->execute([$id]);
    $commande = $commande->fetch();
}

// Résumé stats rapides
$statsCmd = $pdo->query("SELECT statut, COUNT(*) as nb, COALESCE(SUM(total_ttc),0) as total FROM commandes WHERE statut NOT IN ('annulée') GROUP BY statut")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- FILTRES & ACTIONS -->
<div style="display:flex;gap:12px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input type="text" name="q" placeholder="N° commande ou client..." value="<?= htmlspecialchars($search) ?>" style="width:240px">
    </div>
    <select name="statut" onchange="this.form.submit()" style="max-width:180px">
      <option value="">Tous les statuts</option>
      <?php foreach (['brouillon','confirmée','en_cours','essayage','terminée','livrée','annulée'] as $s): ?>
      <option value="<?= $s ?>" <?= $filterStatut===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <div style="margin-left:auto">
    <button class="btn btn-gold" data-modal="modalCmd" onclick="resetCmdForm()">🧵 Nouvelle Commande</button>
  </div>
</div>

<!-- MINI STATS STATUTS -->
<div style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap">
  <?php foreach ($statsCmd as $sc): ?>
  <div style="background:#fff;border-radius:10px;padding:10px 16px;box-shadow:var(--shadow);border-top:3px solid var(--or)">
    <?= badgeStatutCommande($sc['statut']) ?>
    <div style="font-size:.75rem;color:var(--text-muted);margin-top:4px"><?= $sc['nb'] ?> · <?= formatMontant($sc['total']) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- TABLEAU -->
<div class="card slide-up">
  <div class="card-header">
    <div class="card-title">🧵 Commandes <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">(<?= count($commandes) ?> résultats)</span></div>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead>
        <tr>
          <th>N° Commande</th>
          <th>Client</th>
          <th>Date Cmd</th>
          <th>Livraison</th>
          <th>Statut</th>
          <th>Priorité</th>
          <th>Total TTC</th>
          <th>Acompte</th>
          <th>Reste</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($commandes as $c):
          $enRetard = $c['date_livraison'] && $c['date_livraison'] < date('Y-m-d') && !in_array($c['statut'],['livrée','annulée']);
        ?>
        <tr <?= $enRetard ? 'style="background:rgba(193,18,31,.04)"' : '' ?>>
          <td><strong style="color:var(--primary);font-family:monospace"><?= $c['numero_commande'] ?></strong></td>
          <td>
            <div><?= htmlspecialchars($c['client_nom']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= $c['client_tel'] ?></div>
          </td>
          <td><?= formatDate($c['date_commande']) ?></td>
          <td style="color:<?= $enRetard ? 'var(--rouge)' : 'inherit' ?>">
            <?= $c['date_livraison'] ? formatDate($c['date_livraison']) : '-' ?>
            <?= $enRetard ? '⚠️' : '' ?>
          </td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="form_action" value="change_statut">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
              <select name="statut" onchange="this.form.submit()" style="font-size:.78rem;padding:4px 8px;border-radius:6px">
                <?php foreach (['brouillon','confirmée','en_cours','essayage','terminée','livrée','annulée'] as $s): ?>
                <option value="<?= $s ?>" <?= $c['statut']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td>
            <span class="badge badge-priorite-<?= $c['priorite'] ?>">
              <?= $c['priorite']==='vip' ? '⭐ VIP' : ($c['priorite']==='urgente' ? '🔴 Urgente' : '● Normale') ?>
            </span>
          </td>
          <td><strong><?= formatMontant($c['total_ttc']) ?></strong></td>
          <td style="color:var(--success)"><?= formatMontant($c['acompte_verse']) ?></td>
          <td><strong style="color:<?= $c['reste_a_payer'] > 0 ? 'var(--rouge)' : 'var(--success)' ?>"><?= formatMontant($c['reste_a_payer']) ?></strong></td>
          <td>
            <div class="td-actions">
              <button class="action-btn" title="Modifier" onclick='editCmd(<?= htmlspecialchars(json_encode($c)) ?>)'>✏️</button>
              <a class="action-btn" title="Voir facture" href="factures.php?commande_id=<?= $c['id'] ?>">📄</a>
              <form method="POST" style="display:inline">
                <input type="hidden" name="form_action" value="delete">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button type="submit" class="action-btn danger confirm-delete" title="Annuler">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($commandes)): ?>
        <tr><td colspan="10"><div class="empty-state"><div class="empty-icon">🧵</div><p>Aucune commande trouvée</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL COMMANDE -->
<div class="modal-overlay" id="modalCmd">
  <div class="modal" style="max-width:750px">
    <div class="modal-header">
      <div class="modal-title" id="modalCmdTitle">🧵 Nouvelle Commande</div>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="form_action" id="cmdFormAction" value="create">
      <input type="hidden" name="id" id="cmdId">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full">
            <label>Client *</label>
            <select name="client_id" id="cmdClientId" required>
              <option value="">-- Sélectionner un client --</option>
              <?php foreach ($allClients as $cl): ?>
              <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['label']) ?> — <?= $cl['telephone'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Date Commande *</label>
            <input type="date" name="date_commande" id="cmdDateCmd" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Date Livraison Prévue</label>
            <input type="date" name="date_livraison" id="cmdDateLiv">
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select name="statut" id="cmdStatut">
              <?php foreach (['confirmée','brouillon','en_cours','essayage','terminée','livrée'] as $s): ?>
              <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Priorité</label>
            <select name="priorite" id="cmdPriorite">
              <option value="normale">● Normale</option>
              <option value="urgente">🔴 Urgente</option>
              <option value="vip">⭐ VIP</option>
            </select>
          </div>
          <div class="form-group">
            <label>Montant Total (FCFA)</label>
            <input type="number" name="total_ht" id="cmdTotal" min="0" step="500" placeholder="0" oninput="calcReste()">
          </div>
          <div class="form-group">
            <label>Acompte Versé (FCFA)</label>
            <input type="number" name="acompte_verse" id="cmdAcompte" min="0" step="500" placeholder="0" oninput="calcReste()">
          </div>
          <div class="form-group full">
            <label>Reste à Payer</label>
            <input type="text" id="cmdReste" readonly style="background:var(--gris-clair);font-weight:600;color:var(--rouge)">
          </div>
          <div class="form-group full">
            <label>Notes / Détail Modèles</label>
            <textarea name="notes" id="cmdNotes" rows="4" placeholder="Grand boubou brodé en bazin blanc, robe wax pour cérémonie..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-close">Annuler</button>
        <button type="submit" class="btn btn-gold">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
function calcReste() {
  const tot = parseFloat(document.getElementById('cmdTotal').value) || 0;
  const acc = parseFloat(document.getElementById('cmdAcompte').value) || 0;
  document.getElementById('cmdReste').value = (tot - acc).toLocaleString('fr-SN') + ' FCFA';
}

function resetCmdForm() {
  document.getElementById('cmdFormAction').value = 'create';
  document.getElementById('cmdId').value = '';
  document.getElementById('modalCmdTitle').textContent = '🧵 Nouvelle Commande';
  document.getElementById('cmdClientId').value = '';
  document.getElementById('cmdDateCmd').value = new Date().toISOString().split('T')[0];
  document.getElementById('cmdDateLiv').value = '';
  document.getElementById('cmdStatut').value = 'confirmée';
  document.getElementById('cmdPriorite').value = 'normale';
  document.getElementById('cmdTotal').value = '';
  document.getElementById('cmdAcompte').value = '';
  document.getElementById('cmdReste').value = '';
  document.getElementById('cmdNotes').value = '';
}

function editCmd(c) {
  document.getElementById('cmdFormAction').value = 'update';
  document.getElementById('cmdId').value = c.id;
  document.getElementById('modalCmdTitle').textContent = '✏️ Modifier Commande';
  document.getElementById('cmdClientId').value = c.client_id;
  document.getElementById('cmdDateCmd').value = c.date_commande;
  document.getElementById('cmdDateLiv').value = c.date_livraison || '';
  document.getElementById('cmdStatut').value = c.statut;
  document.getElementById('cmdPriorite').value = c.priorite;
  document.getElementById('cmdTotal').value = c.total_ht;
  document.getElementById('cmdAcompte').value = c.acompte_verse;
  document.getElementById('cmdNotes').value = c.notes || '';
  calcReste();
  document.getElementById('modalCmd').classList.add('open');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
