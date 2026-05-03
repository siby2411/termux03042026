<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

// Stats Qualité
$reserves = $conn->query("SELECT r.*, c.nom FROM reserves_qualite r JOIN composantes c ON r.composante_id = c.id WHERE r.statut != 'Valide'");
?>
<div class="mt-8 bg-zinc-900/90 p-6 rounded-3xl border-2 border-red-900/30 shadow-2xl">
    <h3 class="text-xs font-bold text-red-500 uppercase mb-6 flex items-center italic">
        <i class="fas fa-exclamation-triangle mr-3"></i> Audit Qualité : Réserves à lever (Punch List)
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php while($res = $reserves->fetch_assoc()): ?>
        <div class="bg-black p-4 rounded-xl border border-zinc-800 flex justify-between items-center">
            <div>
                <span class="text-[9px] bg-red-900/50 text-red-400 px-2 py-0.5 rounded uppercase font-bold"><?= $res['criticite'] ?></span>
                <h4 class="text-xs font-bold text-white mt-1"><?= $res['nom'] ?> : <?= $res['description_defaut'] ?></h4>
            </div>
            <button class="text-[10px] bg-zinc-800 hover:bg-green-600 px-3 py-1 rounded transition">RÉSOUDRE</button>
        </div>
        <?php endwhile; ?>
        <?php if($reserves->num_rows == 0): ?>
            <p class="text-xs text-green-500 italic">Zéro défaut technique signalé. Chantier conforme.</p>
        <?php endif; ?>
    </div>
</div>
