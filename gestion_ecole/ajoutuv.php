<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Matières et UV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Ajouter Matière / Unité de Valeur pour une Classe</h2>
    <form action="insert_matiere_uv.php" method="POST">
        <!-- Sélection de la classe -->
        <div class="mb-3">
            <label for="classe" class="form-label">Classe</label>
            <select class="form-select" name="classe_id" id="classe" required>
                <option value="">-- Sélectionner une classe --</option>
                <?php
                // Connexion à la base
                $conn = new mysqli("127.0.0.1", "root", "", "ecole");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $sql = "SELECT id, nom_class, code_class FROM classes ORDER BY nom_class";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='".$row['id']."'>".$row['nom_class']." (".$row['code_class'].")</option>";
                    }
                }
                $conn->close();
                ?>
            </select>
        </div>

        <!-- Matière -->
        <div class="mb-3">
            <label for="matiere" class="form-label">Matière</label>
            <input type="text" class="form-control" id="matiere" name="nom_matiere" placeholder="Nom de la matière" required>
        </div>

        <!-- Unité de valeur -->
        <div class="mb-3">
            <label for="uv" class="form-label">Unité de Valeur (UV)</label>
            <input type="text" class="form-control" id="uv" name="uv" placeholder="Nom de l'UV" required>
        </div>

        <!-- Semestre -->
        <div class="mb-3">
            <label for="semestre" class="form-label">Semestre</label>
            <select class="form-select" id="semestre" name="semestre" required>
                <option value="">-- Sélectionner un semestre --</option>
                <option value="1">Semestre 1</option>
                <option value="2">Semestre 2</option>
                <option value="3">Semestre 3</option>
                <option value="4">Semestre 4</option>
                <option value="5">Semestre 5</option>
                <option value="6">Semestre 6</option>
            </select>
        </div>

        <!-- Coefficient -->
        <div class="mb-3">
            <label for="coef" class="form-label">Coefficient</label>
            <input type="number" step="0.1" min="0" class="form-control" id="coef" name="coefficient" placeholder="Coefficient de l'UV" required>
        </div>

        <button type="submit" class="btn btn-primary">Ajouter Matière / UV</button>
    </form>
</div>
</body>
</html>

