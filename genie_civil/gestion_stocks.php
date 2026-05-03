<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $conn->real_escape_string($_POST['designation']);
    $qte = (float)$_POST['quantite'];
    $unite = $_POST['unite'];
    $date = $_POST['date_reception'];

    $conn->query("INSERT INTO inventaire_materiaux (designation, quantite_reçue, unite, date_reception) 
                  VALUES ('$nom', '$qte', '$unite', '$date')");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Omega Stocks - Matériaux</title>
</head>
<body class="bg-slate-100 p-6 text-slate-800">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white p-8 rounded-2xl shadow-xl border-t-8 border-orange-600">
            <h1 class="text-3xl font-black mb-8 italic uppercase tracking-tighter">Réception de Matériel <span class="text-orange-600">& Matériaux</span></h1>
            
            <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10 bg-slate-50 p-6 rounded-xl border border-slate-200">
                <input type="text" name="designation" placeholder="Ciment, Fer 10, Sable..." class="p-3 border rounded shadow-sm" required>
                <input type="number" name="quantite" placeholder="Quantité" class="p-3 border rounded shadow-sm" required>
                <select name="unite" class="p-3 border rounded shadow-sm">
                    <option>Sacs (50kg)</option>
                    <option>Tonnes</option>
                    <option>m3 (Camion)</option>
                    <option>Unités (Barres)</option>
                </select>
                <button type="submit" class="bg-orange-600 text-white font-bold rounded hover:bg-orange-700 transition">ENREGISTRER</button>
                <input type="hidden" name="date_reception" value="<?= date('Y-m-d') ?>">
            </form>

            <h2 class="text-xl font-bold mb-4 border-l-4 border-slate-800 pl-4 uppercase">Inventaire Global Chantier</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php 
                $stocks = $conn->query("SELECT designation, SUM(quantite_reçue) as total, unite FROM inventaire_materiaux GROUP BY designation, unite");
                while($s = $stocks->fetch_assoc()): ?>
                <div class="bg-slate-800 text-white p-6 rounded-xl shadow-lg relative overflow-hidden">
                    <p class="text-xs uppercase text-orange-400 font-bold mb-1"><?= $s['designation'] ?></p>
                    <p class="text-3xl font-mono"><?= $s['total'] ?> <span class="text-sm font-sans"><?= $s['unite'] ?></span></p>
                    <i class="fas fa-box absolute -right-2 -bottom-2 text-6xl opacity-10"></i>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>
