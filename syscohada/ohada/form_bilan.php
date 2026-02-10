<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie Comptable</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5">Saisie des opérations comptables</h2>
    <form action="ajouter_ecriture.php" method="POST">
        <div class="form-group">
            <label for="date_operation">Date de l'opération :</label>
            <input type="date" class="form-control" id="date_operation" name="date_operation" required>
        </div>
        <div class="form-group">
            <label for="numero_compte">Numéro de compte :</label>
            <input type="text" class="form-control" id="numero_compte" name="numero_compte" required>
        </div>
        <div class="form-group">
            <label for="description">Description :</label>
            <input type="text" class="form-control" id="description" name="description" required>
        </div>
        <div class="form-group">
            <label for="debit">Débit :</label>
            <input type="number" class="form-control" id="debit" name="debit">
        </div>
        <div class="form-group">
            <label for="credit">Crédit :</label>
            <input type="number" class="form-control" id="credit" name="credit">
        </div>
        <button type="submit" class="btn btn-primary">Ajouter l'écriture</button>
    </form>
</div>
</body>
</html