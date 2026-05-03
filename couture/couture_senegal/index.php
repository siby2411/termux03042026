<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// ── Stats générales ────────────────────────────────────
$stats = [];

$stats['clients'] = $pdo->query("SELECT COUNT(*) FROM clients WHERE statut='actif'")->fetchColumn();
$stats['commandes_actives'] = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut NOT IN ('livrée','annulée')")->fetchColumn();
$stats['ca_mois'] = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE MONTH(date_paiement)=MONTH(CURDATE()) AND YEAR(date_paiement)=YEAR(CURDATE())")->fetchColumn();
$stats['impaye'] = $pdo->query("SELECT COALESCE(SUM(reste),0) FROM factures WHERE statut IN ('émise','payée_partiel')")->fetchColumn();
$stats['total_facture_annee'] = $pdo->query("SELECT COALESCE(SUM(montant_ttc),0) FROM factures WHERE YEAR(date_facture)=YEAR(CURDATE())")->fetchColumn();
$stats['depenses_mois'] = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM depenses WHERE MONTH(date_dep)=MONTH(CURDATE()) AND YEAR(date_dep)=YEAR(CURDATE())")->fetchColumn();

// ── Dernières commandes ────────────────────────────────
$dernieresCmd = $pdo->query("
  SELECT c.*, CONCAT(cl.prenom,' ',cl.nom) AS client_nom
  FROM commandes c
  JOIN clients cl ON cl.id = c.client_id
  ORDER BY c.created_at DESC LIMIT 8
")->fetchAll();

// ── Commandes par statut ───────────────────────────────
$statuts = $pdo->query("
  SELECT statut, COUNT(*) as nb, SUM(total_ttc) as total
  FROM commandes GROUP BY statut
")->fetchAll();

// ── Derniers paiements ─────────────────────────────────
$derniersPaiements = $pdo->query("
  SELECT p.*, CONCAT(cl.prenom,' ',cl.nom) AS client_nom, f.numero_facture
  FROM paiements p
  JOIN clients cl ON cl.id = p.client_id
  JOIN factures f ON f.id = p.facture_id
  ORDER BY p.date_paiement DESC LIMIT 5
")->fetchAll();

// ── Factures en attente ────────────────────────────────
$facturesAttente = $pdo->query("
  SELECT f.*, CONCAT(cl.prenom,' ',cl.nom) AS client_nom
  FROM factures f
  JOIN clients cl ON cl.id = f.client_id
  WHERE f.statut IN ('émise','payée_partiel')
  ORDER BY f.date_echeance ASC LIMIT 5
")->fetchAll();

// CA mensuel (12 derniers mois)
$caMensuel = $pdo->query("
  SELECT DATE_FORMAT(date_paiement,'%b %Y') as mois,
         MONTH(date_paiement) as m,
         YEAR(date_paiement) as y,
         SUM(montant) as total
  FROM paiements
  WHERE date_paiement >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
  GROUP BY YEAR(date_paiement), MONTH(date_paiement)
  ORDER BY y,m
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- STATS CARDS -->
<div class="stats-grid">
  <div class="stat-card green">
    <div class="stat-icon" style="color:var(--vert)">👥</div>
    <div class="stat-value" style="color:var(--vert)"><?= number_format($stats['clients']) ?></div>
    <div class="stat-label">Clients Actifs</div>
    <div class="stat-trend">↗ Fichier clients</div>
  </div>
  <div class="stat-card gold">
    <div class="stat-icon" style="color:var(--or)">🧵</div>
    <div class="stat-value" style="color:var(--or)"><?= number_format($stats['commandes_actives']) ?></div>
    <div class="stat-label">Commandes En Cours</div>
    <div class="stat-trend">📋 À traiter</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon" style="color:var(--vert)">💰</div>
    <div class="stat-value" style="color:var(--vert);font-size:1.3rem"><?= formatMontant($stats['ca_mois']) ?></div>
    <div class="stat-label">Encaissé Ce Mois</div>
    <div class="stat-trend">✅ Paiements reçus</div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon" style="color:var(--rouge)">⏳</div>
    <div class="stat-value" style="color:var(--rouge);font-size:1.3rem"><?= formatMontant($stats['impaye']) ?></div>
    <div class="stat-label">Reste À Encaisser</div>
    <div class="stat-trend">⚠️ Factures ouvertes</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;margin-bottom:24px">

  <!-- DERNIÈRES COMMANDES -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🧵 Dernières Commandes</div>
      <a href="commandes.php" class="btn btn-outline btn-sm">Voir tout</a>
    </div>
    <div class="card-body table-wrap">
      <table>
        <thead>
          <tr>
            <th>N° Commande</th>
            <th>Client</th>
            <th>Statut</th>
            <th>Priorité</th>
            <th>Livraison</th>
            <th>Montant</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dernieresCmd as $cmd): ?>
          <tr>
            <td><strong style="color:var(--primary)"><?= $cmd['numero_commande'] ?></strong></td>
            <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
            <td><?= badgeStatutCommande($cmd['statut']) ?></td>
            <td>
              <span class="badge badge-priorite-<?= $cmd['priorite'] ?>">
                <?= $cmd['priorite'] === 'vip' ? '⭐ VIP' : ($cmd['priorite'] === 'urgente' ? '🔴 Urgente' : '● Normale') ?>
              </span>
            </td>
            <td><?= $cmd['date_livraison'] ? formatDate($cmd['date_livraison']) : '-' ?></td>
            <td><strong><?= formatMontant($cmd['total_ttc']) ?></strong></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($dernieresCmd)): ?>
          <tr><td colspan="6" class="empty-state"><p>Aucune commande</p></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- PANEL DROIT -->
  <div style="display:flex;flex-direction:column;gap:20px">

    <!-- Widget CA annuel -->
    <div class="finance-widget">
      <div class="finance-label">Chiffre d'Affaires <?= date('Y') ?></div>
      <div class="finance-value"><?= formatMontant($stats['total_facture_annee']) ?></div>
      <div style="margin-top:12px;font-size:.78rem;opacity:.6">
        Dépenses ce mois : <?= formatMontant($stats['depenses_mois']) ?>
      </div>
      <div style="margin-top:10px">
        <a href="finances.php" class="btn btn-gold btn-sm">📈 Voir le bilan</a>
      </div>
    </div>

    <!-- Répartition statuts -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">📊 Statuts Commandes</div>
      </div>
      <div class="card-body" style="padding:16px 20px">
        <?php foreach ($statuts as $s):
          $total_all = array_sum(array_column($statuts, 'nb')) ?: 1;
          $pct = round($s['nb'] / $total_all * 100);
        ?>
        <div style="margin-bottom:12px">
          <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:4px">
            <span><?= badgeStatutCommande($s['statut']) ?></span>
            <span><strong><?= $s['nb'] ?></strong> (<?= $pct ?>%)</span>
          </div>
          <div class="progress-bar-wrap">
            <div class="progress-bar progress-green" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Actions rapides -->
    <div class="card">
      <div class="card-header"><div class="card-title">⚡ Actions Rapides</div></div>
      <div class="card-body" style="padding:16px;display:flex;flex-direction:column;gap:8px">
        <a href="clients.php?action=new"   class="btn btn-primary">➕ Nouveau Client</a>
        <a href="commandes.php?action=new" class="btn btn-gold">🧵 Nouvelle Commande</a>
        <a href="factures.php?action=new"  class="btn btn-outline">📄 Créer Facture</a>
        <a href="paiements.php?action=new" class="btn btn-outline">💰 Enregistrer Paiement</a>
      </div>
    </div>
  </div>
</div>

<!-- FACTURES EN ATTENTE -->
<div class="card">
  <div class="card-header">
    <div class="card-title">⏳ Factures En Attente de Paiement</div>
    <a href="factures.php" class="btn btn-outline btn-sm">Voir tout</a>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead>
        <tr>
          <th>N° Facture</th>
          <th>Client</th>
          <th>Émise le</th>
          <th>Échéance</th>
          <th>Statut</th>
          <th>Total TTC</th>
          <th>Payé</th>
          <th>Reste</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($facturesAttente as $f):
          $overdue = $f['date_echeance'] && $f['date_echeance'] < date('Y-m-d');
        ?>
        <tr <?= $overdue ? 'style="background:rgba(193,18,31,.04)"' : '' ?>>
          <td><strong style="color:var(--or)"><?= $f['numero_facture'] ?></strong></td>
          <td><?= htmlspecialchars($f['client_nom']) ?></td>
          <td><?= formatDate($f['date_facture']) ?></td>
          <td style="color:<?= $overdue ? 'var(--rouge)' : 'inherit' ?>">
            <?= $f['date_echeance'] ? formatDate($f['date_echeance']) : '-' ?>
            <?= $overdue ? ' ⚠️' : '' ?>
          </td>
          <td><?= badgeStatutFacture($f['statut']) ?></td>
          <td><?= formatMontant($f['montant_ttc']) ?></td>
          <td style="color:var(--success)"><?= formatMontant($f['montant_paye']) ?></td>
          <td><strong style="color:var(--rouge)"><?= formatMontant($f['reste']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($facturesAttente)): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🎉</div><p>Toutes les factures sont payées!</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
