<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Matières et UV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Toutes les Matières et Unités de Valeur (UV)</h2>

    <?php
    // Connexion à la base
    $conn = new mysqli("localhost", "root", "123", "ecole");
    if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

    // Requête pour récupérer classes, matières et UV
    $sql = "
        SELECT 
            c.nom_class, c.code_class,
            f.nom_filiere,
            m.nom_matiere, m.code_matiere,
            uv.nom_uv, uv.code_uv, uv.semestre, uv.coefficient
        FROM classes c
        JOIN filieres f ON c.filiere_id = f.id
        JOIN matieres m ON m.filiere_id = f.id
        LEFT JOIN unites_valeur uv ON uv.matiere_id = m.id
        ORDER BY c.nom_class, m.nom_matiere, uv.semestre
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table class='table table-bordered table-striped'>";
        echo "<thead>
                <tr>
                    <th>Classe</th>
                    <th>Code Classe</th>
                    <th>Filière</th>
                    <th>Matière</th>
                    <th>Code Matière</th>
                    <th>UV</th>
                    <th>Code UV</th>
                    <th>Semestre</th>
                    <th>Coefficient</th>
                </tr>
              </thead><tbody>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['nom_class']."</td>
                    <td>".$row['code_class']."</td>
                    <td>".$row['nom_filiere']."</td>
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
        echo "<div class='alert alert-info'>Aucune matière ou UV enregistrée pour le moment.</div>";
    }

    $conn->close();
    ?>
</div>
</body>
</html>

