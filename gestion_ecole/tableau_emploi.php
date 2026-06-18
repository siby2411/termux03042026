<?php
// Vérification de la connexion déjà ouverte par carte_etudiant.php
$query = "SELECT e.jour, e.heure_debut, e.heure_fin, e.salle, u.nom_uv, p.nom as prof_nom 
          FROM emploi_temps e
          JOIN affectations a ON e.affectation_id = a.id
          JOIN uvs u ON a.uv_id = u.id
          JOIN professeurs p ON a.prof_id = p.id_prof
          WHERE a.classe_id = (SELECT classe_id FROM etudiants WHERE id = $id)
          ORDER BY FIELD(e.jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'), e.heure_debut";

$res_emploi = $conn->query($query);
?>
<div class="table-responsive">
    <table class="table table-hover table-bordered shadow-sm">
        <thead class="table-primary">
            <tr>
                <th>Jour</th>
                <th>Heure</th>
                <th>Matière</th>
                <th>Professeur</th>
                <th>Salle</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $res_emploi->fetch_assoc()): ?>
            <tr>
                <td class="fw-bold"><?= htmlspecialchars($row['jour']) ?></td>
                <td><?= substr($row['heure_debut'], 0, 5) ?> - <?= substr($row['heure_fin'], 0, 5) ?></td>
                <td><?= htmlspecialchars($row['nom_uv']) ?></td>
                <td><?= htmlspecialchars($row['prof_nom']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['salle']) ?></span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
