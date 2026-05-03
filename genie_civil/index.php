<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');
$total_projet = $conn->query("SELECT SUM(montant_reel) as t FROM depenses_details")->fetch_assoc()['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Omega Projet - Accueil Expert</title>
</head>
<body class="bg-zinc-950 text-white font-sans p-6">
    <div class="max-w-4xl mx-auto">
        <header class="flex justify-between items-center mb-12 bg-zinc-900 p-8 rounded-3xl border border-zinc-800 shadow-2xl">
            <div>
                <h1 class="text-4xl font-black italic text-orange-500 uppercase tracking-tighter">Omega <span class="text-white">Multisectoriel</span></h1>
                <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest">Système de Pilotage de Direction de Travaux (PDT)</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-gray-400 block uppercase">Budget Engagé</span>
                <span class="text-2xl font-mono font-black text-green-500"><?= number_format($total_projet, 0, ',', ' ') ?> FCFA</span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <a href="stats_innovation.php" class="group relative bg-zinc-900 p-8 rounded-3xl border-2 border-orange-600/30 hover:border-orange-500 transition-all duration-500 shadow-xl overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-9xl text-orange-600/10 group-hover:rotate-12 transition-transform">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-black italic text-orange-500 mb-2 uppercase">Audit & Analytics</h2>
                    <p class="text-sm text-gray-400 leading-relaxed">Courbe S (S-Curve), Répartition par poste (%), et Diagramme de Gantt pour le suivi des délais.</p>
                    <div class="mt-6 flex items-center text-xs font-bold text-white uppercase bg-orange-600 w-fit px-4 py-2 rounded-full">
                        Ouvrir le Pilotage <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </div>
            </a>

            <a href="formulaire_omega.php" class="group relative bg-zinc-900 p-8 rounded-3xl border border-zinc-800 hover:border-green-500 transition-all duration-500 shadow-xl overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-9xl text-green-600/10 group-hover:-rotate-12 transition-transform">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-black italic text-green-500 mb-2 uppercase">Saisie Flux</h2>
                    <p class="text-sm text-gray-400 leading-relaxed">Enregistrement des dépenses : Matériaux, Transport, Carrelage et Main d'œuvre.</p>
                    <div class="mt-6 flex items-center text-xs font-bold text-white uppercase bg-zinc-800 w-fit px-4 py-2 rounded-full">
                        Nouvelle Transaction <i class="fas fa-plus ml-2 text-green-500"></i>
                    </div>
                </div>
            </a>

            <a href="formulaire_planning.php" class="group relative bg-zinc-900 p-8 rounded-3xl border border-zinc-800 hover:border-blue-500 transition-all duration-500 shadow-xl overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-9xl text-blue-600/10 group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-black italic text-blue-500 mb-2 uppercase">Planning Travaux</h2>
                    <p class="text-sm text-gray-400 leading-relaxed">Gestion des tâches, définition des jalons et mise à jour du chronogramme hebdomadaire.</p>
                    <div class="mt-6 flex items-center text-xs font-bold text-white uppercase bg-zinc-800 w-fit px-4 py-2 rounded-full">
                        Gérer les Délais <i class="fas fa-clock ml-2 text-blue-500"></i>
                    </div>
                </div>
            </a>

            <a href="gestion_rh.php" class="group relative bg-zinc-900 p-8 rounded-3xl border border-zinc-800 hover:border-purple-500 transition-all duration-500 shadow-xl overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-9xl text-purple-600/10 group-hover:translate-y-2 transition-transform">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-black italic text-purple-500 mb-2 uppercase">Ressources Humaines</h2>
                    <p class="text-sm text-gray-400 leading-relaxed">Suivi des paiements des ouvriers, plombiers, électriciens et gestion des acomptes.</p>
                    <div class="mt-6 flex items-center text-xs font-bold text-white uppercase bg-zinc-800 w-fit px-4 py-2 rounded-full">
                        Audit Personnel <i class="fas fa-user-check ml-2 text-purple-500"></i>
                    </div>
                </div>
            </a>

        </div>

        <footer class="mt-12 text-center border-t border-zinc-900 pt-8">
            <p class="text-[10px] text-gray-600 uppercase tracking-[0.3em]">Propulsé par Omega Engineering Suite • Dakar 2026</p>
        </footer>
    </div>
</body>
</html>
