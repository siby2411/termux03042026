<?php
// Fichier : modifier_partenaire.php
include 'db_connect.php';

$message = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer les données actuelles
$stmt = $conn->prepare("SELECT * FROM partenaires WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$partenaire = $result->fetch_assoc();

if (!$partenaire) {
    die("Partenaire introuvable.");
}

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $contact = $_POST['contact'];

    $update = $conn->prepare("UPDATE partenaires SET nom = ?, contact = ? WHERE id = ?");
    $update->bind_param("ssi", $nom, $contact, $id);

    if ($update->execute()) {
        $message = "<p style='color:green;'>Modification enregistrée ! <a href='liste_partenaires.php'>Retour à la liste</a></p>";
        // Actualiser les données affichées
        $partenaire['nom'] = $nom;
        $partenaire['contact'] = $contact;
    } else {
        $message = "<p style='color:red;'>Erreur lors de la mise à jour.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Partenaire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modifier le Partenaire</h1>
        <nav><a href="liste_partenaires.php">Annuler et Retour</a></nav>
    </header>
    <main style="max-width: 500px; margin: auto; padding: 20px;">
        <?php echo $message; ?>
        <form method="POST">
            <label>Nom du Partenaire :</label>
            <input type="text" name="nom" value="<?php echo htmlspecialchars($partenaire['nom']); ?>" required style="width:100%; padding:8px; margin-bottom:15px;">
            
            <label>Contact :</label>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($partenaire['contact']); ?>" style="width:100%; padding:8px; margin-bottom:15px;">
            
            <button type="submit" style="background:#3498db; color:white; border:none; padding:10px; width:100%; cursor:pointer;">Mettre à jour</button>
        </form>
    </main>
</body>
</html>
