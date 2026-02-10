<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Écriture Comptable</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <h1>Ajouter une Écriture Comptable</h1>

    <form action="inserer_ecriture.php" method="POST">
        <label for="date_operation">Date de l'opération :</label>
        <input type="date" id="date_operation" name="date_operation" required><br><br>

        <label for="description">Description :</label>
        <textarea id="description" name="description"></textarea><br><br>

        <label for="montant">Montant :</label>
        <input type="number" step="0.01" id="montant" name="montant" required><br><br>

        <label for="compte_debit">Compte Débit :</label>
        <input type="text" id="compte_debit" name="compte_debit" required>
        <span id="result_debit"></span><br><br>

        <label for="compte_credit">Compte Crédit :</label>
        <input type="text" id="compte_credit" name="compte_credit" required>
        <span id="result_credit"></span><br><br>

        <button type="submit">Ajouter l'Écriture</button>
    </form>

    <script type="text/javascript">
        $(document).ready(function() {
            // Vérifier l'existence du Compte Débit
            $('#compte_debit').on('input', function() {
                var numCompte = $(this).val();

                if (numCompte !== "") {
                    $.ajax({
                        url: "search1.php", // Requête vers le fichier PHP de recherche
                        method: "POST",
                        data: { num_compte: numCompte },
                        success: function(response) {
                            $('#result_debit').html(response);
                        }
                    });
                } else {
                    $('#result_debit').html(''); // Réinitialiser si vide
                }
            });

            // Vérifier l'existence du Compte Crédit
            $('#compte_credit').on('input', function() {
                var numCompte = $(this).val();

                if (numCompte !== "") {
                    $.ajax({
                        url: "search1.php", // Requête vers le fichier PHP de recherche
                        method: "POST",
                        data: { num_compte: numCompte },
                        success: function(response) {
                            $('#result_credit').html(response);
                        }
                    });
                } else {
                    $('#result_credit').html(''); // Réinitialiser si vide
                }
            });
        });
    </script>
</body>
</html>