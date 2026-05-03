<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

// Données Financières pour les Ratios
$stats = $conn->query("SELECT * FROM stats_composantes");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Omega Civil Audit - 2026</title>
</head>
<body class="bg-black text-white p-8 font-mono">
    <div class="flex justify-between items-center mb-10 border-b border-orange-500 pb-4">
        <h1 class="text-3xl font-black tracking-tighter">AUDIT FINANCIER <span class="text-orange-500">CHANTIER</span></h1>
        <div class="space-x-4">
            <a href="formulaire_omega.php" class="bg-green-600 px-6 py-2 rounded font-bold text-black hover:bg-green-400">+ NOUVELLE TRANSACTION</a>
            <a href="index.php" class="text-xs text-gray-400">PORTAIL OMEGA</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <?php while($s = $stats->fetch_assoc()): 
            $ratio = round($s['ratio_absorption'] ?? 0);
            $color = ($ratio > 100) ? 'text-red-500' : 'text-green-400';
        ?>
        <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg">
            <h3 class="text-xs text-gray-500 mb-2"><?= $s['composante'] ?></h3>
            <div class="text-2xl font-bold <?= $color ?>"><?= $ratio ?>%</div>
            <p class="text-[10px] text-gray-400 italic">Absorption du budget</p>
            <div class="mt-4 w-full bg-zinc-800 h-1"><div class="bg-orange-500 h-1" style="width:<?= min($ratio, 100) ?>%"></div></div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
        <h2 class="p-4 bg-zinc-800 text-xs font-bold uppercase tracking-widest italic">Journal des Transactions / Génie Civil</h2>
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-zinc-800">
                    <th class="p-4 border-b border-zinc-700">Composante</th>
                    <th class="p-4 border-b border-zinc-700">Détail</th>
                    <th class="p-4 border-b border-zinc-700">Dépense (FCFA)</th>
                    <th class="p-4 border-b border-zinc-700">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $journal = $conn->query("SELECT c.nom, d.* FROM depenses_details d JOIN composantes c ON d.composante_id = c.id ORDER BY d.date_paiement DESC LIMIT 10");
                while($j = $journal->fetch_assoc()): ?>
                <tr class="border-b border-zinc-800 hover:bg-zinc-800/50">
                    <td class="p-4 font-bold text-orange-500"><?= $j['nom'] ?></td>
                    <td class="p-4"><?= $j['libelle'] ?></td>
                    <td class="p-4 font-mono"><?= number_format($j['montant_reel'], 0, ',', ' ') ?></td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-green-900/30 text-green-500 text-[10px] border border-green-500 rounded">VALIDE</span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
