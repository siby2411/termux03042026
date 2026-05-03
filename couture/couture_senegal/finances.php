<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

$annee  = (int)($_GET['annee'] ?? date('Y'));
$moisFlt = (int)($_GET['mois'] ?? 0);

// ── CHIFFRE D'AFFAIRES PAR MOIS ──────────────────────
$caParMois = $pdo->prepare("
  SELECT MONTH(date_paiement) as m, MONTHNAME(date_paiement) as mois_nom,
         SUM(montant) as ca
  FROM paiements
  WHERE YEAR(date_paiement) = ?
  GROUP BY MONTH(date_paiement)
  ORDER BY MONTH(date_paiement)
");
$caParMois->execute([$annee]);
$caParMois = $caParMois->fetchAll();

$caData   = array_fill(1, 12, 0);
$moisNoms = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
foreach ($caParMois as $r) $caData[$r['m']] = (float)$r['ca'];

// ── DÉPENSES PAR MOIS ────────────────────────────────
$depParMois = $pdo->prepare("
  SELECT MONTH(date_dep) as m, SUM(montant) as total
  FROM depenses WHERE YEAR(date_dep) = ?
  GROUP BY MONTH(date_dep)
");
$depParMois->execute([$annee]);
$depParMois = $depParMois->fetchAll();
$depData = array_fill(1, 12, 0);
foreach ($depParMois as $r) $depData[$r['m']] = (float)$r['total'];

// ── TOTAUX ANNUELS ────────────────────────────────────
$totalCA   = array_sum($caData);
$totalDep  = array_sum($depData);
$benefice  = $totalCA - $totalDep;
$margeNet  = $totalCA > 0 ? round($benefice / $totalCA * 100, 1) : 0;

// ── DÉPENSES PAR CATÉGORIE ────────────────────────────
$depCat = $pdo->prepare("
  SELECT categorie, SUM(montant) as total, COUNT(*) as nb
  FROM depenses WHERE YEAR(date_dep) = ?
  GROUP BY categorie ORDER BY total DESC
");
$depCat->execute([$annee]);
$depCat = $depCat->fetchAll();

// ── MEILLEURS CLIENTS ─────────────────────────────────
$topClients = $pdo->prepare("
  SELECT cl.nom, cl.prenom, cl.telephone,
         SUM(p.montant) as ca_client,
         COUNT(DISTINCT f.commande_id) as nb_cmd
  FROM paiements p
  JOIN factures f ON f.id = p.facture_id
  JOIN clients cl ON cl.id = p.client_id
  WHERE YEAR(p.date_paiement) = ?
  GROUP BY p.client_id
  ORDER BY ca_client DESC LIMIT 5
");
$topClients->execute([$annee]);
$topClients = $topClients->fetchAll();

// ── DERNIÈRES DÉPENSES ────────────────────────────────
$dernieresDep = $pdo->prepare("
  SELECT * FROM depenses
  WHERE YEAR(date_dep) = ?
  ORDER BY date_dep DESC LIMIT 10
");
$dernieresDep->execute([$annee]);
$dernieresDep = $dernieresDep->fetchAll();

// ── FACTURES IMPAYÉES ─────────────────────────────────
$impaye = $pdo->query("
  SELECT COALESCE(SUM(reste),0) FROM factures WHERE statut IN ('émise','payée_partiel')
")->fetchColumn();

$annees = range(date('Y'), date('Y') - 4);

require_once __DIR__ . '/includes/header.php';
?>

<!-- SÉLECTEUR ANNÉE -->
<div style="display:flex;gap:12px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
  <div style="display:flex;gap:8px;align-items:center">
    <label style="font-weight:600;font-size:.85rem;text-transform:uppercase">Année :</label>
    <?php foreach ($annees as $a): ?>
    <a href="?annee=<?= $a ?>"
       class="btn <?= $a == $annee ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $a ?></a>
    <?php endforeach; ?>
  </div>
  <div style="margin-left:auto">
    <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Imprimer Bilan</button>
  </div>
</div>

<!-- WIDGET BILAN -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:28px">
  <div class="finance-widget">
    <div class="finance-label">Chiffre d'Affaires <?= $annee ?></div>
    <div class="finance-value"><?= formatMontant($totalCA) ?></div>
    <div style="margin-top:8px;font-size:.8rem;opacity:.6">Encaissements réels</div>
  </div>
  <div style="background:linear-gradient(135deg,#7c2d12,#c2410c);border-radius:var(--radius-lg);padding:28px;color:#fff;position:relative;overflow:hidden">
    <div style="font-size:.75rem;opacity:.6;text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Dépenses <?= $annee ?></div>
    <div style="font-family:var(--font-display);font-size:2rem;font-weight:900;color:#fca5a5"><?= formatMontant($totalDep) ?></div>
    <div style="margin-top:8px;font-size:.8rem;opacity:.6">Charges & frais</div>
  </div>
  <div style="background:linear-gradient(135deg,<?= $benefice >= 0 ? '#064e3b,#059669' : '#7f1d1d,#dc2626' ?>);border-radius:var(--radius-lg);padding:28px;color:#fff;position:relative;overflow:hidden">
    <div style="font-size:.75rem;opacity:.6;text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Bénéfice Net <?= $annee ?></div>
    <div style="font-family:var(--font-display);font-size:2rem;font-weight:900;color:<?= $benefice >= 0 ? '#6ee7b7' : '#fca5a5' ?>">
      <?= $benefice >= 0 ? '+' : '' ?><?= formatMontant($benefice) ?>
    </div>
    <div style="margin-top:8px;font-size:.8rem;opacity:.6">Marge : <?= $margeNet ?>%</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;margin-bottom:24px">

  <!-- GRAPHIQUE CA VS DÉPENSES -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📊 CA vs Dépenses — <?= $annee ?></div>
    </div>
    <div class="card-body" style="padding:24px">
      <canvas id="chartCA" height="260"></canvas>
    </div>
  </div>

  <!-- DÉPENSES PAR CATÉGORIE -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🧾 Dépenses par Catégorie</div>
    </div>
    <div class="card-body" style="padding:16px 20px">
      <?php if (empty($depCat)): ?>
      <div class="empty-state"><p>Aucune dépense enregistrée</p></div>
      <?php else: ?>
      <?php foreach ($depCat as $dc):
        $pct = $totalDep > 0 ? round($dc['total']/$totalDep*100) : 0;
        $icons = ['tissu'=>'🧵','fourniture'=>'🪡','loyer'=>'🏠','salaire'=>'👩‍💼','electricite'=>'⚡','transport'=>'🚗','marketing'=>'📣','autre'=>'📦'];
        $ico = $icons[$dc['categorie']] ?? '📦';
      ?>
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:4px">
          <span><?= $ico ?> <?= ucfirst($dc['categorie']) ?> (<?= $dc['nb'] ?>)</span>
          <span><strong><?= formatMontant($dc['total']) ?></strong> · <?= $pct ?>%</span>
        </div>
        <div class="progress-bar-wrap">
          <div class="progress-bar progress-red" style="width:<?= $pct ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

  <!-- TOP CLIENTS -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">⭐ Top 5 Clients <?= $annee ?></div>
    </div>
    <div class="card-body table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Commandes</th>
            <th>CA Client</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($topClients as $i => $tc): ?>
          <tr>
            <td>
              <span style="font-family:var(--font-display);font-weight:900;color:<?= ['var(--or-clair)','#aaa','#c96f26'][min($i,2)] ?>;font-size:1.1rem">
                <?= ['🥇','🥈','🥉'][$i] ?? ($i+1) ?>
              </span>
            </td>
            <td>
              <strong><?= htmlspecialchars($tc['nom'].' '.$tc['prenom']) ?></strong>
              <div style="font-size:.75rem;color:var(--text-muted)"><?= $tc['telephone'] ?></div>
            </td>
            <td><span class="badge" style="background:var(--info)"><?= $tc['nb_cmd'] ?></span></td>
            <td><strong style="color:var(--primary)"><?= formatMontant($tc['ca_client']) ?></strong></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($topClients)): ?>
          <tr><td colspan="4"><div class="empty-state"><p>Aucune donnée</p></div></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- DERNIÈRES DÉPENSES -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🧾 Dernières Dépenses</div>
      <a href="depenses.php" class="btn btn-outline btn-sm">Toutes</a>
    </div>
    <div class="card-body table-wrap">
      <table>
        <thead><tr><th>Date</th><th>Catégorie</th><th>Description</th><th>Montant</th></tr></thead>
        <tbody>
          <?php foreach ($dernieresDep as $d): ?>
          <tr>
            <td style="font-size:.8rem"><?= formatDate($d['date_dep']) ?></td>
            <td><span class="badge" style="background:var(--or);font-size:.7rem"><?= ucfirst($d['categorie']) ?></span></td>
            <td style="font-size:.83rem"><?= htmlspecialchars($d['description']) ?></td>
            <td><strong style="color:var(--rouge)"><?= formatMontant($d['montant']) ?></strong></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($dernieresDep)): ?>
          <tr><td colspan="4"><div class="empty-state"><p>Aucune dépense</p></div></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- TABLEAU MENSUEL DÉTAILLÉ -->
<div class="card" style="margin-top:24px">
  <div class="card-header">
    <div class="card-title">📅 Tableau de Bord Mensuel <?= $annee ?></div>
  </div>
  <div class="card-body table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mois</th>
          <th>Encaissements</th>
          <th>Dépenses</th>
          <th>Résultat</th>
          <th>Marge</th>
          <th>Évolution</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $totCA = $totDep = 0;
        for ($m = 1; $m <= 12; $m++):
          $ca  = $caData[$m];
          $dep = $depData[$m];
          $res = $ca - $dep;
          $marge = $ca > 0 ? round($res/$ca*100,1) : 0;
          $totCA  += $ca;
          $totDep += $dep;
          $futur  = mktime(0,0,0,$m,1,$annee) > time();
        ?>
        <tr style="<?= $futur ? 'opacity:.4' : '' ?>">
          <td><strong><?= $moisNoms[$m] ?> <?= $annee ?></strong><?= $futur ? ' <em style="font-size:.75rem;color:var(--gris)">(à venir)</em>' : '' ?></td>
          <td style="color:var(--success)"><?= $ca > 0 ? formatMontant($ca) : '-' ?></td>
          <td style="color:var(--rouge)"><?= $dep > 0 ? formatMontant($dep) : '-' ?></td>
          <td style="font-weight:700;color:<?= $res >= 0 ? 'var(--success)' : 'var(--rouge)' ?>">
            <?= $ca || $dep ? ($res >= 0 ? '+' : '') . formatMontant($res) : '-' ?>
          </td>
          <td><?= $ca > 0 ? $marge . '%' : '-' ?></td>
          <td style="width:120px">
            <?php if ($ca > 0 || $dep > 0):
              $pct = min(100, $ca > 0 ? round($ca/$totalCA*100) : 0);
            ?>
            <div class="progress-bar-wrap">
              <div class="progress-bar <?= $res >= 0 ? 'progress-green' : 'progress-red' ?>" style="width:<?= $pct ?>%"></div>
            </div>
            <?php endif; ?>
          </td>
        </tr>
        <?php endfor; ?>
        <tr style="background:var(--gris-clair);font-weight:700">
          <td>📊 TOTAL <?= $annee ?></td>
          <td style="color:var(--success)"><?= formatMontant($totalCA) ?></td>
          <td style="color:var(--rouge)"><?= formatMontant($totalDep) ?></td>
          <td style="color:<?= $benefice >= 0 ? 'var(--success)' : 'var(--rouge)' ?>"><?= ($benefice >= 0 ? '+' : '') . formatMontant($benefice) ?></td>
          <td><?= $margeNet ?>%</td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode(array_values($moisNoms)) ?>.slice(1);
const caData  = <?= json_encode(array_values($caData)) ?>;
const depData = <?= json_encode(array_values($depData)) ?>;
const benefices = caData.map((c,i) => c - depData[i]);

const ctx = document.getElementById('chartCA').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'Encaissements',
        data: caData,
        backgroundColor: 'rgba(0,133,63,.75)',
        borderRadius: 6,
      },
      {
        label: 'Dépenses',
        data: depData,
        backgroundColor: 'rgba(193,18,31,.65)',
        borderRadius: 6,
      },
      {
        label: 'Bénéfice',
        data: benefices,
        type: 'line',
        borderColor: '#C8960C',
        backgroundColor: 'rgba(200,150,12,.12)',
        borderWidth: 2.5,
        pointBackgroundColor: '#C8960C',
        pointRadius: 4,
        tension: 0.4,
        fill: true,
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      tooltip: {
        callbacks: {
          label: ctx => ctx.dataset.label + ' : ' + ctx.parsed.y.toLocaleString('fr-SN') + ' FCFA'
        }
      }
    },
    scales: {
      y: {
        ticks: {
          callback: v => (v/1000).toLocaleString('fr-SN') + 'k'
        },
        grid: { color: 'rgba(0,0,0,.05)' }
      },
      x: { grid: { display: false } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
