<?php
require 'config/db.php';
$page_title = 'Tableau de Bord';
require 'includes/header.php';

// KPIs
$total_vols      = $pdo->query("SELECT COUNT(*) FROM vols")->fetchColumn();
$vols_en_cours   = $pdo->query("SELECT COUNT(*) FROM vols WHERE statut='EN_COURS'")->fetchColumn();
$total_clients   = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$total_res       = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$ca_total        = $pdo->query("SELECT COALESCE(SUM(prix_total),0) FROM reservations WHERE statut IN('CONFIRMEE','PAYEE')")->fetchColumn();
$res_mois        = $pdo->query("SELECT COUNT(*) FROM reservations WHERE MONTH(date_reservation)=MONTH(NOW())")->fetchColumn();

// Prochains vols
$prochains = $pdo->query("
  SELECT v.*, c.nom as compagnie, o.ville as origine_v, d.ville as dest_v,
         o.code_iata as orig_code, d.code_iata as dest_code
  FROM vols v
  LEFT JOIN compagnies_aeriennes c ON v.compagnie_id=c.id
  LEFT JOIN destinations o ON v.origine_id=o.id
  LEFT JOIN destinations d ON v.destination_id=d.id
  WHERE v.date_depart >= NOW()
  ORDER BY v.date_depart ASC LIMIT 6
")->fetchAll();

// Dernières réservations
$dernieres = $pdo->query("
  SELECT r.*, CONCAT(cl.prenom,' ',cl.nom) as client_nom,
         CONCAT(o.code_iata,' → ',d.code_iata) as trajet,
         v.numero_vol
  FROM reservations r
  LEFT JOIN clients cl ON r.client_id=cl.id
  LEFT JOIN vols v ON r.vol_id=v.id
  LEFT JOIN destinations o ON v.origine_id=o.id
  LEFT JOIN destinations d ON v.destination_id=d.id
  ORDER BY r.date_reservation DESC LIMIT 7
")->fetchAll();

function statut_badge(string $s): string {
    $map = [
        'PROGRAMME' => 'programme','EN_COURS' => 'en_cours','ARRIVE' => 'arrive',
        'ANNULE' => 'annule','RETARDE' => 'retarde',
        'PAYEE' => 'payee','CONFIRMEE' => 'confirmee','EN_ATTENTE' => 'en_attente',
        'ANNULEE' => 'annule',
    ];
    $labels = [
        'PROGRAMME' => '▷ Programmé','EN_COURS' => '▶ En vol','ARRIVE' => '✓ Arrivé',
        'ANNULE' => '✕ Annulé','RETARDE' => '⚠ Retardé',
        'PAYEE' => '✓ Payée','CONFIRMEE' => '◈ Confirmée','EN_ATTENTE' => '○ Attente',
        'ANNULEE' => '✕ Annulée',
    ];
    $cls = $map[$s] ?? 'en_attente';
    $lbl = $labels[$s] ?? $s;
    return "<span class=\"badge badge-{$cls} badge-dot\">{$lbl}</span>";
}
?>
<?php require 'includes/navbar.php'; ?>

<div class="page">
  <!-- Hero -->
  <div style="margin-bottom:32px;padding:32px 28px;background:linear-gradient(135deg,rgba(212,168,72,0.08) 0%,transparent 60%),var(--card);border:1px solid var(--border);border-radius:16px;position:relative;overflow:hidden">
    <div style="position:absolute;right:-20px;top:-20px;font-size:8rem;opacity:0.04;transform:rotate(-15deg);pointer-events:none">✈</div>
    <div style="font-size:0.7rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gold);margin-bottom:6px">Bienvenue sur la plateforme</div>
    <div style="font-family:'Bebas Neue',sans-serif;font-size:2.4rem;letter-spacing:0.04em;color:white;line-height:1">
      OMEGA <span style="color:var(--gold)">AGENCE VOYAGE</span>
    </div>
    <p style="margin-top:8px;color:var(--muted);font-size:0.88rem">Gestion complète des vols, réservations et clients · Dakar, Sénégal</p>
    <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap">
      <a href="ajouter_vol.php" class="btn btn-gold">✈ Nouveau vol</a>
      <a href="ajouter_reservation.php" class="btn btn-ghost">+ Réservation</a>
      <a href="ajouter_client.php" class="btn btn-ghost">+ Client</a>
    </div>
  </div>

  <!-- KPIs -->
  <div class="stats-grid">
    <div class="stat-card gold">
      <span class="stat-icon">✈</span>
      <div class="stat-label">Total Vols</div>
      <div class="stat-value" data-count="<?= $total_vols ?>">0</div>
      <div class="stat-sub"><?= $vols_en_cours ?> en vol actuellement</div>
    </div>
    <div class="stat-card cyan">
      <span class="stat-icon">📋</span>
      <div class="stat-label">Réservations</div>
      <div class="stat-value" data-count="<?= $total_res ?>">0</div>
      <div class="stat-sub"><?= $res_mois ?> ce mois-ci</div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">👤</span>
      <div class="stat-label">Clients</div>
      <div class="stat-value" data-count="<?= $total_clients ?>">0</div>
      <div class="stat-sub">Base clients enregistrés</div>
    </div>
    <div class="stat-card green">
      <span class="stat-icon">💰</span>
      <div class="stat-label">Chiffre d'Affaires</div>
      <div class="stat-value" style="font-size:1.3rem"><?= money($ca_total) ?></div>
      <div class="stat-sub">Confirmé + Payé</div>
    </div>
  </div>

  <div class="grid-2" style="gap:20px;margin-bottom:24px">
    <!-- Prochains départs -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">✈ Prochains Départs</span>
        <a href="vols.php" class="btn btn-ghost btn-sm">Tous les vols →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>Vol</th><th>Trajet</th><th>Départ</th><th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach($prochains as $v): ?>
          <tr>
            <td>
              <div style="font-weight:700;color:var(--text)"><?= htmlspecialchars($v['numero_vol']) ?></div>
              <div style="font-size:0.72rem;color:var(--muted)"><?= htmlspecialchars($v['compagnie'] ?? '') ?></div>
            </td>
            <td>
              <div class="route-display">
                <span class="route-code"><?= $v['orig_code'] ?></span>
                <span class="route-arrow">→</span>
                <span class="route-code"><?= $v['dest_code'] ?></span>
              </div>
            </td>
            <td>
              <div style="font-size:0.8rem"><?= date('d M', strtotime($v['date_depart'])) ?></div>
              <div style="font-size:0.7rem;color:var(--muted)"><?= date('H:i', strtotime($v['date_depart'])) ?></div>
            </td>
            <td><?= statut_badge($v['statut']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Dernières réservations -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">📋 Dernières Réservations</span>
        <a href="reservations.php" class="btn btn-ghost btn-sm">Toutes →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>Réf.</th><th>Client</th><th>Vol</th><th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach($dernieres as $r): ?>
          <tr>
            <td style="font-family:'Bebas Neue',sans-serif;font-size:0.85rem;letter-spacing:0.04em;color:var(--gold)"><?= $r['reference'] ?></td>
            <td style="font-size:0.82rem"><?= htmlspecialchars($r['client_nom'] ?? '—') ?></td>
            <td style="font-size:0.78rem;color:var(--muted)"><?= htmlspecialchars($r['trajet'] ?? '—') ?></td>
            <td><?= statut_badge($r['statut']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
