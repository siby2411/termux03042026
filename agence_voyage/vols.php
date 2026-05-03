<?php
require 'config/db.php';
$page_title = 'Gestion des Vols';
require 'includes/header.php';

$filtre = $_GET['statut'] ?? '';
$sql = "SELECT v.*, c.nom as compagnie, c.code_iata as comp_iata,
               o.ville as orig_v, o.code_iata as orig_code,
               d.ville as dest_v, d.code_iata as dest_code
        FROM vols v
        LEFT JOIN compagnies_aeriennes c ON v.compagnie_id=c.id
        LEFT JOIN destinations o ON v.origine_id=o.id
        LEFT JOIN destinations d ON v.destination_id=d.id";
$params = [];
if ($filtre) { $sql .= " WHERE v.statut = :s"; $params[':s'] = $filtre; }
$sql .= " ORDER BY v.date_depart ASC";
$vols = $pdo->prepare($sql); $vols->execute($params); $vols = $vols->fetchAll();

$statuts = ['','PROGRAMME','EN_COURS','ARRIVE','RETARDE','ANNULE'];
$labels_s = ['' => 'Tous', 'PROGRAMME' => 'Programmés', 'EN_COURS' => 'En vol',
             'ARRIVE' => 'Arrivés', 'RETARDE' => 'Retardés', 'ANNULE' => 'Annulés'];

function statut_badge(string $s): string {
    $map=['PROGRAMME'=>'programme','EN_COURS'=>'en_cours','ARRIVE'=>'arrive','ANNULE'=>'annule','RETARDE'=>'retarde'];
    $labels=['PROGRAMME'=>'▷ Programmé','EN_COURS'=>'▶ En vol','ARRIVE'=>'✓ Arrivé','ANNULE'=>'✕ Annulé','RETARDE'=>'⚠ Retardé'];
    return "<span class=\"badge badge-".($map[$s]??'en_attente')." badge-dot\">".($labels[$s]??$s)."</span>";
}
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Gestion</div>
      <h1>Vols</h1>
      <p><?= count($vols) ?> vol(s) enregistré(s)</p>
    </div>
    <a href="ajouter_vol.php" class="btn btn-gold">✈ Ajouter un vol</a>
  </div>

  <!-- Filtres statut -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
    <?php foreach($statuts as $s): ?>
    <a href="?statut=<?= $s ?>"
       style="padding:6px 14px;border-radius:8px;font-size:0.76rem;font-weight:600;text-decoration:none;transition:all 0.2s;
              <?= $filtre===$s ? 'background:rgba(212,168,72,0.15);color:var(--gold);border:1px solid rgba(212,168,72,0.3)' : 'background:var(--card);color:var(--muted);border:1px solid var(--border)' ?>">
      <?= $labels_s[$s] ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Vol</th><th>Compagnie</th><th>Trajet</th>
          <th>Départ</th><th>Arrivée</th><th>Appareil</th>
          <th>Prix Éco</th><th>Statut</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php if(empty($vols)): ?>
        <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">✈</div>Aucun vol trouvé</div></td></tr>
        <?php else: foreach($vols as $v): ?>
        <tr>
          <td><strong style="color:var(--text);font-weight:700"><?= htmlspecialchars($v['numero_vol']) ?></strong></td>
          <td>
            <span style="background:rgba(255,255,255,0.06);padding:2px 7px;border-radius:5px;font-family:'Bebas Neue',sans-serif;font-size:0.9rem;letter-spacing:0.06em"><?= htmlspecialchars($v['comp_iata']??'??') ?></span>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:2px"><?= htmlspecialchars($v['compagnie']??'') ?></div>
          </td>
          <td>
            <div class="route-display">
              <span class="route-code"><?= $v['orig_code'] ?></span>
              <span class="route-arrow">→</span>
              <span class="route-code"><?= $v['dest_code'] ?></span>
            </div>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:2px"><?= htmlspecialchars($v['orig_v']??'') ?> → <?= htmlspecialchars($v['dest_v']??'') ?></div>
          </td>
          <td>
            <div style="font-size:0.82rem"><?= date('d/m/Y', strtotime($v['date_depart'])) ?></div>
            <div style="font-size:0.7rem;color:var(--muted)"><?= date('H:i', strtotime($v['date_depart'])) ?></div>
          </td>
          <td>
            <div style="font-size:0.82rem"><?= date('d/m/Y', strtotime($v['date_arrivee'])) ?></div>
            <div style="font-size:0.7rem;color:var(--muted)"><?= date('H:i', strtotime($v['date_arrivee'])) ?></div>
          </td>
          <td style="font-size:0.78rem;color:var(--muted)"><?= htmlspecialchars($v['type_appareil']) ?></td>
          <td style="color:var(--gold);font-weight:600;font-size:0.82rem"><?= $v['prix_eco'] > 0 ? money($v['prix_eco']) : '—' ?></td>
          <td><?= statut_badge($v['statut']) ?></td>
          <td>
            <a href="ajouter_reservation.php?vol_id=<?= $v['id'] ?>" class="btn btn-ghost btn-sm btn-icon" title="Réserver">+📋</a>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
