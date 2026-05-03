<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comp_id = (int)$_POST['composante_id'];
    $libelle = $conn->real_escape_string($_POST['libelle']);
    $prevu   = !empty($_POST['montant_prevu']) ? (float)$_POST['montant_prevu'] : 0;
    $reel    = !empty($_POST['montant_reel']) ? (float)$_POST['montant_reel'] : 0;
    $date    = !empty($_POST['date_op']) ? $_POST['date_op'] : date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO depenses_details (composante_id, libelle, montant_prevu, montant_reel, date_paiement) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdds", $comp_id, $libelle, $prevu, $reel, $date);
    $stmt->execute();
    header("Location: stats_innovation.php");
}
$composantes = $conn->query("SELECT * FROM composantes ORDER BY nom ASC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Saisie Omega - Expert</title>
</head>
<body class="bg-zinc-950 text-white p-6">
    <div class="max-w-xl mx-auto bg-zinc-900 p-8 rounded-2xl border border-orange-600 shadow-2xl">
        <h2 class="text-xl font-black mb-6 text-orange-500 uppercase tracking-tighter italic text-center">Saisie Expert : Flux Chantier</h2>
        <form method="POST" class="space-y-4">
            <select name="composante_id" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700 outline-none focus:border-orange-500">
                <?php while($c = $composantes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['nom'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="libelle" placeholder="Ex: Livraison Sable, Pose Carreaux Salon, Paie Journaliers" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700" required>
            <div class="grid grid-cols-2 gap-4">
                <input type="number" step="0.01" name="montant_prevu" placeholder="Budget Prévu" class="bg-zinc-800 p-3 rounded border border-green-900 text-green-400 font-mono">
                <input type="number" step="0.01" name="montant_reel" placeholder="Dépense Réelle" class="bg-zinc-800 p-3 rounded border border-orange-900 text-orange-400 font-mono">
            </div>
            <input type="date" name="date_op" value="<?= date('Y-m-d') ?>" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700 text-xs">
            <button type="submit" class="w-full bg-orange-600 py-4 rounded-xl font-black uppercase shadow-lg shadow-orange-900/30">Valider l'écriture</button>
            <a href="stats_innovation.php" class="block text-center text-xs text-gray-500 hover:text-white mt-4">ANNULER / RETOUR ANALYTICS</a>
        </form>
    </div>
</body>
</html>
