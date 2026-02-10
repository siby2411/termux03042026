<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Cours</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
        }
        .logo {
            display: block;
            margin: 20px auto;
            max-width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <img src="4.jpg" alt="Omega Informatique" class="logo">
    </div>

    <div class="container mt-5">
        <h2 class="text-center">Rechercher un Module Syscohada</h2>
        
        <!-- Champ de recherche pour filtrer les intitulés -->
        <div class="form-group">
            <label for="search">Rechercher un cours :</label>
            <input type="text" id="search" class="form-control" placeholder="Tapez les premiers caractères...">
            <div id="suggestions" class="list-group mt-2"></div>
        </div>

        <!-- Sélection du cours pour soumission -->
        <form action="recherche_cours.php" method="POST">
            <div class="form-group">
                <label for="cours">Sélectionnez un module :</label>
                <select class="form-control" id="cours" name="cours_id" required>
                    <option value="">-- Sélectionnez un module --</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>

        <!-- Boutons pour modifier et ajouter un cours -->
        <div class="mt-3">
            <button onclick="window.location.href='update_cours.php'" class="btn btn-warning">Modifier un Cours</button>
            <button onclick="window.location.href='cours.html'" class="btn btn-success">Ajouter un Cours</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#search').on('input', function() {
                let searchQuery = $(this).val();

                if (searchQuery.length > 0) {
                    $.ajax({
                        url: 'search_course.php',
                        method: 'POST',
                        data: { query: searchQuery },
                        success: function(response) {
                            $('#suggestions').html(response);
                        }
                    });
                } else {
                    $('#suggestions').html('');
                }
            });

            // Lorsqu'un intitulé est cliqué, ajoutez-le dans le champ de sélection
            $(document).on('click', '.course-link', function(e) {
                e.preventDefault();
                const courseId = $(this).data('id');
                const courseTitle = $(this).text();

                // Ajoute le cours sélectionné à la liste déroulante
                $('#cours').html(`<option value="${courseId}" selected>${courseTitle}</option>`);
                
                // Vide le champ de suggestions et de recherche
                $('#suggestions').html('');
                $('#search').val('');
            });
        });
    </script>
</body>
</html>