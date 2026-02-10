<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "123";
$dbname = "ohada";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupérer l'ID de l'écriture à modifier
$id = $_GET['id'];

// Récupérer les détails de l'écriture
$sql = "SELECT * FROM ecritures WHERE id = $id";
$result = $conn->query($sql);
$ecriture = $result->fetch_assoc();

if (!$ecriture) {
    die("Écriture non trouvée pour l'ID : " . $id);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_operation = $_POST['date_operation'];
    $compte_debit = $_POST['compte_debit'];
    $montant_debit = $_POST['montant_debit'];
    $compte_credit = $_POST['compte_credit'];
    $montant_credit = $_POST['montant_credit'];
    $description = $_POST['description'];

    // Requête de mise à jour
    $sql_update = "UPDATE ecritures 
                   SET date_operation = '$date_operation', compte_debit = '$compte_debit', 
                       solde_debiteur = '$montant_debit', compte_credit = '$compte_credit', 
                       solde_crediteur = '$montant_credit', description = '$description'
                   WHERE id = $id";

    if ($conn->query($sql_update) === TRUE) {
        echo "L'écriture a été mise à jour avec succès.";
    } else {
        echo "Erreur de mise à jour : " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Modifier Écriture</title>
</head>
<body>
<div class="container mt-5">
    <h2>Modifier l'Écriture #<?php echo $ecriture['id']; ?></h2>
    <form method="POST">
        <div class="mb-3">
            <label for="date_operation" class="form-label">Date de l'opération</label>
            <input type="date" class="form-control" id="date_operation" name="date_operation" value="<?php echo $ecriture['date_operation']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="compte_debit" class="form-label">Compte Débit</label>
            <input type="text" class="form-control" id="compte_debit" name="compte_debit" value="<?php echo $ecriture['compte_debit']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="montant_debit" class="form-label">Montant Débit</label>
            <input type="number" step="0.01" class="form-control" id="montant_debit" name="montant_debit" value="<?php echo $ecriture['solde_debiteur']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="compte_credit" class="form-label">Compte Crédit</label>
            <input type="text" class="form-control" id="compte_credit" name="compte_credit" value="<?php echo $ecriture['compte_credit']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="montant_credit" class="form-label">Montant Crédit</label>
            <input type="number" step="0.01" class="form-control" id="montant_credit" name="montant_credit" value="<?php echo $ecriture['solde_crediteur']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $ecriture['description']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour l'écriture</button>
    </form>
    <a href="liste_ecriture.php" class="btn btn-secondary mt-3">Retour à la liste des écritures</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>