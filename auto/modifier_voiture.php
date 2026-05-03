<?php
// Fichier : modifier_voiture.php
include 'db_connect.php';

$message = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer la voiture
$stmt = $conn->prepare("SELECT * FROM voitures WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$voiture = $stmt->get_result()->fetch_assoc();

if (!$voiture) { die("Voiture introuvable."); }

// Récupérer la liste des partenaires pour le menu déroulant
$partenaires = $conn->query("SELECT id, nom FROM partenaires");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $annee = intval($_POST['annee']);
    $prix = floatval($_POST['prix_journalier']);
    $statut = $_POST['statut'];
    $partenaire_id = intval($_POST['partenaire_id']);

    $sql = "UPDATE voitures SET marque=?, modele=?, annee=?, prix_journalier=?, statut=?, partenaire_id=? WHERE id=?";
    $update = $conn->prepare($sql);
    $update->bind_param("ssidssi", $marque, $modele, $annee, $prix, $statut, $partenaire_id, $id);

    if ($update->execute()) {
        $message = "<p style='color:green;'>Voiture mise à jour ! <a href='liste_voitures.php'>Retour à la liste</a></p>";
    } else {
        $message = "<p style='color:red;'>Erreur : " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Voiture</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modifier la Voiture</h1>
        <nav><a href="liste_voitures.php">Annuler</a></nav>
    </header>
    <main style="max-width: 600px; margin: auto; padding: 20px;">
        <?php echo $message; ?>
        <form method="POST">
            <label>Marque :</label>
            <input type="text" name="marque" value="<?php echo htmlspecialchars($voiture['marque']); ?>" required style="width:100%; margin-bottom:10px;">

            <label>Modèle :</label>
            <input type="text" name="modele" value="<?php echo htmlspecialchars($voiture['modele']); ?>" required style="width:100%; margin-bottom:10px;">

            <label>Année :</label>
            <input type="number" name="annee" value="<?php echo $voiture['annee']; ?>" style="width:100%; margin-bottom:10px;">

            <label>Prix Journalier (€) :</label>
            <input type="number" step="0.01" name="prix_journalier" value="<?php echo $voiture['prix_journalier']; ?>" style="width:100%; margin-bottom:10px;">

            <label>Statut :</label>
            <select name="statut" style="width:100%; margin-bottom:10px;">
                <option value="Disponible" <?php if($voiture['statut'] == 'Disponible') echo 'selected'; ?>>Disponible</option>
                <option value="Loué" <?php if($voiture['statut'] == 'Loué') echo 'selected'; ?>>Loué</option>
                <option value="Maintenance" <?php if($voiture['statut'] == 'Maintenance') echo 'selected'; ?>>Maintenance</option>
            </select>

            <label>Partenaire :</label>
            <select name="partenaire_id" required style="width:100%; margin-bottom:20px;">
                <?php while($p = $partenaires->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php if($p['id'] == $voiture['partenaire_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($p['nom']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" style="background:#2ecc71; color:white; border:none; padding:10px; width:100%; cursor:pointer;">Sauvegarder les modifications</button>
        </form>
    </main>
</body>
</html>
