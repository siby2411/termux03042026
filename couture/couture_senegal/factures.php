<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$print  = isset($_GET['print']);

// ── TRAITEMENTS POST ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $_POST;

    if ($d['form_action'] === 'create') {
        $num  = genererCode('FAC', 'factures', 'numero_facture');
        $ht   = (float)($d['montant_ht'] ?? 0);
        $tva  = (float)($d['tva_pct'] ?? 0);
        $rem  = (float)($d['remise'] ?? 0);
        $tvam = round($ht * $tva / 100, 2);
        $ttc  = $ht + $tvam - $rem;
        $stmt = $pdo->prepare("INSERT INTO factures (numero_facture,commande_id,client_id,date_facture,date_echeance,statut,montant_ht,tva_pct,tva_montant,remise,montant_ttc,montant_paye,reste,notes)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,0,?,?)");
        $stmt->execute([$num, $d['commande_id'], $d['client_id'], $d['date_facture'], $d['date_echeance'] ?: null,
          'émise', $ht, $tva, $tvam, $rem, $ttc, $ttc, $d['notes']]);
        setFlash('success', "Facture $num créée !");
        header('Location: factures.php'); exit;

    } elseif ($d['form_action'] === 'update') {
        $ht  = (float)($d['montant_ht'] ?? 0);
        $tva = (float)($d['tva_pct'] ?? 0);
        $rem = (float)($d['remise'] ?? 0);
        $tvam = round($ht * $tva / 100, 2);
        $ttc  = $ht + $tvam - $rem;
        $stmt = $pdo->prepare("UPDATE factures SET date_echeance=?,statut=?,montant_ht=?,tva_pct=?,tva_montant=?,remise=?,montant_ttc=?,reste=montant_ttc-montant_paye,notes=? WHERE id=?");
        $stmt->execute([$d['date_echeance'] ?: null, $d['statut'], $ht, $tva, $tvam, $rem, $ttc, $d['notes'], $d['id']]);
        setFlash('success', 'Facture mise à jour !');
        header('Location: factures.php'); exit;

    } elseif ($d['form_action'] === 'delete') {
        $pdo->prepare("UPDATE factures SET statut='annulée' WHERE id=?")->execute([$d['id']]);
        setFlash('info', 'Facture annulée.');
        header('Location: factures.php'); exit;

    } elseif ($d['form_action'] === 'paiement') {
        // Enregistrer paiement rapide
        $fac = $pdo->prepare("SELECT * FROM factures WHERE id=?");
        $fac->execute([$d['facture_id']]);
        $fac = $fac->fetch();
        $montant = (float)$d['montant'];
        $pdo->prepare("INSERT INTO paiements (facture_id,client_id,date_paiement,montant,mode_paiement,reference,notes) VALUES (?,?,?,?,?,?,?)")
            ->execute([$d['facture_id'], $fac['client_id'], $d['date_paiement'], $montant, $d['mode_paiement'], $d['reference'], $d['notes_paiement']]);
        $newPaye  = $fac['montant_paye'] + $montant;
        $newReste = $fac['montant_ttc'] - $newPaye;
        $newStat  = $newReste <= 0 ? 'payée' : 'payée_partiel';
        $pdo->prepare("UPDATE factures SET montant_paye=?,reste=?,statut=? WHERE id=?")
            ->execute([$newPaye, max(0,$newReste), $newStat, $d['facture_id']]);
        // Mettre à jour commande
        $pdo->prepare("UPDATE commandes SET acompte_verse=acompte_verse+?, reste_a_payer=GREATEST(reste_a_payer-?,0) WHERE id=?")
            ->execute([$montant, $montant, $fac['commande_id']]);
        setFlash('success', "Paiement de " . formatMontant($montant) . " enregistré !");
        header('Location: factures.php'); exit;
    }
}

// ── DONNÉES ───────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$filterStatut = $_GET['statut'] ?? '';
$cmdFlt = (int)($_GET['commande_id'] ?? 0);

$where  = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (f.numero_facture LIKE ? OR cl.nom LIKE ? OR cl.prenom LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%"]);
}
if ($filterStatut) { $where .= " AND f.statut=?"; $params[] = $filterStatut; }
if ($cmdFlt)       { $where .= " AND f.commande_id=?"; $params[] = $cmdFlt; }

