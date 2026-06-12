// Fichier: /modules/secretariat/orientation.php
<?php
require_once '../../config/database.php';
$pdo = getPDO();

// Requête pour voir qui est où et dans quel service
$stmt = $pdo->query("SELECT v.*, s.nom_service 
                     FROM vue_medecin_salle v 
                     JOIN services s ON v.service_id = s.id 
                     ORDER BY v.batiment_nom, v.salle_nom");
$disponibilites = $stmt->fetchAll();
?>

<div class="card p-4">
    <h3>Orientation des Patients</h3>
    <table class="table">
        <thead>
            <tr><th>Médecin</th><th>Service</th><th>Bâtiment</th><th>Salle</th><th>Statut</th></tr>
        </thead>
        <tbody>
            <?php foreach($disponibilites as $d): ?>
            <tr>
                <td>Dr. <?= $d['medecin_nom'] ?></td>
                <td><?= $d['nom_service'] ?></td>
                <td><?= $d['batiment_nom'] ?></td>
                <td><?= $d['salle_nom'] ?></td>
                <td><span class="badge bg-success">Disponible</span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
