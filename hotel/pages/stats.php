<?php
// 1. Calcul des Revenus Totaux
$rev_query = $pdo->query("SELECT SUM(montant_total) as total FROM reservations WHERE statut != 'Annule'")->fetch();
$total_revenus = $rev_query['total'] ?? 0;

// 2. Calcul des Dépenses (Paies + Charges)
$paies_query = $pdo->query("SELECT SUM(net_a_payer) as total FROM paies")->fetch();
$charges_query = $pdo->query("SELECT SUM(montant) as total FROM charges")->fetch();
$total_depenses = ($paies_query['total'] ?? 0) + ($charges_query['total'] ?? 0);

$benefice = $total_revenus - $total_depenses;
$ratio = $total_revenus > 0 ? ($benefice / $total_revenus) * 100 : 0;
?>

<div class="stats-container">
    <div class="card bg-gold">
        <h4>💰 Revenus Totaux</h4>
        <h2><?= number_format($total_revenus, 0, ',', ' ') ?> F CFA</h2>
    </div>
    <div class="card bg-red">
        <h4>📉 Dépenses Totales</h4>
        <h2><?= number_format($total_depenses, 0, ',', ' ') ?> F CFA</h2>
    </div>
    <div class="card <?= $benefice >= 0 ? 'bg-green' : 'bg-red' ?>">
        <h4>📊 Bénéfice Net</h4>
        <h2><?= number_format($benefice, 0, ',', ' ') ?> F CFA</h2>
    </div>
</div>

<div class="card">
    <h3>🔍 Détails du mois en cours (<?= date('M Y') ?>)</h3>
    <div style="display: flex; gap: 20px; align-items: center;">
        <div style="flex: 1;">
            <p>Salaires versés : <b><?= number_format($paies_query['total'] ?? 0, 0, ',', ' ') ?> F</b></p>
            <p>Charges fixes : <b><?= number_format($charges_query['total'] ?? 0, 0, ',', ' ') ?> F</b></p>
            <hr>
            <p>Marge de rentabilité : <b style="color: var(--gold);"><?= round($ratio, 2) ?>%</b></p>
        </div>
        <div style="flex: 1; text-align: center;">
            <div style="border: 10px solid #eee; border-top: 10px solid var(--gold); border-radius: 50%; width: 100px; height: 100px; margin: auto; display: flex; align-items: center; justify-content: center;">
                <span><?= round($ratio) ?>%</span>
            </div>
            <p>Santé financière</p>
        </div>
    </div>
</div>

<style>
.stats-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
.bg-gold { background: linear-gradient(135deg, #d4af37, #b8860b); color: white; }
.bg-red { background: linear-gradient(135deg, #e53e3e, #c53030); color: white; }
.bg-green { background: linear-gradient(135deg, #48bb78, #2f855a); color: white; }
</style>
