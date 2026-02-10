<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Matière / UV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Ajouter Matière / Unité de Valeur (UV) pour une Classe</h2>
    <form action="insert_matiere_uv.php" method="POST">
        <!-- Classe -->
        <div class="mb-3">
            <label for="classe" class="form-label">Classe</label>
            <select name="classe_id" id="classe" class="form-select" required>
                <option value="">-- Sélectionner une classe --</option>
                <?php
                $conn = new mysqli("localhost", "root", "123", "ecole");
                if ($conn->connect_error) die("Erreur: " . $conn->connect_error);

                $res = $conn->query("SELECT id, nom_class FROM classes ORDER BY nom_class");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['nom_class']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Matière -->
        <div class="mb-3">
            <label for="matiere" class="form-label">Matière</label>
            <select name="matiere_id" id="matiere" class="form-select" required onchange="loadUVs(this.value)">
                <option value="">-- Sélectionner une matière --</option>
                <?php
                $res = $conn->query("SELECT id, nom_matiere FROM matieres ORDER BY nom_matiere");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['nom_matiere']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- UV -->
        <div class="mb-3">
            <label for="uv" class="form-label">Unité de Valeur (UV)</label>
            <select name="uv_id" id="uv" class="form-select" required>
                <option value="">-- Sélectionner une UV --</option>
                <!-- Les UV seront peuplées dynamiquement via JS -->
            </select>
        </div>

        <!-- Semestre -->
        <div class="mb-3">
            <label for="semestre" class="form-label">Semestre</label>
            <select name="semestre" id="semestre" class="form-select" required>
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
            <input type="number" step="0.1" min="0" name="coefficient" id="coef" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Ajouter UV</button>
    </form>
</div>

<script>
// Fonction pour charger les UV d'une matière via AJAX
function loadUVs(matiereId) {
    const uvSelect = document.getElementById('uv');
    uvSelect.innerHTML = '<option value="">-- Chargement... --</option>';

    if (!matiereId) {
        uvSelect.innerHTML = '<option value="">-- Sélectionner une UV --</option>';
        return;
    }

    fetch('get_uv.php?matiere_id=' + matiereId)
        .then(response => response.json())
        .then(data => {
            uvSelect.innerHTML = '<option value="">-- Sélectionner une UV --</option>';
            data.forEach(uv => {
                uvSelect.innerHTML += `<option value="${uv.id}">${uv.nom_uv}</option>`;
            });
        })
        .catch(err => console.error('Erreur AJAX:', err));
}
</script>
</body>
</html>

