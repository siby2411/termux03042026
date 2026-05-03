<?php
$total_immeubles = $pdo->query("SELECT COUNT(*) FROM immeubles")->fetchColumn();
$total_prospects = $pdo->query("SELECT COUNT(*) FROM prospects")->fetchColumn();
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border-start border-primary border-5">
            <h6 class="text-muted small">TOTAL PROPRIÉTÉS</h6>
            <h2 class="fw-bold mb-0"><?= $total_immeubles ?></h2>
            <i class="bi bi-house kpi-icon text-primary"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-start border-warning border-5">
            <h6 class="text-muted small">PROSPECTS ACTIFS</h6>
            <h2 class="fw-bold mb-0"><?= $total_prospects ?></h2>
            <i class="bi bi-person-check kpi-icon text-warning"></i>
        </div>
    </div>
</div>
