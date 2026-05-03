<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_connect_ecole.php';
$conn = db_connect_ecole();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Tarif</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white text-center">
            <h3 class="m-0">Ajouter un Tarif</h3>
            <small>Scolarité + Droit d'inscription</small>
        </div>

        <div class="card-body">

            <form method="POST" action="insert_tarif.php" class="row g-3">

                <!-- Sélection Classe -->
                <div class="col-md-12">
                    <label class="form-label">Classe</label>
                    <select name="classe_id" class="form-select" required>
                        <option value="">-- Sélectionner une classe --</option>

                        <?php
                        $sql = "SELECT id, nom_class FROM classes ORDER BY nom_class ASC";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()):
                        ?>
                            <option value="<?= $row['id'] ?>"><?= $row['nom_class'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Montant scolarité -->
                <div class="col-md-6">
                    <label class="form-label">Montant Scolarité</label>
                    <input type="number" name="montant_scolarite" class="form-control" required>
                </div>

                <!-- Droits d'inscription -->
                <div class="col-md-6">
                    <label class="form-label">Droit d'inscription</label>
                    <input type="number" name="droit_inscription" class="form-control" required>
                </div>

                <!-- Valider -->
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-success px-4 py-2">
                        Enregistrer le Tarif
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>

