<?php
include 'db_connect.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $permis = $_POST['permis'];

    $stmt = $conn->prepare("INSERT INTO clients (nom, prenom, email, telephone, adresse, permis_conduire_num) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nom, $prenom, $email, $telephone, $adresse, $permis);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Client ajouté avec succès ! <a href='liste_clients.php'>Voir la liste</a></p>";
    } else {
        $message = "<p style='color:red;'>Erreur : " . $conn->error . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Client</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header><h1>Nouveau Client</h1><nav><a href="liste_clients.php">Retour Liste</a></nav></header>
    <main style="max-width: 500px; margin: auto; padding: 20px;">
        <?php echo $message; ?>
        <form method="POST">
            <label>Nom :</label><input type="text" name="nom" required style="width:100%; margin-bottom:10px;">
            <label>Prénom :</label><input type="text" name="prenom" required style="width:100%; margin-bottom:10px;">
            <label>Email :</label><input type="email" name="email" style="width:100%; margin-bottom:10px;">
            <label>Téléphone :</label><input type="text" name="telephone" style="width:100%; margin-bottom:10px;">
            <label>Adresse :</label><textarea name="adresse" style="width:100%; margin-bottom:10px;"></textarea>
            <label>N° Permis de conduire :</label><input type="text" name="permis" style="width:100%; margin-bottom:10px;">
            <button type="submit" style="width:100%; padding:10px; background:#2ecc71; color:white; border:none; cursor:pointer;">Enregistrer le Client</button>
        </form>
    </main>
</body>
</html>
