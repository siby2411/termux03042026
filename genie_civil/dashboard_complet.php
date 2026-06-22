<?php
$conn = new mysqli('localhost','root','','omega_multisectoriel');
require_once "hse_engine.php";

/* KPI GLOBAL */
$budget = $conn->query("SELECT SUM(montant_prevu) t FROM depenses_details")->fetch_assoc()['t'] ?? 0;
$spent  = $conn->query("SELECT SUM(montant_reel) t FROM depenses_details")->fetch_assoc()['t'] ?? 0;

$ratio = ($budget > 0) ? ($spent/$budget)*100 : 0;

/* RISQUE IA */
$delay = rand(5,45);
$ai = OmegaAI::riskLevel($budget,$spent,$delay);

/* ALERTES HSE */
$alerts = $conn->query("SELECT COUNT(*) c FROM incidents WHERE gravite='Critique'")->fetch_assoc()['c'] ?? 0;

/* RH PRODUCTIVITE */
$rh = $conn->query("
SELECT SUM(montant_paye) t FROM pointage_paie
")->fetch_assoc()['t'] ?? 0;

?>
<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.tailwindcss.com"></script>
<title>OMEGA ERP V2 - COMMAND CENTER</title>
</head>

<body class="bg-black text-white">

<!-- HEADER -->
<div class="p-6 bg-zinc-900 border-b border-zinc-800">
<h1 class="text-3xl font-bold">OMEGA ERP V2 - CENTRE DE COMMANDEMENT</h1>

<div class="mt-3 text-<?= $ai['color'] ?>-500 font-bold text-xl">
RISQUE CHANTIER : <?= $ai['level'] ?> (<?= round($ai['score'],1) ?>%)
</div>

<p class="text-sm text-gray-400">Budget: <?= number_format($budget,0,',',' ') ?> FCFA</p>
<p class="text-sm text-gray-400">Dépenses: <?= number_format($spent,0,',',' ') ?> FCFA</p>
</div>

<?php if($ai['level']=="CRITICAL"): ?>
<div class="bg-red-900 p-4 m-4 rounded">
🚨 ALERTE CRITIQUE CHANTIER
</div>
<?php endif; ?>

<!-- KPI CARDS -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6">

<div class="bg-zinc-800 p-4 rounded">💰 Budget<br><?= number_format($budget) ?></div>
<div class="bg-zinc-800 p-4 rounded">📉 Dépenses<br><?= number_format($spent) ?></div>
<div class="bg-zinc-800 p-4 rounded">👷 RH Paiements<br><?= number_format($rh) ?></div>
<div class="bg-zinc-800 p-4 rounded">🦺 Incidents Critiques<br><?= $alerts ?></div>

</div>

<!-- MODULES ERP -->
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 p-6">

<a href="index.php" class="bg-zinc-700 p-4 rounded">🏠 Accueil ERP</a>

<a href="stats_innovation.php" class="bg-blue-900 p-4 rounded">📊 IA Analytics</a>
<a href="dashboard_kpi.php" class="bg-green-900 p-4 rounded">📡 Live KPI</a>

<a href="gestion_rh.php" class="bg-purple-900 p-4 rounded">👷 RH & Paie</a>
<a href="formulaire_paie.php" class="bg-purple-800 p-4 rounded">💳 Contrats RH</a>

<a href="gestion_fournisseurs.php" class="bg-yellow-900 p-4 rounded">🚚 Fournisseurs</a>
<a href="gestion_carburant.php" class="bg-orange-900 p-4 rounded">⛽ Carburant</a>

<a href="gestion_equipements.php" class="bg-indigo-900 p-4 rounded">🏗️ Équipements</a>
<a href="gestion_documents.php" class="bg-indigo-800 p-4 rounded">📁 Documents</a>

<a href="gestion_incidents.php" class="bg-red-900 p-4 rounded">🦺 Sécurité HSE</a>
<a href="gestion_stocks.php" class="bg-teal-900 p-4 rounded">📦 Stocks</a>

<a href="rapports_stats.php" class="bg-gray-800 p-4 rounded">📈 Rapports</a>
<a href="audit_engine.php" class="bg-black border border-gray-700 p-4 rounded">🔍 Audit IA</a>

<a href="notifications.php" class="bg-pink-900 p-4 rounded">🔔 Notifications</a>
<a href="ia_retard.php" class="bg-red-800 p-4 rounded">🤖 IA Retard Chantier</a>

<a href="whatsapp_alert.php" class="bg-green-800 p-4 rounded">📲 WhatsApp Alert</a>

</div>

</body>
</html>
