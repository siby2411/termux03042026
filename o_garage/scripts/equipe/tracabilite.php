<?php require_once '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-fingerprint text-primary me-2"></i>Journal de Traçabilité Technique</h2>
        <span class="badge bg-dark p-2">Total Interventions : <?= $db->query("SELECT COUNT(*) FROM fiches_intervention")->fetchColumn() ?></span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Code Mec.</th>
                        <th>Technicien</th>
                        <th>Véhicule</th>
                        <th>Intervention</th>
                        <th>Date</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query = "SELECT f.*, p.code_interne, p.nom_complet, v.immatriculation 
                              FROM fiches_intervention f 
                              JOIN personnel p ON f.id_mec_1 = p.id_personnel 
                              JOIN vehicules v ON f.id_vehicule = v.id_vehicule 
                              ORDER BY f.date_entree DESC";
                    $res = $db->query($query);
                    while($row = $res->fetch()):
                        $status_class = ($row['statut'] == 'Terminé') ? 'bg-success' : 'bg-warning text-dark';
                    ?>
                    <tr>
                        <td><code class="fw-bold text-primary"><?= $row['code_interne'] ?></code></td>
                        <td><?= $row['nom_complet'] ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $row['immatriculation'] ?></span></td>
                        <td class="small"><?= $row['description_panne'] ?></td>
                        <td><?= date('d/m/Y', strtotime($row['date_entree'])) ?></td>
                        <td><span class="badge <?= $status_class ?>"><?= $row['statut'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
