<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu de Navigation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Mon Application</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="reqcompte.html">Formulaire de Comptes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="req.php">Requêtes Comptes</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Bienvenue sur l'application de gestion de comptes</h1>
        <p>Utilisez le menu de navigation pour accéder aux formulaires.</p>

        <div class="form-group">
            <label for="classeSelect">Sélectionnez une classe :</label>
            <select id="classeSelect" class="form-control">
                <option value="">Sélectionnez une classe</option>
            </select>
        </div>

        <div class="form-group">
            <label for="compteSelect">Sélectionnez un numéro de compte :</label>
            <select id="compteSelect" class="form-control" disabled>
                <option value="">Sélectionnez un numéro de compte</option>
            </select>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Récupérer les classes
            $.ajax({
                url: 'get_classes.php',
                method: 'GET',
                success: function(data) {
                    console.log(data); // Ajoutez cette ligne pour voir la réponse dans la console
                    const classes = JSON.parse(data);
                    classes.forEach(function(classe) {
                        $('#classeSelect').append(`<option value="${classe.id}">${classe.nom}</option>`);
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Erreur lors de la récupération des classes :", error);
                }
            });

            // Écouteur d'événements pour le changement de classe
            $('#classeSelect').change(function() {
                const classeId = $(this).val();
                $('#compteSelect').empty().append('<option value="">Sélectionnez un numéro de compte</option>').prop('disabled', true);

                if (classeId) {
                    // Récupérer les numéros de compte en fonction de la classe
                    $.ajax({
                        url: 'get_comptes.php', // Assurez-vous que ce fichier existe et est correctement configuré
                        method: 'GET',
                        data: { id_classe: classeId },
                        success: function(data) {
                            const comptes = JSON.parse(data);
                            comptes.forEach(function(compte) {
                                $('#compteSelect').append(`<option value="${compte.numero_compte}">${compte.numero_compte} - ${compte.nom}</option>`);
                            });
                            $('#compteSelect').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error("Erreur lors de la récupération des comptes :", error);
                        }
                    });
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>