<?php
$conn = new mysqli("localhost","root","","omega_multisectoriel");

$res = $conn->query("SELECT * FROM ai_alerts ORDER BY created_at DESC LIMIT 10");
?>

<div class="bg-zinc-900 p-4 rounded-xl border border-zinc-700 mt-4">
<h2 class="text-sm font-bold text-white mb-3">📡 ALERTES CHANTIER LIVE</h2>

<?php while($a = $res->fetch_assoc()): ?>
<div class="p-2 border-b border-zinc-800 text-xs">
    <div class="text-<?= $a['risk_score']>85?'red':($a['risk_score']>60?'orange':'green') ?>-400 font-bold">
        SCORE <?= $a['risk_score'] ?>%
    </div>
    <div><?= htmlspecialchars($a['message']) ?></div>
    <div class="text-gray-500"><?= $a['created_at'] ?></div>
</div>
<?php endwhile; ?>

</div>
