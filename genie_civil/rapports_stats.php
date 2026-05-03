<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

// 1. Calcul du Score de Santé Global (Ratio de Conformité)
$global = $conn->query("SELECT SUM(montant_prevu) as p, SUM(montant_reel) as r FROM depenses_details")->fetch_assoc();
$performance_globale = ($global['p'] > 0) ? round(($global['r'] / $global['p']) * 100, 1) : 0;

// 2. Statistiques par Composante (Impact et Ratios)
$stats = $conn->query("SELECT c.nom, 
    SUM(d.montant_prevu) as budget, 
    SUM(d.montant_reel) as depense,
    (SUM(d.montant_reel) - SUM(d.montant_prevu)) as variance
    FROM composantes c 
    LEFT JOIN depenses_details d ON c.id = d.composante_id 
    GROUP BY c.id");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Omega BI - Rapports & Statistiques</title>
</head>
<body class="bg-zinc-950 text-slate-200 font-sans p-6">
    <header class="mb-10 flex justify-between items-center bg-zinc-900 p-6 rounded-2xl border border-zinc-800">
        <div>
            <h1 class="text-2xl font-black text-orange-500 uppercase tracking-tighter">Omega Analytics Suite</h1>
            <p class="text-xs text-gray-500 font-mono">Status: <span class="text-green-500">LIVE_DATA_STREAMING</span></p>
        </div>
        <div class="text-center">
            <div class="text-4xl font-black <?= $performance_globale > 100 ? 'text-red-500' : 'text-green-500' ?>">
                <?= $performance_globale ?>%
            </div>
            <p class="text-[10px] uppercase text-gray-400">Indice d'Absorption Budgétaire</p>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
        <div class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800">
            <h2 class="text-sm font-bold mb-6 text-gray-400 uppercase tracking-widest"><i class="fas fa-chart-pie mr-2"></i> Analyse des Écarts par Secteur</h2>
            <div class="space-y-6">
                <?php while($row = $stats->fetch_assoc()): 
                    $color = ($row['variance'] > 0) ? 'bg-red-500' : 'bg-blue-500';
                    $perc = ($row['budget'] > 0) ? round(($row['depense'] / $row['budget']) * 100) : 0;
                ?>
                <div class="relative">
                    <div class="flex justify-between text-xs mb-2">
                        <span class="font-bold"><?= $row['nom'] ?></span>
                        <span class="<?= $row['variance'] > 0 ? 'text-red-400' : 'text-green-400' ?>">
                            <?= number_format($row['variance'], 0, ',', ' ') ?> FCFA
                        </span>
                    </div>
                    <div class="w-full bg-zinc-800 h-3 rounded-full overflow-hidden">
                        <div class="<?= $color ?> h-full transition-all duration-1000" style="width: <?= min($perc, 100) ?>%"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 flex flex-col justify-center">
            <h2 class="text-sm font-bold mb-6 text-gray-400 uppercase tracking-widest text-center">Indicateurs de Rentabilité</h2>
            <div class="flex justify-around items-center h-full">
                <div class="text-center">
                    <div class="w-24 h-24 rounded-full border-4 border-orange-500 flex items-center justify-center text-xl font-bold">
                        1.2
                    </div>
                    <p class="text-[10px] mt-2 text-gray-500">ROI PRÉVISIONNEL</p>
                </div>
                <div class="text-center">
                    <div class="w-24 h-24 rounded-full border-4 border-green-500 flex items-center justify-center text-xl font-bold">
                        0.9
                    </div>
                    <p class="text-[10px] mt-2 text-gray-500">CPI (Cost Index)</p>
                </div>
            </div>
            <p class="mt-6 text-[10px] text-gray-500 italic text-center border-t border-zinc-800 pt-4">
                * Un CPI > 1 indique que le projet est en dessous du budget (Économie).
            </p>
        </div>
    </div>

    <footer class="flex space-x-4">
        <button onclick="window.print()" class="bg-white text-black px-8 py-3 rounded-xl font-bold text-xs uppercase hover:bg-orange-500 hover:text-white transition">
            <i class="fas fa-file-pdf mr-2"></i> Exporter Rapport PDF
        </button>
        <a href="dashboard_complet.php" class="bg-zinc-800 text-white px-8 py-3 rounded-xl font-bold text-xs uppercase border border-zinc-700">
            Retour Journal de bord
        </a>
    </footer>
</body>
</html>
