<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Omega Air - Boarding Control</title>
    <style>
        .bg-airport { background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1436491865332-7a61a109c0f2?auto=format&fit=crop&q=80'); background-size: cover; }
        .glitch { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body class="bg-airport h-screen text-white font-mono flex items-center justify-center">
    <div class="w-4/5 bg-black/80 border-2 border-green-500 p-10 rounded-lg shadow-[0_0_50px_rgba(34,197,94,0.3)]">
        <div class="flex justify-between items-start border-b border-green-900 pb-6 mb-6">
            <div>
                <h1 class="text-4xl font-black tracking-tighter">OMEGA TRAVEL AGENCY</h1>
                <p class="text-green-500 tracking-[0.5em] text-xs uppercase">International Logistics & Luxury</p>
            </div>
            <div class="text-right">
                <p class="text-sm">GATE: <span class="text-xl font-bold">D-221</span></p>
                <p class="text-sm">FLIGHT: <span class="text-xl font-bold">OM-2026</span></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div>
                <h2 class="text-green-500 mb-4 italic">> LIVE TRACKING_</h2>
                <ul class="space-y-4">
                    <li class="flex justify-between border-b border-zinc-800 pb-2">
                        <span>DAKAR [DSS] ➔ PARIS [CDG]</span>
                        <span class="text-blue-400 font-bold">EN VOL</span>
                    </li>
                    <li class="flex justify-between border-b border-zinc-800 pb-2">
                        <span>DUBAI [DXB] ➔ DAKAR [DSS]</span>
                        <span class="text-orange-500 font-bold">RETARDÉ</span>
                    </li>
                </ul>
            </div>
            <div class="flex flex-col justify-center items-center bg-zinc-900/50 p-6 rounded border border-zinc-700">
                <i class="fas fa-suitcase-rolling text-5xl mb-4 text-green-500"></i>
                <p class="text-center text-sm opacity-70 italic">Gestion automatisée du fret et des bagages pour les investisseurs Omega.</p>
                <a href="../genie_civil/" class="mt-6 px-6 py-2 bg-green-600 hover:bg-green-500 text-black font-bold uppercase transition">Retour Gestion Chantier</a>
            </div>
        </div>
    </div>
</body>
</html>
