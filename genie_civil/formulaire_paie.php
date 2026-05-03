<?php
$conn = new mysqli('localhost', 'root', '', 'omega_multisectoriel');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $conn->real_escape_string($_POST['nom_intervenant']);
    $role = $_POST['role'];
    $montant = $_POST['montant_contrat'];
    $acompte = $_POST['acomptes_verses'];
    $date = $_POST['date_dernier_versement'];

    $conn->query("INSERT INTO gestion_paie (nom_intervenant, role, montant_contrat, acomptes_verses, date_dernier_versement, statut_paiement) 
                  VALUES ('$nom', '$role', '$montant', '$acompte', '$date', 'Partiel')");
    header("Location: stats_innovation.php");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Omega - Contrats RH</title>
</head>
<body class="bg-zinc-950 text-white p-6">
    <div class="max-w-xl mx-auto bg-zinc-900 p-8 rounded-3xl border border-blue-500 shadow-2xl">
        <h2 class="text-xl font-black mb-6 text-blue-500 uppercase italic">Nouveau Contrat Prestataire</h2>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="nom_intervenant" placeholder="Nom ou Entreprise" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700" required>
                <select name="role" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700">
                    <option>Chef de Projet</option>
                    <option>Maçon</option>
                    <option>Plombier</option>
                    <option>Électricien</option>
                    <option>Menuisier</option>
                    <option>Peintre</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] text-gray-500 uppercase font-bold">Montant Global du Contrat (FCFA)</label>
                <input type="number" name="montant_contrat" placeholder="Ex: 5000000" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] text-gray-500 uppercase font-bold">Acompte Versé</label>
                    <input type="number" name="acomptes_verses" value="0" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700">
                </div>
                <div>
                    <label class="text-[10px] text-gray-500 uppercase font-bold">Date de signature</label>
                    <input type="date" name="date_dernier_versement" value="<?= date('Y-m-d') ?>" class="w-full bg-zinc-800 p-3 rounded border border-zinc-700">
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 py-4 rounded-xl font-black uppercase">Enregistrer Engagement</button>
            <a href="stats_innovation.php" class="block text-center text-xs text-gray-500 mt-4 tracking-widest">RETOUR DASHBOARD</a>
        </form>
    </div>
</body>
</html>
