<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Compte</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Recherche de Compte par Description</h2>
    <form id="rechercheForm">
        <div class="form-group">
            <label for="description">Description du compte :</label>
            <input type="text" class="form-control" id="description" name="description" placeholder="Entrez les premiers caractères de la description">
        </div>
    </form>
    
    <div id="resultats">
        <!-- Les résultats de la recherche seront affichés ici -->
    </div>
</div>

<script>
$(document).ready(function() {
    // Lorsque l'utilisateur tape dans le champ de description
    $('#description').on('keyup', function() {
        var description = $(this).val();

        // Faire une requête Ajax si la description n'est pas vide
        if (description.length > 0) {
            $.ajax({
                url: 'reqdesc.php',
                type: 'GET',
                data: { description: description },
                success: function(data) {
                    var resultats = JSON.parse(data);  // Convertir la réponse en JSON
                    var html = '<ul class="list-group">';

                    // Générer les résultats
                    if (resultats.length > 0) {
                        resultats.forEach(function(compte) {
                            html += '<li class="list-group-item">' + compte.num_compte + ' - ' + compte.intitule + '</li>';
                        });
                    } else {
                        html += '<li class="list-group-item">Aucun résultat trouvé</li>';
                    }

                    html += '</ul>';
                    $('#resultats').html(html);  // Afficher les résultats
                },
                error: function(xhr, status, error) {
                    console.error("Erreur lors de l'appel Ajax :", error);
                }
            });
        } else {
            $('#resultats').html(''); // Effacer les résultats si l'input est vide
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>