<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// ── TRAITEMENTS POST ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;
    $mesures = json_encode([
        'poitrine' => (int)($d['poitrine'] ?? 0),
        'taille'   => (int)($d['taille']   ?? 0),
        'hanches'  => (int)($d['hanches']  ?? 0),
        'hauteur'  => (int)($d['hauteur']  ?? 0),
        'epaules'  => (int)($d['epaules']  ?? 0),
        'bras'     => (int)($d['bras']     ?? 0),
    ]);

    if ($d['form_action'] === 'create') {
        $code = genererCode('CLI', 'clients', 'code_client');
        $stmt = $pdo->prepare("INSERT INTO clients (code_client,nom,prenom,telephone,telephone2,email,adresse,quartier,ville,mesures,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$code, strtoupper($d['nom']), $d['prenom'], $d['telephone'], $d['telephone2'], $d['email'], $d['adresse'], $d['quartier'], $d['ville'] ?: 'Dakar', $mesures, $d['notes']]);
        setFlash('success', "Client {$d['prenom']} " . strtoupper($d['nom']) . " créé avec succès ! Code : $code");
        header('Location: clients.php'); exit;

    } elseif ($d['form_action'] === 'update') {
        $stmt = $pdo->prepare("UPDATE clients SET nom=?,prenom=?,telephone=?,telephone2=?,email=?,adresse=?,quartier=?,ville=?,mesures=?,notes=?,statut=? WHERE id=?");
        $stmt->execute([strtoupper($d['nom']), $d['prenom'], $d['telephone'], $d['telephone2'], $d['email'], $d['adresse'], $d['quartier'], $d['ville'], $mesures, $d['notes'], $d['statut'], $d['id']]);
        setFlash('success', 'Client mis à jour avec succès !');
        header('Location: clients.php'); exit;

    } elseif ($d['form_action'] === 'delete') {
        $pdo->prepare("UPDATE clients SET statut='inactif' WHERE id=?")->execute([$d['id']]);
        setFlash('info', 'Client désactivé.');
        header('Location: clients.php'); exit;
    }
}

// ── DONNÉES ───────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$filterStatut = $_GET['statut'] ?? '';

