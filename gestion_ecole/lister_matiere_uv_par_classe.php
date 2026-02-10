<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Matières et UV par Classe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Liste des Matières et UV pour une Classe</h2>

    <?php
    // Connexion base de données
    $conn = new mysqli("localhost", "root", "123", "ecole");
    if ($conn->connect_error) die("Connexion échouée: " . $conn->connect_error);

    // Récupération des classes pour dropdown
    $sql_classes = "SELECT id, nom_class, code_class FROM classes ORDER BY nom_class";
    $result_classes = $conn->query($sql_classes);

    $classe_id_selected = isset($_GET['classe_id']) ? intval($_GET['classe_id']) : 0;
    ?>

    <!-- Formulaire sélection classe -->
    <form method="GET" class="mb-4">
        <div class="mb-3">
            <label for="classe" class="form-label">Classe</label>
            <select class="form-select" name="classe_id" id="classe" required onchange="this.form.submit()">
                <option value="">-- Sélectionner une classe --</option>
                <?php
                if ($result_classes->num_rows > 0) {
                    while($row = $result_classes->fetch_assoc()) {
                        $selected = ($row['id'] == $classe_id_selected) ? "selected" : "";
                        echo "<option value='".$row['id']."' $selected>".$row['nom_class']." (".$row['code_class'].")</option>";
                    }
                }
                ?>
            </select>
        </div>
    </form>

    <?php
    if ($classe_id_selected > 0) {
        // Récupérer filiere de la classe
        $stmt = $conn->prepare("
            SELECT f.id AS filiere_id, f.nom_filiere
            FROM classes c
            JOIN filieres f ON c.filiere_id = f.id
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $classe_id_selected);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $filiere = $res->fetch_assoc();
            $filiere_id = $filiere['filiere_id'];

            // Récupérer matières et UV pour cette filière
            $stmt2 = $conn->prepare("
                SELECT m.nom_matiere, m.code_matiere, uv.nom_uv, uv.code_uv, uv.semestre, uv.coefficient
                FROM matieres m
                LEFT JOIN unites_valeur uv ON uv.matiere_id = m.id
                WHERE m.filiere_id = ?
                ORDER BY m.nom_matiere, uv.semestre
            ");
            $stmt2->bind_param("i", $filiere_id);
            $stmt2->execute();
            $result_uv = $stmt2->get_result();

            if ($result_uv->num_rows > 0) {
                echo "<table class='table table-bordered'>";
                echo "<thead><tr>
                        <th>Matière</th>
                        <th>Code Matière</th>
                        <th>UV</th>
                        <th>Code UV</th>
                        <th>Semestre</th>
                        <th>Coefficient</th>
                      </tr></thead><tbody>";

                while($row = $result_uv->fetch_assoc()) {
                    echo "<tr>
                        <td>".$row['nom_matiere']."</td>
                        <td>".$row['code_matiere']."</td>
                        <td>".($row['nom_uv'] ?? "-")."</td>
                        <td>".($row['code_uv'] ?? "-")."</td>
                        <td>".($row['semestre'] ?? "-")."</td>
                        <td>".($row['coefficient'] ?? "-")."</td>
                    </tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "<div class='alert alert-info'>Aucune matière ou UV trouvée pour cette classe.</div>";
            }
            $stmt2->close();
        } else {
            echo "<div class='alert alert-danger'>Classe introuvable.</div>";
        }
        $stmt->close();
    }

    $conn->close();
    ?>
</div>
</body>
</html>

