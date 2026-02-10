<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Numéros de Compte</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Recherche de Numéros de Compte</h2>
        <form id="searchForm">
            <div class="form-group">
                <label for="num_compte">Saisissez le premier chiffre du numéro de compte :</label>
                <input type="text" class="form-control" id="num_compte" placeholder="Ex: 7" required>
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
        <ul id="resultList" class="list-group mt-3"></ul>
    </div>

    <script>
        $(document).ready(function(){
            $('#searchForm').on('submit', function(e){
                e.preventDefault(); // Empêche l'envoi normal du formulaire
                var input = $('#num_compte').val().trim(); // Récupère la valeur saisie et supprime les espaces

                if (input.length === 0) {
                    alert("Veuillez saisir un chiffre."); // Vérifie que quelque chose a été saisi
                    return;
                }

                // Envoie une requête AJAX
                $.ajax({
                    url: 'search_accounts.php', // Le fichier PHP qui va traiter la requête
                    type: 'GET',
                    data: { num_compte: input },
                    dataType: 'json', // Indique que nous attendons une réponse JSON
                    success: function(response) {
                        console.log("Réponse reçue:", response); // Ajoute cette ligne pour déboguer
                        $('#resultList').empty(); // Vide la liste des résultats
                        if (response.length > 0) {
                            response.forEach(function(account) {
                                $('#resultList').append(
                                    '<li class="list-group-item">' +
                                        '<strong>Numéro de compte:</strong> ' + account.num_compte + '<br>' +
                                        '<strong>Intitulé:</strong> ' + account.intitule + '<br>' +
                                        '<strong>Sous-classe ID:</strong> ' + (account.sous_classe_id ? account.sous_classe_id : 'N/A') + '<br>' +
                                        '<strong>Description:</strong> ' + (account.description ? account.description : 'N/A') +
                                    '</li>'
                                );
                            });
                        } else {
                            $('#resultList').append('<li class="list-group-item">Aucun numéro de compte trouvé.</li>');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Erreur AJAX:", textStatus, errorThrown); // Ajoute cette ligne pour déboguer
                        $('#resultList').empty().append('<li class="list-group-item">Erreur lors de la recherche.</li>');
                    }
                });
            });
        });
    </script>
</body>
</html>