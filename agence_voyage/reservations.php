<?php
require 'config/db.php';
$page_title = 'Réservations';
require 'includes/header.php';

$filtre = $_GET['statut'] ?? '';
$sql = "SELECT r.*, CONCAT(cl.prenom,' ',cl.nom) as client_nom, cl.telephone as client_tel,
               v.numero_vol, c.nom as compagnie, c.code_iata as comp_iata,
               o.code_iata as orig_code, d.code_iata as dest_code,
               o.ville as orig_v, d.ville as dest_v
        FROM reservations r
        LEFT JOIN clients cl ON r.client_id=cl.id
        LEFT JOIN vols v ON r.vol_id=v.id
        LEFT JOIN compagnies_aeriennes c ON v.compagnie_id=c.id
        LEFT JOIN destinations o ON v.origine_id=o.id
        LEFT JOIN destinations d ON v.destination_id=d.id";
$params = [];
if ($filtre) { $sql .= " WHERE r.statut=:s"; $params[':s']=$filtre; }
$sql .= " ORDER BY r.date_reservation DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $reservations = $stmt->fetchAll();

function badge(string $s): string {
    $map=['PAYEE'=>'payee','CONFIRMEE'=>'confirmee','EN_ATTENTE'=>'en_attente','ANNULEE'=>'annule'];
    $labels=['PAYEE'=>'✓ Payée','CONFIRMEE'=>'◈ Confirmée','EN_ATTENTE'=>'○ En attente','ANNULEE'=>'✕ Annulée'];
    return "<span class='badge badge-".($map[$s]??'en_attente')." badge-dot'>".($labels[$s]??$s)."</span>";
}
function classe_badge(string $c): string {
    $m=['ECONOMIQUE'=>'économique','BUSINESS'=>'business','PREMIERE'=>'premiere'];
    return "<span class='badge badge-".($m[$c]??'economique')."'>$c</span>";
}
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Gestion</div>
      <h1>Réservations</h1>
      <p><?= count($reservations) ?> réservation(s)</p>
    </div>
    <a href="ajouter_reservation.php" class="btn btn-gold">+ Nouvelle Réservation</a>
  </div>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
    <?php foreach(['' => 'Toutes', 'EN_ATTENTE' => 'En attente', 'CONFIRMEE' => 'Confirmées', 'PAYEE' => 'Payées', 'ANNULEE' => 'Annulées'] as $v => $l): ?>
    <a href="?statut=<?= $v ?>"
       style="padding:6px 14px;border-radius:8px;font-size:0.76rem;font-weight:600;text-decoration:none;transition:all 0.2s;
              <?= $filtre===$v ? 'background:rgba(212,168,72,0.15);color:var(--gold);border:1px solid rgba(212,168,72,0.3)' : 'background:var(--card);color:var(--muted);border:1px solid var(--border)' ?>">
      <?= $l ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Référence</th><th>Client</th><th>Vol / Trajet</th>
          <th>Classe</th><th>Pax</th><th>Total</th><th>Statut</th><th>Date</th>
        </tr></thead>
        <tbody>
        <?php if(empty($reservations)): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📋</div>Aucune réservation</div></td></tr>
        <?php else: foreach($reservations as $r): ?>
        <tr>
          <td style="font-family:'Bebas Neue',sans-serif;font-size:0.85rem;letter-spacing:0.04em;color:var(--gold)"><?= $r['reference'] ?></td>
          <td>
            <div style="font-weight:600;font-size:0.85rem"><?= htmlspecialchars($r['client_nom']??'—') ?></div>
            <div style="font-size:0.7rem;color:var(--muted)"><?= htmlspecialchars($r['client_tel']??'') ?></div>
          </td>
          <td>
            <div style="font-weight:600;font-size:0.82rem"><?= htmlspecialchars($r['numero_vol']??'—') ?></div>
            <?php if($r['orig_code'] && $r['dest_code']): ?>
            <div class="route-display" style="margin-top:3px">
              <span class="route-code" style="font-size:0.75rem"><?= $r['orig_code'] ?></span>
              <span class="route-arrow" style="font-size:0.8rem">→</span>
              <span class="route-code" style="font-size:0.75rem"><?= $r['dest_code'] ?></span>
            </div>
            <?php endif; ?>
          </td>
          <td><?= classe_badge($r['classe']) ?></td>
          <td style="text-align:center;font-weight:700;color:var(--text)"><?= $r['nb_passagers'] ?></td>
          <td style="font-weight:700;color:var(--gold);font-size:0.85rem"><?= money($r['prix_total']) ?></td>
          <td><?= badge($r['statut']) ?></td>
          <td style="font-size:0.75rem;color:var(--muted)"><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
