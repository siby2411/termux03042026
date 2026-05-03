<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

$mois = $_GET['mois'] ?? date('m');
$annee = $_GET['annee'] ?? date('Y');

// 1. REVENUS DU MOIS
$stmtRev = $pdo->prepare("SELECT SUM(montant) FROM paiements WHERE MONTH(date_paiement) = ? AND YEAR(date_paiement) = ?");
$stmtRev->execute([$mois, $annee]);
$revenus = $stmtRev->fetchColumn() ?: 0;

// 2. DÉPENSES DU MOIS
$stmtDep = $pdo->prepare("SELECT SUM(montant) FROM depenses WHERE MONTH(date_dep) = ? AND YEAR(date_dep) = ?");
$stmtDep->execute([$mois, $annee]);
$charges = $stmtDep->fetchColumn() ?: 0;

$benefice = $revenus - $charges;

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Analyse Financière</h4>
        <small class="text-muted text-uppercase"><?= date('F Y', mktime(0,0,0,$mois, 1, $annee)) ?></small>
    </div>
    <div class="d-flex gap-2">
        <a href="bilan_pdf.php?mois=<?= $mois ?>&annee=<?= $annee ?>" target="_blank" class="btn btn-dark shadow-sm border-gold" style="border: 1px solid #d4af37;">
            <i class="bi bi-file-earmark-pdf me-2 text-gold"></i> Synthèse PDF
        </a>
        
        <form class="d-flex gap-2">
            <select name="mois" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?= sprintf('%02s',$m) ?>" <?= $m==$mois?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="annee" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="2026" selected>2026</option>
                <option value="2025">2025</option>
            </select>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-dark text-white p-3">
            <small class="text-gold text-uppercase small" style="letter-spacing:1px;">Entrées (Revenus)</small>
            <h3 class="fw-bold mb-0"><?= number_format($revenus, 0, ',', ' ') ?> <small style="font-size:12px;">FCFA</small></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 border-start border-danger border-5">
            <small class="text-muted text-uppercase small">Sorties (Dépenses)</small>
            <h3 class="fw-bold mb-0"><?= number_format($charges, 0, ',', ' ') ?> F</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-left: 5px solid #d4af37 !important;">
            <small class="text-muted text-uppercase small">Bénéfice Net</small>
            <h3 class="fw-bold mb-0 <?= $benefice < 0 ? 'text-danger' : 'text-success' ?>"><?= number_format($benefice, 0, ',', ' ') ?> F</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 mb-4">
    <h6 class="fw-bold mb-4 text-uppercase small"><i class="bi bi-bar-chart-line me-2 text-gold"></i> Comparatif Performance</h6>
    <div style="height: 300px;">
        <canvas id="financeChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('financeChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Performance Mensuelle'],
        datasets: [
            {
                label: 'Revenus',
                data: [<?= $revenus ?>],
                backgroundColor: '#d4af37',
                borderRadius: 5
            },
            {
                label: 'Dépenses',
                data: [<?= $charges ?>],
                backgroundColor: '#000000',
                borderRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