$factures = $pdo->prepare("
  SELECT f.*, CONCAT(cl.prenom,' ',cl.nom) AS client_nom,
         c.numero_commande
  FROM factures f
  JOIN clients cl ON cl.id = f.client_id
  JOIN commandes c ON c.id = f.commande_id
  $where ORDER BY f.date_facture DESC
");
$factures->execute($params);
$factures = $factures->fetchAll();

// Toutes les commandes pour le select
$allCmds = $pdo->query("
  SELECT c.id, c.numero_commande, c.total_ttc,
         CONCAT(cl.prenom,' ',cl.nom) AS client_nom,
         cl.id AS cid
  FROM commandes c
  JOIN clients cl ON cl.id = c.client_id
  WHERE c.statut NOT IN ('annulée')
  ORDER BY c.date_commande DESC
")->fetchAll();

// Stats rapides
$totalFacture = $pdo->query("SELECT COALESCE(SUM(montant_ttc),0) FROM factures WHERE statut!='annulée'")->fetchColumn();
$totalPaye    = $pdo->query("SELECT COALESCE(SUM(montant_paye),0) FROM factures WHERE statut!='annulée'")->fetchColumn();
$totalReste   = $pdo->query("SELECT COALESCE(SUM(reste),0) FROM factures WHERE statut IN ('émise','payée_partiel')")->fetchColumn();

// Détail facture pour impression
$facturePrint = null;
if ($print && $id) {
    $facturePrint = $pdo->prepare("
      SELECT f.*, CONCAT(cl.prenom,' ',cl.nom) AS client_nom,
             cl.telephone, cl.adresse, cl.quartier, cl.ville, cl.email,
             c.numero_commande, c.notes AS commande_notes
      FROM factures f
      JOIN clients cl ON cl.id = f.client_id
      JOIN commandes c ON c.id = f.commande_id
      WHERE f.id=?");
    $facturePrint->execute([$id]);
    $facturePrint = $facturePrint->fetch();

    $paiementsFac = $pdo->prepare("SELECT * FROM paiements WHERE facture_id=? ORDER BY date_paiement");
    $paiementsFac->execute([$id]);
    $paiementsFac = $paiementsFac->fetchAll();
}

if ($print && $facturePrint): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Facture <?= $facturePrint['numero_facture'] ?></title>
  <link rel="stylesheet" href="/couture_senegal/assets/css/style.css">
  <style>
    body { background:#fff; margin:0; padding:20px; }
    @media print { .no-print { display:none; } }
  </style>
</head>
<body>
<div class="no-print" style="text-align:right;margin-bottom:16px">
  <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimer</button>
  <a href="factures.php" class="btn btn-outline">← Retour</a>
</div>
<div class="facture-print">
  <div class="sn-stripe" style="margin-bottom:24px;border-radius:4px"></div>
  <div class="facture-header">
    <div>
      <div class="facture-logo">✂️ CoutureSn Pro</div>
      <div style="margin-top:8px;font-size:.85rem;color:#666">
        <div>Atelier Mode Sénégal</div>
        <div>Dakar, Sénégal</div>
        <div>Powered by Omega Informatique Consulting</div>
      </div>
    </div>
    <div>
      <div class="facture-num"><?= $facturePrint['numero_facture'] ?></div>
      <div style="text-align:right;font-size:.85rem;color:#666;margin-top:6px">
        <div>Émise le : <?= formatDate($facturePrint['date_facture']) ?></div>
        <?php if ($facturePrint['date_echeance']): ?>
        <div>Échéance : <?= formatDate($facturePrint['date_echeance']) ?></div>
        <?php endif; ?>
        <div style="margin-top:6px"><?= badgeStatutFacture($facturePrint['statut']) ?></div>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
    <div style="background:#f5f3ee;padding:16px;border-radius:10px">
      <div style="font-size:.72rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Facturé à</div>
      <div style="font-weight:700;font-size:1.05rem"><?= htmlspecialchars($facturePrint['client_nom']) ?></div>
      <div style="color:#666;font-size:.88rem">
        <?= $facturePrint['telephone'] ?><br>
        <?= $facturePrint['email'] ?><br>
        <?= htmlspecialchars($facturePrint['adresse'] ?? '') ?>, <?= $facturePrint['quartier'] ?><br>
        <?= $facturePrint['ville'] ?>
      </div>
    </div>
    <div style="background:#f5f3ee;padding:16px;border-radius:10px">
      <div style="font-size:.72rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Commande liée</div>
      <div style="font-weight:700;color:var(--primary)"><?= $facturePrint['numero_commande'] ?></div>
      <div style="color:#666;font-size:.88rem;margin-top:6px"><?= nl2br(htmlspecialchars($facturePrint['commande_notes'] ?? '')) ?></div>
    </div>
  </div>

  <!-- Tableau montants -->
  <table style="width:100%;border-collapse:collapse;margin-bottom:20px">
    <thead>
      <tr style="background:#0D1B0F;color:#fff">
        <th style="padding:12px 16px;text-align:left">Description</th>
        <th style="padding:12px 16px;text-align:right">Montant HT</th>
      </tr>
    </thead>
    <tbody>
      <tr><td style="padding:16px;border-bottom:1px solid #eee">Prestations couture — Commande <?= $facturePrint['numero_commande'] ?></td>
          <td style="padding:16px;border-bottom:1px solid #eee;text-align:right"><?= formatMontant($facturePrint['montant_ht']) ?></td></tr>
    </tbody>
  </table>

  <div style="display:flex;justify-content:flex-end">
    <div style="width:280px">
      <?php if ($facturePrint['tva_montant'] > 0): ?>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eee">
        <span>TVA (<?= $facturePrint['tva_pct'] ?>%)</span>
        <span><?= formatMontant($facturePrint['tva_montant']) ?></span>
      </div>
      <?php endif; ?>
      <?php if ($facturePrint['remise'] > 0): ?>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eee;color:var(--rouge)">
        <span>Remise</span>
        <span>-<?= formatMontant($facturePrint['remise']) ?></span>
      </div>
      <?php endif; ?>
      <div class="facture-total-box">
        <div class="total-label">TOTAL TTC</div>
        <div class="total-value"><?= formatMontant($facturePrint['montant_ttc']) ?></div>
      </div>
      <?php if ($facturePrint['montant_paye'] > 0): ?>
      <div style="display:flex;justify-content:space-between;padding:10px 0;color:var(--success);font-weight:600">
        <span>✅ Déjà payé</span>
        <span><?= formatMontant($facturePrint['montant_paye']) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 16px;background:#fff3cd;border-radius:8px;font-weight:700;color:var(--warning)">
        <span>RESTE À PAYER</span>
        <span><?= formatMontant($facturePrint['reste']) ?></span>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($paiementsFac): ?>
  <div style="margin-top:24px;padding-top:20px;border-top:1px dashed #ccc">
    <div style="font-weight:700;margin-bottom:10px;color:#666;font-size:.85rem;text-transform:uppercase">Historique des paiements</div>
    <?php foreach ($paiementsFac as $p): ?>
    <div style="display:flex;justify-content:space-between;font-size:.85rem;padding:6px 0;border-bottom:1px solid #f0f0f0">
      <span><?= formatDate($p['date_paiement']) ?> — <?= ucfirst(str_replace('_',' ',$p['mode_paiement'])) ?></span>
      <strong style="color:var(--success)"><?= formatMontant($p['montant']) ?></strong>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div style="margin-top:32px;padding-top:16px;border-top:1px solid #eee;font-size:.75rem;color:#999;text-align:center">
    <div>CoutureSn Pro — Atelier Mode Sénégal — Dakar</div>
    <div>Système développé par <strong>OMEGA INFORMATIQUE CONSULTING</strong> · www.omega-info.sn</div>
  </div>
  <div class="sn-stripe" style="margin-top:20px;border-radius:4px"></div>
</div>
</body>
</html>
<?php
exit;
endif;

require_once __DIR__ . '/includes/header.php';
?>

<!-- STATS -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px">
  <div class="stat-card gold">
    <div class="stat-icon" style="color:var(--or)">📤</div>
    <div class="stat-value" style="color:var(--or);font-size:1.2rem"><?= formatMontant($totalFacture) ?></div>
    <div class="stat-label">Total Facturé</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon" style="color:var(--vert)">✅</div>
    <div class="stat-value" style="color:var(--vert);font-size:1.2rem"><?= formatMontant($totalPaye) ?></div>
    <div class="stat-label">Total Encaissé</div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon" style="color:var(--rouge)">⏳</div>
    <div class="stat-value" style="color:var(--rouge);font-size:1.2rem"><?= formatMontant($totalReste) ?></div>
    <div class="stat-label">Reste À Encaisser</div>
  </div>
</div>

<!-- FILTRES & ACTIONS -->
<div style="display:flex;gap:12px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <form method="GET">
      <input type="text" name="q" placeholder="N° facture ou client..." value="<?= htmlspecialchars($search) ?>" style="width:240px">
    </form>
  </div>
  <select onchange="location.href='?statut='+this.value" style="max-width:180px">
    <option value="">Tous statuts</option>
    <?php foreach (['draft','émise','payée_partiel','payée','annulée'] as $s): ?>
    <option value="<?= $s ?>" <?= $filterStatut===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
    <?php endforeach; ?>
  </select>
  <div style="margin-left:auto">
    <button class="btn btn-primary" data-modal="modalFac">📄 Nouvelle Facture</button>
  </div>
</div>

<!-- TABLEAU -->
<div class="card slide-up">
  <div class="card-header">
    <div class="card-title">📄 Factures <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">(<?= count($factures) ?>)</span></div>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead>
        <tr>
          <th>N° Facture</th>
          <th>N° Commande</th>
          <th>Client</th>
          <th>Date</th>
          <th>Échéance</th>
          <th>Statut</th>
          <th>Total TTC</th>
          <th>Payé</th>
          <th>Reste</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($factures as $f):
          $overdue = $f['date_echeance'] && $f['date_echeance'] < date('Y-m-d') && !in_array($f['statut'],['payée','annulée']);
        ?>
        <tr <?= $overdue ? 'style="background:rgba(193,18,31,.04)"' : '' ?>>
          <td><strong style="color:var(--or);font-family:monospace"><?= $f['numero_facture'] ?></strong></td>
          <td style="font-size:.82rem;color:var(--primary)"><?= $f['numero_commande'] ?></td>
          <td><?= htmlspecialchars($f['client_nom']) ?></td>
          <td><?= formatDate($f['date_facture']) ?></td>
          <td style="color:<?= $overdue ? 'var(--rouge)' : 'inherit' ?>">
            <?= $f['date_echeance'] ? formatDate($f['date_echeance']) : '-' ?>
            <?= $overdue ? '⚠️' : '' ?>
          </td>
          <td><?= badgeStatutFacture($f['statut']) ?></td>
          <td><strong><?= formatMontant($f['montant_ttc']) ?></strong></td>
          <td style="color:var(--success)"><?= formatMontant($f['montant_paye']) ?></td>
          <td>
            <?php if ($f['reste'] > 0): ?>
            <strong style="color:var(--rouge)"><?= formatMontant($f['reste']) ?></strong>
            <?php else: ?>
            <span style="color:var(--success)">✅ Soldé</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="td-actions">
              <a class="action-btn" href="?print=1&id=<?= $f['id'] ?>" target="_blank" title="Imprimer">🖨️</a>
              <?php if ($f['reste'] > 0): ?>
              <button class="action-btn" title="Paiement" onclick='openPaiement(<?= json_encode($f) ?>)'>💰</button>
              <?php endif; ?>
              <button class="action-btn" title="Modifier" onclick='editFac(<?= htmlspecialchars(json_encode($f)) ?>)'>✏️</button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="form_action" value="delete">
                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                <button type="submit" class="action-btn danger confirm-delete" title="Annuler">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($factures)): ?>
        <tr><td colspan="10"><div class="empty-state"><div class="empty-icon">📄</div><p>Aucune facture</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL NOUVELLE FACTURE -->
<div class="modal-overlay" id="modalFac">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modalFacTitle">📄 Nouvelle Facture</div>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="form_action" id="facFormAction" value="create">
      <input type="hidden" name="id" id="facId">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group full">
            <label>Commande *</label>
            <select name="commande_id" id="facCmdId" required onchange="remplirClient(this)">
              <option value="">-- Sélectionner une commande --</option>
              <?php foreach ($allCmds as $cm): ?>
              <option value="<?= $cm['id'] ?>" data-client="<?= $cm['cid'] ?>" data-total="<?= $cm['total_ttc'] ?>">
                <?= $cm['numero_commande'] ?> — <?= htmlspecialchars($cm['client_nom']) ?> (<?= formatMontant($cm['total_ttc']) ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <input type="hidden" name="client_id" id="facClientId">
          <div class="form-group">
            <label>Date Facture *</label>
            <input type="date" name="date_facture" id="facDate" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Date Échéance</label>
            <input type="date" name="date_echeance" id="facEcheance">
          </div>
          <div class="form-group">
            <label>Montant HT (FCFA)</label>
            <input type="number" name="montant_ht" id="facHt" min="0" step="500" oninput="calcFac()">
          </div>
          <div class="form-group">
            <label>TVA (%)</label>
            <input type="number" name="tva_pct" id="facTva" value="0" min="0" max="30" oninput="calcFac()">
          </div>
          <div class="form-group">
            <label>Remise (FCFA)</label>
            <input type="number" name="remise" id="facRemise" value="0" min="0" oninput="calcFac()">
          </div>
          <div class="form-group">
            <label>Total TTC</label>
            <input type="text" id="facTtc" readonly style="background:var(--gris-clair);font-weight:700;color:var(--primary)">
          </div>
          <div class="form-group" id="facStatutGroup" style="display:none">
            <label>Statut</label>
            <select name="statut" id="facStatut">
              <?php foreach (['émise','payée_partiel','payée','annulée'] as $s): ?>
              <option value="<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group full">
            <label>Notes</label>
            <textarea name="notes" id="facNotes" placeholder="Conditions de paiement, remarques..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-close">Annuler</button>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL PAIEMENT -->
<div class="modal-overlay" id="modalPay">
  <div class="modal" style="max-width:460px">
    <div class="modal-header">
      <div class="modal-title">💰 Enregistrer Paiement</div>
      <button class="modal-close">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="form_action" value="paiement">
      <input type="hidden" name="facture_id" id="payFacId">
      <div class="modal-body">
        <div id="payInfo" style="background:var(--gris-clair);padding:14px;border-radius:8px;margin-bottom:18px;font-size:.88rem"></div>
        <div class="form-grid">
          <div class="form-group">
            <label>Date Paiement *</label>
            <input type="date" name="date_paiement" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Montant *</label>
            <input type="number" name="montant" id="payMontant" min="1" step="500" required>
          </div>
          <div class="form-group full">
            <label>Mode de Paiement</label>
            <select name="mode_paiement">
              <?php foreach (['espèces','wave','orange_money','free_money','virement','chèque','autre'] as $m): ?>
              <option value="<?= $m ?>"><?= ucfirst(str_replace('_',' ',$m)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Référence transaction</label>
            <input type="text" name="reference" placeholder="WAVE-XXXX">
          </div>
          <div class="form-group">
            <label>Notes</label>
            <input type="text" name="notes_paiement">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-close">Annuler</button>
        <button type="submit" class="btn btn-gold">✅ Confirmer Paiement</button>
      </div>
    </form>
  </div>
</div>

<script>
function calcFac() {
  const ht  = parseFloat(document.getElementById('facHt').value) || 0;
  const tva = parseFloat(document.getElementById('facTva').value) || 0;
  const rem = parseFloat(document.getElementById('facRemise').value) || 0;
  const ttc = ht + (ht * tva / 100) - rem;
  document.getElementById('facTtc').value = ttc.toLocaleString('fr-SN') + ' FCFA';
}

function remplirClient(sel) {
  const opt = sel.options[sel.selectedIndex];
  document.getElementById('facClientId').value = opt.dataset.client || '';
  const tot = parseFloat(opt.dataset.total) || 0;
  document.getElementById('facHt').value = tot;
  calcFac();
}

function editFac(f) {
  document.getElementById('facFormAction').value = 'update';
  document.getElementById('facId').value = f.id;
  document.getElementById('modalFacTitle').textContent = '✏️ Modifier Facture';
  document.getElementById('facStatutGroup').style.display = 'block';
  document.getElementById('facStatut').value = f.statut;
  document.getElementById('facEcheance').value = f.date_echeance || '';
  document.getElementById('facHt').value = f.montant_ht;
  document.getElementById('facTva').value = f.tva_pct;
  document.getElementById('facRemise').value = f.remise;
  document.getElementById('facNotes').value = f.notes || '';
  calcFac();
  document.getElementById('modalFac').classList.add('open');
}

function openPaiement(f) {
  document.getElementById('payFacId').value = f.id;
  document.getElementById('payMontant').value = f.reste;
  document.getElementById('payInfo').innerHTML =
    `<strong>${f.numero_facture}</strong> · Reste : <strong style="color:var(--rouge)">${parseFloat(f.reste).toLocaleString('fr-SN')} FCFA</strong>`;
  document.getElementById('modalPay').classList.add('open');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
