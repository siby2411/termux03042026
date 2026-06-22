<?php
$conn = new mysqli("localhost","root","","omega_multisectoriel");

require_once "hse_engine.php";

$total = $conn->query("SELECT SUM(montant_reel) t FROM depenses_details")->fetch_assoc()['t'] ?? 0;
$budget = $conn->query("SELECT SUM(montant_prevu) t FROM depenses_details")->fetch_assoc()['t'] ?? 0;

$delay = rand(5,45);

$ai = OmegaAI::riskLevel($budget,$total,$delay);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<title>OMEGA ERP - BTP INTELLIGENT</title>
</head>

<body class="bg-black text-white p-6">

<!-- 🔷 BANNIÈRE PREMIUM -->
<div class="relative overflow-hidden bg-gradient-to-r from-zinc-950 via-zinc-900 to-black border border-orange-600/30 rounded-3xl p-8 shadow-2xl mb-6">

    <div class="absolute -top-10 -right-10 text-[150px] text-orange-600/10 rotate-12">
        <i class="fas fa-industry"></i>
    </div>

    <div class="relative z-10 flex flex-col md:flex-row md:justify-between md:items-center">

        <div>
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight">
                <span class="text-orange-500">OMEGA</span>
                <span class="text-white">INFORMATIQUE</span>
            </h1>

            <p class="text-sm text-gray-400 mt-2 font-semibold uppercase">
                Consulting • Génie Civil • Management de Projet BTP
            </p>

            <p class="text-[11px] text-gray-600 mt-1">
                Plateforme intelligente de pilotage des chantiers – Dakar 2026
            </p>
        </div>

        <div class="text-right mt-4 md:mt-0">
            <div class="bg-orange-600 text-black px-4 py-2 rounded-full font-black text-xs uppercase">
                SYSTEM ACTIVE
            </div>

            <div class="text-xs text-gray-400 mt-2">
                ERP Enterprise • IA & Real-Time Monitoring
            </div>
        </div>

    </div>
</div>

<!-- 🔴 ALERTES RISQUE -->
<header class="p-6 bg-zinc-900 rounded-xl border border-zinc-800 mb-6">

<h2 class="text-xl font-bold">
RISK ENGINE — CHANTIER INTELLIGENT
</h2>

<div class="mt-3 text-<?= $ai['color'] ?>-500 font-black text-2xl">
<?= $ai['level'] ?> RISK SCORE : <?= round($ai['score'],1) ?>%
</div>

<p class="text-sm text-gray-400 mt-2">
Budget: <?= number_format($budget,0,',',' ') ?> FCFA
</p>

<p class="text-sm text-gray-400">
Dépensé: <?= number_format($total,0,',',' ') ?> FCFA
</p>

</header>

<?php if($ai['level']=="CRITICAL"): ?>
<div class="bg-red-900 border border-red-700 p-4 mt-4 rounded-xl animate-pulse">
🚨 ALERTES CHANTIER CRITIQUES ACTIVÉES — SURVEILLANCE RENFORCÉE
</div>
<?php endif; ?>

<!-- MODULES ERP -->
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6">

<a href="dashboard_complet.php" class="bg-zinc-800 p-4 rounded-xl hover:bg-zinc-700">📊 Command Center</a>
<a href="gestion_rh.php" class="bg-zinc-800 p-4 rounded-xl hover:bg-zinc-700">👷 RH AI</a>
<a href="gestion_fournisseurs.php" class="bg-zinc-800 p-4 rounded-xl hover:bg-zinc-700">🚚 Supply Chain</a>
<a href="gestion_carburant.php" class="bg-zinc-800 p-4 rounded-xl hover:bg-zinc-700">⛽ Fuel AI</a>
<a href="gestion_equipements.php" class="bg-zinc-800 p-4 rounded-xl hover:bg-zinc-700">🏗️ Equipment</a>
<a href="gestion_documents.php" class="bg-zinc-800 p-4 rounded-xl hover:bg-zinc-700">📁 Docs AI</a>

<a href="gestion_incidents.php" class="bg-red-900 p-4 rounded-xl hover:bg-red-800">🦺 HSE SAFETY</a>
<a href="stats_innovation.php" class="bg-blue-900 p-4 rounded-xl hover:bg-blue-800">📈 AI ANALYTICS</a>
<a href="dashboard_kpi.php" class="bg-green-900 p-4 rounded-xl hover:bg-green-800">📡 LIVE KPI</a>

</div>

</body>
</html>
