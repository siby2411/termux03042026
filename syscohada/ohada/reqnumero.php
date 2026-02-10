<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Recherche de Compte OHADA</title>
</head>
<body>
<div class="container mt-5">
    <h2>Recherche de Compte OHADA</h2>
    <form method="GET" action="reqnum.php">
        <div class="form-group">
            <label for="num_compte">Numéro de compte :</label>
            <input type="text" class="form-control" id="num_compte" name="num_compte" required>
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    <?php
    // Ici, vous pouvez inclure le code PHP pour afficher les détails du compte, comme vous l'avez déjà fait.
    ?>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>