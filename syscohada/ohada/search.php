<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification de Compte</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <h1>Vérifier si un Numéro de Compte Existe</h1>

    <form>
        <label for="num_compte">Saisir un numéro de compte :</label>
        <input type="text" id="num_compte" placeholder="Entrez le numéro de compte" required>
    </form>

    <div id="result"></div>

    <script type="text/javascript">
        $(document).ready(function() {
            // Quand l'utilisateur termine la saisie dans le champ
            $('#num_compte').on('input', function() {
                var numCompte = $(this).val();

                if (numCompte !== "") {
                    // Effectuer la requête AJAX
                    $.ajax({
                        url: "search1.php",
                        method: "POST",
                        data: {num_compte: numCompte},
                        success: function(response) {
                            $('#result').html(response);
                        }
                    });
                } else {
                    $('#result').html(''); // Vider le résultat si le champ est vide
                }
            });
        });
    </script>

<H1> Ajouter Numéro de Compte </H1>


 <form action="insert_compte.php" method="POST">
        <label for="num_compte">Numéro de Compte :</label>
        <input type="text" id="num_compte" name="num_compte" required><br><br>
        
        <label for="intitule">Intitulé :</label>
        <input type="text" id="intitule" name="intitule" required><br><br>
        
        <label for="sous_classe_id">Sous-classe ID (optionnel) :</label>
        <input type="number" id="sous_classe_id" name="sous_classe_id"><br><br>
        
        <label for="description">Description :</label>
        <textarea id="description" name="description"></textarea><br><br>
        
        <button type="submit">Ajouter le Compte</button>
    </form>






</body>
</html>