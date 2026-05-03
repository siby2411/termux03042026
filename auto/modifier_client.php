<?php
// Fichier : modifier_client.php
include 'db_connect.php';

$message = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Récupérer les informations actuelles du client
$stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    die("Erreur : Client introuvable.");
}

// 2. Traitement de la mise à jour lors de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $permis = $_POST['permis_conduire_num'];

    $update = $conn->prepare("UPDATE clients SET nom=?, prenom=?, email=?, telephone=?, adresse=?, permis_conduire_num=? WHERE id=?");
    $update->bind_param("ssssssi", $nom, $prenom, $email, $telephone, $adresse, $permis, $id);

    if ($update->execute()) {
        $message = "<p style='color:green;'>Les informations du client ont été mises à jour avec succès ! <a href='liste_clients.php'>Retour à la liste</a></p>";
        // Mettre à jour l'affichage local
        $client['nom'] = $nom;
        $client['prenom'] = $prenom;
        $client['email'] = $email;
        $client['telephone'] = $telephone;
        $client['adresse'] = $adresse;
        $client['permis_conduire_num'] = $permis;
    } else {
        $message = "<p style='color:red;'>Erreur lors de la mise à jour : " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Client - Omega Auto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modifier la fiche Client</h1>
        <nav><a href="liste_clients.php">Annuler et Retour</a></nav>
    </header>

    <main style="max-width: 600px; margin: auto; padding: 20px;">
        <?php echo $message; ?>
        
        <form method="POST">
            <label>Nom :</label>
            <input type="text" name="nom" value="<?php echo htmlspecialchars($client['nom']); ?>" required style="width:100%; margin-bottom:15px;">

            <label>Prénom :</label>
            <input type="text" name="prenom" value="<?php echo htmlspecialchars($client['prenom']); ?>" required style="width:100%; margin-bottom:15px;">

            <label>Email :</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" style="width:100%; margin-bottom:15px;">

            <label>Téléphone :</label>
            <input type="text" name="telephone" value="<?php echo htmlspecialchars($client['telephone']); ?>" style="width:100%; margin-bottom:15px;">

            <label>Adresse :</label>
            <textarea name="adresse" style="width:100%; margin-bottom:15px; height:80px;"><?php echo htmlspecialchars($client['adresse']); ?></textarea>

            <label>N° de Permis :</label>
            <input type="text" name="permis_conduire_num" value="<?php echo htmlspecialchars($client['permis_conduire_num']); ?>" style="width:100%; margin-bottom:20px;">

            <button type="submit" style="background:#3498db; color:white; border:none; padding:12px; width:100%; cursor:pointer; font-size:16px; border-radius:5px;">
                Enregistrer les modifications
            </button>
        </form>
    </main>
</body>
</html>
