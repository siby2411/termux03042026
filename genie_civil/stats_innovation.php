<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

// A. CALCULS KPI (Management Financier)
$total_realise = $conn->query("SELECT SUM(montant_reel) as t FROM depenses_details")->fetch_assoc()['t'] ?? 0;
$audit_postes = $conn->query("SELECT c.nom, SUM(d.montant_reel) as cumul, (SUM(d.montant_reel)/NULLIF($total_realise,0)*100) as p FROM composantes c JOIN depenses_details d ON c.id = d.composante_id GROUP BY c.id ORDER BY cumul DESC");

// B. SUIVI RH (Dettes et Engagements)
$rh_total = $conn->query("SELECT SUM(montant_contrat) as total, SUM(acomptes_verses) as paye FROM gestion_paie")->fetch_assoc();
$dette_rh = ($rh_total['total'] ?? 0) - ($rh_total['paye'] ?? 0);

// C. PLANNING (Gantt)
$planning = $conn->query("SELECT p.*, c.nom as secteur FROM planning_travaux p JOIN composantes c ON p.composante_id = c.id ORDER BY p.date_debut ASC");

// D. QUALITÉ (Réserves)
$reserves = $conn->query("SELECT COUNT(*) as nb FROM reserves_qualite WHERE statut != 'Valide'")->fetch_assoc()['nb'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Omega R+3 - Système de Pilotage</title>
</head>
<body class="bg-black text-slate-400 font-sans flex h-screen overflow-hidden">
    
    <aside class="w-64 bg-zinc-900 border-r border-zinc-800 p-6 flex flex-col shrink-0">
        <h1 class="text-orange-500 font-black text-2xl mb-10 italic">OMEGA <span class="text-white text-xs">R+3</span></h1>
        <div class="space-y-6">
            <div class="text-[9px] font-bold text-gray-500 uppercase tracking-widest">Saisie de données</div>
            <nav class="space-y-2 text-xs font-bold uppercase">
                <a href="formulaire_omega.php" class="flex items-center p-3 hover:bg-zinc-800 rounded transition text-green-500"><i class="fas fa-plus-circle mr-3"></i> Dépenses</a>
                <a href="formulaire_planning.php" class="flex items-center p-3 hover:bg-zinc-800 rounded transition text-blue-500"><i class="fas fa-calendar-alt mr-3"></i> Planning</a>
                <a href="formulaire_paie.php" class="flex items-center p-3 hover:bg-zinc-800 rounded transition text-purple-500"><i class="fas fa-user-tie mr-3"></i> Contrats RH</a>
                <a href="index.php" class="flex items-center p-3 hover:bg-zinc-800 rounded transition text-gray-400 border-t border-zinc-800 mt-4"><i class="fas fa-home mr-3"></i> Accueil</a>
            </nav>
        </div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="mb-10 flex justify-between items-end border-b border-zinc-800 pb-8">
            <div>
                <h2 class="text-3xl font-black text-white italic uppercase tracking-tighter">Pilotage <span class="text-orange-500">Immeuble R+3</span></h2>
                <p class="text-[10px] text-gray-500 mt-2 font-mono">ID CHANTIER: DKR-2026-R3 | Dakar, Sénégal</p>
            </div>
            <div class="flex gap-6">
                <div class="text-right border-r border-zinc-800 pr-6">
                    <span class="text-[9px] text-gray-500 uppercase">Cash-Flow Utilisé</span>
                    <p class="text-xl font-mono font-black text-white"><?= number_format($total_realise, 0, ',', ' ') ?> F</p>
                </div>
                <div class="text-right">
                    <span class="text-[9px] text-gray-500 uppercase">Restant à Payer RH</span>
                    <p class="text-xl font-mono font-black text-red-500"><?= number_format($dette_rh, 0, ',', ' ') ?> F</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="bg-zinc-900/50 p-6 rounded-3xl border border-zinc-800">
                <h3 class="text-xs font-black text-orange-500 uppercase mb-6 italic"><i class="fas fa-chart-pie mr-2"></i> Poids Financier par Secteur</h3>
                <div class="space-y-4">
                    <?php while($a = $audit_postes->fetch_assoc()): ?>
                    <div class="border-b border-zinc-800/50 pb-2">
                        <div class="flex justify-between text-[11px] mb-1 font-bold">
                            <span class="text-gray-300"><?= $a['nom'] ?></span>
                            <span class="text-white"><?= number_format($a['cumul'], 0, ',', ' ') ?> F</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex-1 bg-zinc-800 h-1 rounded-full">
                                <div class="bg-orange-500 h-full" style="width: <?= $a['p'] ?>%"></div>
                            </div>
                            <span class="text-[9px] font-mono text-orange-400"><?= round($a['p'], 1) ?>%</span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-zinc-900/50 p-6 rounded-3xl border border-zinc-800">
                    <h3 class="text-xs font-black text-blue-500 uppercase mb-6 italic"><i class="fas fa-tasks mr-2"></i> Chronogramme des Travaux (Gantt)</h3>
                    <div class="space-y-5">
                        <?php while($g = $planning->fetch_assoc()): ?>
                        <div>
                            <div class="flex justify-between text-[10px] mb-2 uppercase font-bold">
                                <span><i class="fas fa-hard-hat mr-2 text-blue-500"></i><?= $g['secteur'] ?> : <?= $g['tache'] ?></span>
                                <span class="text-gray-500 italic"><?= $g['date_debut'] ?> au <?= $g['date_fin'] ?></span>
                            </div>
                            <div class="w-full bg-zinc-800 h-6 rounded border border-zinc-700 overflow-hidden relative">
                                <div class="bg-blue-600/50 h-full flex items-center px-4 transition-all duration-1000" style="width: <?= $g['progression'] ?>%">
                                    <span class="text-[9px] font-black text-white"><?= $g['progression'] ?>% RÉALISÉ</span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-zinc-900/50 p-6 rounded-3xl border border-zinc-800">
                        <h3 class="text-xs font-black text-purple-500 uppercase mb-4 italic"><i class="fas fa-user-check mr-2"></i> État de Paie</h3>
                        <p class="text-[10px] text-gray-500 leading-relaxed italic">Management : Contrôlez les acomptes versés face à la progression réelle.</p>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="text-center bg-zinc-950 p-3 rounded-xl border border-zinc-800 flex-1">
                                <span class="text-[9px] block text-gray-500 uppercase">Payé</span>
                                <span class="text-sm font-bold text-green-500"><?= number_format($rh_total['paye'],0,',',' ') ?></span>
                            </div>
                            <div class="text-center bg-zinc-950 p-3 rounded-xl border border-zinc-800 flex-1">
                                <span class="text-[9px] block text-gray-500 uppercase">Dû</span>
                                <span class="text-sm font-bold text-red-500"><?= number_format($dette_rh,0,',',' ') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-950/20 p-6 rounded-3xl border border-red-900/30">
                        <h3 class="text-xs font-black text-red-500 uppercase mb-4 italic"><i class="fas fa-shield-alt mr-2"></i> Management Qualité</h3>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-white">Réserves en suspens</span>
                            <span class="bg-red-600 text-white text-xl font-black px-4 py-1 rounded-lg"><?= $reserves ?></span>
                        </div>
                        <p class="text-[9px] text-red-400 mt-2 italic">A corriger avant validation du décompte final.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
