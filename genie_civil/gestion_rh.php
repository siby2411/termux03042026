<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p_id = (int)$_POST['personnel_id'];
    $montant = (float)$_POST['montant'];
    $date = $_POST['date_paie'];
    $statut = $_POST['statut'];

    $conn->query("INSERT INTO pointage_paie (personnel_id, date_travail, montant_paye, statut_paiement) 
                  VALUES ('$p_id', '$date', '$montant', '$statut')");
    // Enregistrer aussi dans la trésorerie globale pour le bilan
    $conn->query("INSERT INTO tresorerie (secteur_id, libelle, montant, type_flux, date_op) 
                  VALUES (1, 'Main d’œuvre: ID '.$p_id, $montant, 'Depense', '$date')");
}

$equipes = $conn->query("SELECT * FROM personnel");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Omega RH - Paie Ouvriers</title>
</head>
<body class="bg-zinc-950 text-white p-6 font-sans">
    <div class="max-w-4xl mx-auto">
        <header class="flex justify-between items-center mb-10 border-b border-zinc-800 pb-4">
            <h1 class="text-2xl font-black text-blue-500">PAIEMENT & POINTAGE OUVRIERS</h1>
            <a href="index.php" class="text-xs border border-zinc-700 px-4 py-2 rounded">RETOUR</a>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <form method="POST" class="bg-zinc-900 p-6 rounded-xl border border-blue-900/30 space-y-4">
                <h2 class="text-sm font-bold uppercase text-blue-400 mb-4 italic">Nouvel Acompte/Solde</h2>
                <select name="personnel_id" class="w-full bg-zinc-800 p-3 rounded text-sm border border-zinc-700">
                    <?php while($e = $equipes->fetch_assoc()): ?>
                        <option value="<?= $e['id'] ?>"><?= $e['nom_complet'] ?> (<?= $e['specialite'] ?>)</option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="montant" placeholder="Montant (FCFA)" class="w-full bg-zinc-800 p-3 rounded text-sm border border-zinc-700" required>
                <input type="date" name="date_paie" value="<?= date('Y-m-d') ?>" class="w-full bg-zinc-800 p-3 rounded text-sm">
                <select name="statut" class="w-full bg-zinc-800 p-3 rounded text-sm border border-zinc-700">
                    <option value="Avance">Avance / Acompte</option>
                    <option value="Solde">Solde Fin de Semaine</option>
                    <option value="Complet">Paiement Intégral</option>
                </select>
                <button type="submit" class="w-full bg-blue-600 py-3 rounded font-bold uppercase text-xs">Valider le Paiement</button>
            </form>

            <div class="md:col-span-2 bg-zinc-900 p-6 rounded-xl border border-zinc-800">
                <h2 class="text-sm font-bold uppercase text-gray-400 mb-4 italic">Historique de Paie</h2>
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-zinc-800 text-gray-500">
                            <th class="p-2">Ouvrier / Equipe</th>
                            <th class="p-2">Montant</th>
                            <th class="p-2">Date</th>
                            <th class="p-2">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $paies = $conn->query("SELECT p.nom_complet, pt.* FROM pointage_paie pt JOIN personnel p ON pt.personnel_id = p.id ORDER BY pt.date_travail DESC LIMIT 8");
                        while($row = $paies->fetch_assoc()): ?>
                        <tr class="border-b border-zinc-800 hover:bg-zinc-800">
                            <td class="p-2 font-bold"><?= $row['nom_complet'] ?></td>
                            <td class="p-2 font-mono"><?= number_format($row['montant_paye'], 0, ',', ' ') ?></td>
                            <td class="p-2"><?= $row['date_travail'] ?></td>
                            <td class="p-2"><span class="text-blue-400 italic"><?= $row['statut_paiement'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
