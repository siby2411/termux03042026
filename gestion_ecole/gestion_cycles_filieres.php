<?php
// --------------------------------------------------------
// Connexion à la base de données
// --------------------------------------------------------
$host = "localhost";
$user = "root";
$pass = "123";
$dbname = "ecole";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// --------------------------------------------------------
// Ajout d'un cycle
// --------------------------------------------------------
if (isset($_POST['add_cycle'])) {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $conn->query("INSERT INTO cycles (nom, description) VALUES ('$nom', '$description')");
}

// --------------------------------------------------------
// Ajout d'une filière
// --------------------------------------------------------
if (isset($_POST['add_filiere'])) {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $cycle_id = intval($_POST['cycle_id']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);

    $conn->query("INSERT INTO filieres (nom, cycle_id, details) VALUES ('$nom', $cycle_id, '$details')");
}

// --------------------------------------------------------
// Récupération des cycles + filières
// --------------------------------------------------------
$cycles = $conn->query("
    SELECT * FROM cycles ORDER BY id ASC
");

$filieres = $conn->query("
    SELECT f.*, c.nom AS cycle_nom 
    FROM filieres f
    JOIN cycles c ON f.cycle_id = c.id
    ORDER BY c.id ASC, f.nom ASC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Cycles et Filières</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

    <h1 class="text-3xl font-bold mb-6 text-center text-indigo-700">
        Gestion des Cycles & Filières
    </h1>

    <!-- ==================== FORMULAIRES ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- FORMULAIRE AJOUT CYCLE -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4 text-indigo-600">Ajouter un Cycle</h2>

            <form method="POST">
                <input type="hidden" name="add_cycle" value="1">

                <label class="block mb-2 font-medium">Nom du Cycle</label>
                <input type="text" name="nom" required
                       class="w-full p-2 border rounded mb-3" placeholder="Ex: Licence">

                <label class="block mb-2 font-medium">Description</label>
                <textarea name="description" class="w-full p-2 border rounded mb-3"></textarea>

                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    Ajouter
                </button>
            </form>
        </div>

        <!-- FORMULAIRE AJOUT FILIÈRE -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4 text-indigo-600">Ajouter une Filière</h2>

            <form method="POST">
                <input type="hidden" name="add_filiere" value="1">

                <label class="block mb-2 font-medium">Nom de la Filière</label>
                <input type="text" name="nom" required
                       class="w-full p-2 border rounded mb-3" placeholder="Ex: Informatique">

                <label class="block mb-2 font-medium">Cycle associé</label>
                <select name="cycle_id" class="w-full p-2 border rounded mb-3" required>
                    <option value="">-- Choisir un cycle --</option>
                    <?php while ($c = $cycles->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= $c['nom'] ?></option>
                    <?php endwhile; ?>
                </select>

                <label class="block mb-2 font-medium">Details</label>
                <textarea name="details" class="w-full p-2 border rounded mb-3"></textarea>

                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    Ajouter
                </button>
            </form>
        </div>

    </div>

    <!-- ==================== LISTES ==================== -->
    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- LISTE DES CYCLES -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4 text-indigo-600">Liste des Cycles</h2>

            <table class="w-full border">
                <tr class="bg-gray-200">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Nom</th>
                    <th class="p-2 border">Description</th>
                </tr>

                <?php
                $cycles->data_seek(0); // reset pointer
                while ($c = $cycles->fetch_assoc()):
                ?>
                <tr>
                    <td class="p-2 border"><?= $c['id'] ?></td>
                    <td class="p-2 border"><?= $c['nom'] ?></td>
                    <td class="p-2 border"><?= $c['description'] ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- LISTE DES FILIÈRES -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4 text-indigo-600">Liste des Filières</h2>

            <table class="w-full border">
                <tr class="bg-gray-200">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Filière</th>
                    <th class="p-2 border">Cycle</th>
                    <th class="p-2 border">Détails</th>
                </tr>

                <?php while ($f = $filieres->fetch_assoc()): ?>
                <tr>
                    <td class="p-2 border"><?= $f['id'] ?></td>
                    <td class="p-2 border"><?= $f['nom'] ?></td>
                    <td class="p-2 border text-indigo-600 font-semibold"><?= $f['cycle_nom'] ?></td>
                    <td class="p-2 border"><?= $f['details'] ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>

</body>
</html>

