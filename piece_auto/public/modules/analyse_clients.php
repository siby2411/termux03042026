<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$page_title = "Statistiques Clients";
include '../../includes/header.php';

// Requête pour voir les clients qui dépensent le plus
$query = "SELECT c.id_client, c.nom, c.prenom, COUNT(cv.id_commande_vente) as nb_achats, SUM(cv.total_commande) as total_depense
          FROM CLIENTS c
          LEFT JOIN COMMANDE_VENTE cv ON c.id_client = cv.id_client
          GROUP BY c.id_client
          ORDER BY total_depense DESC";
$stmt = $db->query($query);
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-info text-white shadow-sm border-0">
            <div class="card-body">
                <h6>Client le plus fidèle</h6>
                <h3 id="topClient">Chargement...</h3>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-bold">Classement par Chiffre d'Affaires</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Client</th>
                    <th class="text-center">Nombre de Commandes</th>
                    <th class="text-end">Total CA Généré</th>
                    <th class="text-end">Moyenne / Commande</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                    $moyenne = ($row['nb_achats'] > 0) ? $row['total_depense'] / $row['nb_achats'] : 0;
                ?>
                <tr>
                    <td><b><?= strtoupper($row['nom']) ?></b> <?= $row['prenom'] ?></td>
                    <td class="text-center"><span class="badge bg-secondary"><?= $row['nb_achats'] ?></span></td>
                    <td class="text-end fw-bold"><?= number_format($row['total_depense'], 0, ',', ' ') ?> F</td>
                    <td class="text-end text-muted"><?= number_format($moyenne, 0, ',', ' ') ?> F</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
