<?php
// Connexion à la base de données MySQL
$servername = "localhost";
$username = "root";
$password = "123";
$dbname = "ohada";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Vérifier que l'ID est fourni
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Récupérer les données actuelles de la formule
    $stmt = $conn->prepare("SELECT * FROM formule_comptabilite WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $formule = $result->fetch_assoc();

    // Fermer la requête préparée
    $stmt->close();
}

// Traitement de la mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $libelle = $_POST['libelle'];
    $formule_text = $_POST['formule'];

    // Préparer la requête de mise à jour
    $stmt = $conn->prepare("UPDATE formule_comptabilite SET libelle = ?, formule = ? WHERE id = ?");
    $stmt->bind_param("ssi", $libelle, $formule_text, $id);

    if ($stmt->execute()) {
        echo "Formule mise à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour de la formule : " . $stmt->error;
    }

    // Rediriger vers la liste après mise à jour
    header("Location: liste_formule.php");

    // Fermer la requête préparée
    $stmt->close();
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mettre à jour la Formule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Mettre à jour la Formule</h1>
        <form action="update_formule.php" method="post" class="mt-3">
            <input type="hidden" name="id" value="<?php echo $formule['id']; ?>">

            <div class="mb-3">
                <label for="libelle" class="form-label">Libellé</label>
                <input type="text" class="form-control" id="libelle" name="libelle" value="<?php echo $formule['libelle']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="formule" class="form-label">Formule</label>
                <textarea class="form-control" id="formule" name="formule" rows="3" required><?php echo $formule['formule']; ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
</body>
</html>