$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (c.nom LIKE ? OR c.prenom LIKE ? OR c.telephone LIKE ? OR c.code_client LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
if ($filterStatut) {
    $where .= " AND c.statut=?";
    $params[] = $filterStatut;
}

$clients = $pdo->prepare("
  SELECT c.*,
    (SELECT COUNT(*) FROM commandes WHERE client_id=c.id) as nb_commandes,
    (SELECT COALESCE(SUM(montant_ttc),0) FROM factures WHERE client_id=c.id) as ca_total
  FROM clients c $where ORDER BY c.nom,c.prenom
");
$clients->execute($params);
$clients = $clients->fetchAll();

$client = null;
if ($id) {
    $client = $pdo->prepare("SELECT * FROM clients WHERE id=?");
    $client->execute([$id]);
    $client = $client->fetch();
    $mesures = json_decode($client['mesures'] ?? '{}', true);
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- TOPBAR ACTIONS -->
<div style="display:flex;gap:12px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <form method="GET" style="display:contents">
      <input type="text" name="q" placeholder="Rechercher un client..." value="<?= htmlspecialchars($search) ?>">
    </form>
  </div>
  <select onchange="location.href='?statut='+this.value" style="max-width:160px">
    <option value="">Tous statuts</option>
    <option value="actif"   <?= $filterStatut==='actif'   ? 'selected':'' ?>>Actifs</option>
    <option value="inactif" <?= $filterStatut==='inactif' ? 'selected':'' ?>>Inactifs</option>
  </select>
  <div style="margin-left:auto">
    <button class="btn btn-primary" data-modal="modalClient" onclick="resetForm()">➕ Nouveau Client</button>
  </div>
</div>

<!-- TABLEAU -->
<div class="card slide-up">
  <div class="card-header">
    <div class="card-title">👥 Fichier Clients <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">(<?= count($clients) ?> résultats)</span></div>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead>
        <tr>
          <th>Code</th>
          <th>Nom & Prénom</th>
          <th>Téléphone</th>
          <th>Email</th>
          <th>Quartier</th>
          <th>Commandes</th>
          <th>CA Total</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clients as $c): ?>
        <tr>
          <td><span style="font-family:monospace;font-size:.8rem;color:var(--primary);font-weight:700"><?= $c['code_client'] ?></span></td>
          <td>
            <strong><?= htmlspecialchars($c['nom']) ?></strong>
            <span style="color:var(--text-muted)"> <?= htmlspecialchars($c['prenom']) ?></span>
          </td>
          <td><?= $c['telephone'] ?: '-' ?></td>
          <td style="font-size:.82rem"><?= $c['email'] ?: '-' ?></td>
          <td><?= $c['quartier'] ?: $c['ville'] ?></td>
          <td>
            <span class="badge" style="background:var(--info)"><?= $c['nb_commandes'] ?> cmd</span>
          </td>
          <td><strong style="color:var(--primary)"><?= formatMontant($c['ca_total']) ?></strong></td>
          <td>
            <span class="badge" style="background:<?= $c['statut']==='actif' ? 'var(--success)' : 'var(--gris)' ?>">
              <?= $c['statut'] === 'actif' ? '✅ Actif' : '⏸ Inactif' ?>
            </span>
          </td>
          <td>
            <div class="td-actions">
              <button class="action-btn" title="Modifier"
                onclick="editClient(<?= htmlspecialchars(json_encode($c)) ?>)">✏️</button>
              <a class="action-btn" title="Commandes" href="commandes.php?client_id=<?= $c['id'] ?>">🧵</a>
              <form method="POST" style="display:inline">
                <input type="hidden" name="form_action" value="delete">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button type="submit" class="action-btn danger confirm-delete" title="Désactiver">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($clients)): ?>
        <tr><td colspan="9">
          <div class="empty-state">
            <div class="empty-icon">👥</div>
            <p>Aucun client trouvé</p>
          </div>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL CLIENT -->
<div class="modal-overlay" id="modalClient">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modalClientTitle">➕ Nouveau Client</div>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="form_action" id="clientFormAction" value="create">
      <input type="hidden" name="id" id="clientId">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Nom *</label>
            <input type="text" name="nom" id="fNom" required placeholder="DIALLO">
          </div>
          <div class="form-group">
            <label>Prénom *</label>
            <input type="text" name="prenom" id="fPrenom" required placeholder="Fatou">
          </div>
          <div class="form-group">
            <label>Téléphone Principal</label>
            <input type="tel" name="telephone" id="fTel" placeholder="77 000 00 00">
          </div>
          <div class="form-group">
            <label>Téléphone 2</label>
            <input type="tel" name="telephone2" id="fTel2">
          </div>
          <div class="form-group full">
            <label>Email</label>
            <input type="email" name="email" id="fEmail">
          </div>
          <div class="form-group full">
            <label>Adresse</label>
            <input type="text" name="adresse" id="fAdresse">
          </div>
          <div class="form-group">
            <label>Quartier</label>
            <input type="text" name="quartier" id="fQuartier" placeholder="Plateau">
          </div>
          <div class="form-group">
            <label>Ville</label>
            <input type="text" name="ville" id="fVille" value="Dakar">
          </div>
          <div class="form-group" id="statutGroup" style="display:none">
            <label>Statut</label>
            <select name="statut" id="fStatut">
              <option value="actif">Actif</option>
              <option value="inactif">Inactif</option>
            </select>
          </div>
        </div>

        <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
          <h3 style="font-family:var(--font-display);font-size:1rem;margin-bottom:14px;color:var(--primary)">📏 Mesures Corporelles (cm)</h3>
          <div class="form-grid-3">
            <div class="form-group">
              <label>Poitrine</label>
              <input type="number" name="poitrine" id="fPoitrine" placeholder="92">
            </div>
            <div class="form-group">
              <label>Taille</label>
              <input type="number" name="taille" id="fTaille" placeholder="72">
            </div>
            <div class="form-group">
              <label>Hanches</label>
              <input type="number" name="hanches" id="fHanches" placeholder="98">
            </div>
            <div class="form-group">
              <label>Hauteur</label>
              <input type="number" name="hauteur" id="fHauteur" placeholder="165">
            </div>
            <div class="form-group">
              <label>Épaules</label>
              <input type="number" name="epaules" id="fEpaules" placeholder="38">
            </div>
            <div class="form-group">
              <label>Longueur bras</label>
              <input type="number" name="bras" id="fBras" placeholder="58">
            </div>
          </div>
        </div>

        <div class="form-group" style="margin-top:16px">
          <label>Notes</label>
          <textarea name="notes" id="fNotes" placeholder="Préférences, allergies tissus, etc."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-close">Annuler</button>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
function resetForm() {
  document.getElementById('clientFormAction').value = 'create';
  document.getElementById('clientId').value = '';
  document.getElementById('modalClientTitle').textContent = '➕ Nouveau Client';
  document.getElementById('statutGroup').style.display = 'none';
  ['Nom','Prenom','Tel','Tel2','Email','Adresse','Quartier','Notes','Poitrine','Taille','Hanches','Hauteur','Epaules','Bras'].forEach(f => {
    const el = document.getElementById('f' + f);
    if (el) el.value = f === 'Ville' ? 'Dakar' : '';
  });
  document.getElementById('fVille').value = 'Dakar';
}

function editClient(c) {
  const m = JSON.parse(c.mesures || '{}');
  document.getElementById('clientFormAction').value = 'update';
  document.getElementById('clientId').value = c.id;
  document.getElementById('modalClientTitle').textContent = '✏️ Modifier Client';
  document.getElementById('statutGroup').style.display = 'block';
  document.getElementById('fNom').value       = c.nom;
  document.getElementById('fPrenom').value    = c.prenom;
  document.getElementById('fTel').value       = c.telephone || '';
  document.getElementById('fTel2').value      = c.telephone2 || '';
  document.getElementById('fEmail').value     = c.email || '';
  document.getElementById('fAdresse').value   = c.adresse || '';
  document.getElementById('fQuartier').value  = c.quartier || '';
  document.getElementById('fVille').value     = c.ville || 'Dakar';
  document.getElementById('fNotes').value     = c.notes || '';
  document.getElementById('fStatut').value    = c.statut;
  document.getElementById('fPoitrine').value  = m.poitrine || '';
  document.getElementById('fTaille').value    = m.taille   || '';
  document.getElementById('fHanches').value   = m.hanches  || '';
  document.getElementById('fHauteur').value   = m.hauteur  || '';
  document.getElementById('fEpaules').value   = m.epaules  || '';
  document.getElementById('fBras').value      = m.bras     || '';
  document.getElementById('modalClient').classList.add('open');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
