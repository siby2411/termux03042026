<?php
// Fichier : ajouter_partenaire.php
include 'db_connect.php';

$message = "";

// Traitement du formulaire à l'envoi (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $contact = $_POST['contact'];

    // Préparation de la requête pour éviter les injections SQL
    $stmt = $conn->prepare("INSERT INTO partenaires (nom, contact) VALUES (?, ?)");
    $stmt->bind_param("ss", $nom, $contact);

    if ($stmt->execute()) {
        $message = "<div style='color:green; padding:10px; border:1px solid green; margin-bottom:10px;'>
                        ✅ Partenaire <strong>" . htmlspecialchars($nom) . "</strong> ajouté avec succès ! 
                        <a href='ajouter_voiture.php'>Ajouter une voiture maintenant</a>
                    </div>";
    } else {
        $message = "<div style='color:red; padding:10px; border:1px solid red; margin-bottom:10px;'>
                        ❌ Erreur lors de l'ajout : " . $conn->error . "
                    </div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Partenaire - Omega Auto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Gestion des Partenaires</h1>
        <nav>
            <a href="index.php">Accueil</a> | 
            <a href="liste_partenaires.php">Liste Partenaires</a> |
            <a href="ajouter_voiture.php">Ajouter Voiture</a>
        </nav>
    </header>

    <main style="max-width: 500px; margin: 20px auto; background: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2>Inscrire un nouveau partenaire</h2>
        
        <?php echo $message; ?>

        <form action="ajouter_partenaire.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label for="nom" style="display:block; margin-bottom:5px;">Nom de l'agence ou propriétaire :</label>
                <input type="text" id="nom" name="nom" required style="width:100%; padding:8px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="contact" style="display:block; margin-bottom:5px;">Contact (Email ou Téléphone) :</label>
                <input type="text" id="contact" name="contact" placeholder="ex: +221 77..." style="width:100%; padding:8px; box-sizing: border-box;">
            </div>

            <button type="submit" style="background:#2ecc71; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; width:100%; font-size:16px;">
                Enregistrer le partenaire
            </button>
        </form>
    </main>

    <footer style="text-align:center; margin-top:20px; color:#666;">
        <p>&copy; <?php echo date("Y"); ?> Omega Market Auto</p>
    </footer>
</body>
</html>
