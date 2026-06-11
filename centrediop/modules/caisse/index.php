<?php
session_start();
require_once '../../includes/db.php';
// On appelle la sidebar en dernier, pour qu'elle s'ajuste au contenu
require_once '../../includes/sidebar.php';

// On injecte le CSS via PHP en s'assurant qu'il est chargé après le reste
echo '<link rel="stylesheet" href="/assets/css/style.css">';
?>

<div class="main-content" style="margin-left: 250px; padding: 20px;">
    <div class="container-fluid">
        <h2 style="color: #2c3e50; margin-bottom: 20px;">Espace Caisse</h2>
        
        <div class="dashboard-card">
            <h4>File d'attente (Paiements)</h4>
            <table class="data-table" style="width: 100%; margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Service</th>
                        <th>Montant</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT c.id, p.nom, p.prenom, c.service_id, c.prix 
                                        FROM consultations c 
                                        JOIN patients p ON c.patient_id = p.id 
                                        WHERE c.statut = 'attente_paiement'");
                    
                    if ($res && $res->num_rows > 0) {
                        while($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nom'].' '.$row['prenom']) ?></td>
                                <td><?= htmlspecialchars($row['service_id']) ?></td>
                                <td><?= number_format($row['prix'], 0, ',', ' ') ?> FCFA</td>
                                <td>
                                    <a href="encaisser.php?id=<?= $row['id'] ?>" class="btn btn-success">Encaisser</a>
                                </td>
                            </tr>
                        <?php endwhile;
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center;'>Aucun paiement en attente.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
