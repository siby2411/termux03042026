<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "123", "ohada");

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifier si l'ID de l'opération a été passé dans l'URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Récupérer les données de l'opération à partir de l'ID
    $sql = "SELECT * FROM operation_diverse WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $operation = $result->fetch_assoc();
    } else {
        echo "Opération non trouvée.";
        exit();
    }
} else {
    echo "Aucun ID d'opération spécifié.";
    exit();
}

// Vérifier si le formulaire de mise à jour a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_operation = $_POST['date_operation'];
    $description = $_POST['description'];
    $montant = $_POST['montant'];
    $numero_compte = $_POST['numero_compte'];
    $statut = $_POST['statut'];

    // Préparer la requête de mise à jour
    $sql = "UPDATE operation_diverse SET date_operation = ?, description = ?, montant = ?, numero_compte = ?, statut = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssi", $date_operation, $description, $montant, $numero_compte, $statut, $id);

    // Exécuter la mise à jour
    if ($stmt->execute()) {
        echo "Opération mise à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour : " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour d'une opération diverse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Mise à jour d'une opération diverse</h2>
    
    <form method="POST" action="update_operation_diverse.php?id=<?php echo $id; ?>">
        <div class="mb-3">
            <label for="date_operation" class="form-label">Date de l'opération</label>
            <input type="date" class="form-control" id="date_operation" name="date_operation" value="<?php echo $operation['date_operation']; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" class="form-control" id="description" name="description" value="<?php echo $operation['description']; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="montant" class="form-label">Montant</label>
            <input type="number" step="0.01" class="form-control" id="montant" name="montant" value="<?php echo $operation['montant']; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="numero_compte" class="form-label">Numéro de Compte</label>
            <input type="text" class="form-control" id="numero_compte" name="numero_compte" value="<?php echo $operation['numero_compte']; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="statut" class="form-label">Statut</label>
            <select class="form-select" id="statut" name="statut" required>
                <option value="debit" <?php if ($operation['statut'] == 'debit') echo 'selected'; ?>>Débit</option>
                <option value="credit" <?php if ($operation['statut'] == 'credit') echo 'selected'; ?>>Crédit</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>