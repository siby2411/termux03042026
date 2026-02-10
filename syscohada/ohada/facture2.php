<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Opération OHADA</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Ajouter une Opération OHADA</h1>
    <form action="insert_compte.php" method="GET">

        <!-- Champ pour la date de l'opération -->
        <div class="form-group">
            <label for="date_operation">Date de l'Opération :</label>
            <input type="date" id="date_operation" name="date_operation" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="classe">Sélectionnez la Classe :</label>
            <select id="classe" name="classe" class="form-control" onchange="fetchSousClasses()">
                <option value="">Sélectionnez une Classe</option>
                <?php
                $stmt = $pdo->query("SELECT * FROM classes_ohada");
                $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($classes as $classe) {
                    echo "<option value='" . htmlspecialchars($classe['id'], ENT_QUOTES) . "'>" . htmlspecialchars($classe['intitule_classe'], ENT_QUOTES) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="sous_classe">Sélectionnez la Sous-classe :</label>
            <select id="sous_classe" name="sous_classe" class="form-control" onchange="fetchComptes()">
                <option value="">Sélectionnez une Sous-classe</option>
                <!-- Les options seront remplies par AJAX -->
            </select>
        </div>

        <div class="form-group">
            <label for="num_compte">Numéro de Compte :</label>
            <select id="num_compte" name="num_compte" class="form-control">
                <option value="">Sélectionnez un Numéro de Compte</option>
                <!-- Les options seront remplies par AJAX -->
            </select>
        </div>

        <!-- Champ pour le montant de l'opération -->
        <div class="form-group">
            <label for="montant_operation">Montant de l'Opération :</label>
            <input type="number" id="montant_operation" name="montant_operation" class="form-control" placeholder="Entrez le montant" step="0.01" required>
        </div>

        <!-- Champ pour la description -->
        <div class="form-group">
            <label for="description">Description :</label>
            <textarea id="description" name="description" class="form-control" placeholder="Entrez la description de l'opération" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Ajouter Opération</button>
    </form>
</div>

<script>
    function fetchSousClasses() {
        var classe_id = $('#classe').val();
        if (classe_id) {
            $.get('fetch_sous_classes.php', { classe_id: classe_id }, function(data) {
                $('#sous_classe').html(data);
                $('#num_compte').html('<option value="">Sélectionnez un Numéro de Compte</option>'); // Réinitialiser le numéro de compte
            }).fail(function() {
                alert('Erreur lors du chargement des sous-classes');
            });
        } else {
            $('#sous_classe').html('<option value="">Sélectionnez une Sous-classe</option>');
            $('#num_compte').html('<option value="">Sélectionnez un Numéro de Compte</option>'); // Réinitialiser le numéro de compte
        }
    }

    function fetchComptes() {
        var sous_classe_id = $('#sous_classe').val();
        if (sous_classe_id) {
            $.get('fetch_comptes.php', { sous_classe_id: sous_classe_id }, function(data) {
                $('#num_compte').html(data);
            }).fail(function() {
                alert('Erreur lors du chargement des numéros de compte');
            });
        } else {
            $('#num_compte').html('<option value="">Sélectionnez un Numéro de Compte</option>');
        }
    }
</script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>