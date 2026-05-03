<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comp_id = (int)$_POST['composante_id'];
    $tache   = $conn->real_escape_string($_POST['tache']);
    $debut   = $_POST['date_debut'];
    $fin     = $_POST['date_fin'];
    $prog    = (int)$_POST['progression'];

    $conn->query("INSERT INTO planning_travaux (composante_id, tache, date_debut, date_fin, progression) 
                  VALUES ('$comp_id', '$tache', '$debut', '$fin', '$prog')");
    header("Location: stats_innovation.php");
}
$composantes = $conn->query("SELECT * FROM composantes ORDER BY nom ASC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Saisie Planning - Omega</title>
</head>
<body class="bg-zinc-950 text-white p-6">
    <div class="max-w-md mx-auto bg-zinc-900 p-8 rounded-2xl border border-blue-500">
        <h2 class="text-xl font-bold mb-6 text-blue-500 uppercase">Planifier Travaux / RDV</h2>
        <form method="POST" class="space-y-4">
            <select name="composante_id" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700">
                <?php while($c = $composantes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['nom'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="tache" placeholder="Nom de la tâche (ex: Pose Carrelage)" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700" required>
            <div class="grid grid-cols-2 gap-4">
                <input type="date" name="date_debut" class="bg-zinc-800 p-3 rounded border border-zinc-700">
                <input type="date" name="date_fin" class="bg-zinc-800 p-3 rounded border border-zinc-700">
            </div>
            <input type="number" name="progression" placeholder="Progression %" min="0" max="100" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700">
            <button type="submit" class="w-full bg-blue-600 py-4 rounded font-bold uppercase">Ajouter au Planning</button>
            <a href="stats_innovation.php" class="block text-center text-xs mt-4 text-gray-500">ANNULER</a>
        </form>
    </div>
</body>
</html>
