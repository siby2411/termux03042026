<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$page_title = "États Financiers - OMEGA";
include 'header_ecole.php';

$search = $_GET['search'] ?? '';

$sql = "SELECT e.code_etudiant, e.nom, e.prenom, c.nom_class,
        t.montant_scolarite, t.droit_inscription,
        (SELECT SUM(montant_paye) FROM paiements WHERE code_etudiant = e.code_etudiant AND type_paiement = 'Scolarite') as paye_scol,
        (SELECT SUM(montant_paye) FROM paiements WHERE code_etudiant = e.code_etudiant AND type_paiement = 'Inscription') as paye_ins
        FROM etudiants e
        JOIN classes c ON e.classe_id = c.id
        JOIN tarifs t ON t.classe_id = c.id";

if ($search) { $sql .= " WHERE e.code_etudiant LIKE '%$search%' OR e.nom LIKE '%$search%'"; }

$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="card omega-card border-0 shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Suivi des Recettes Scolaires</h5>
            <form class="d-flex w-50">
                <input type="text" name="search" class="form-control me-2" placeholder="Rechercher étudiant..." value="<?= $search ?>">
                <button type="submit" class="btn btn-light text-primary">Rechercher</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Étudiant</th>
                        <th>Droit Inscription</th>
                        <th>Scolarité</th>
                        <th>Solde Total Restant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): 
                        $du_ins = $row['droit_inscription'] - $row['paye_ins'];
                        $du_scol = $row['montant_scolarite'] - $row['paye_scol'];
                        $total_du = $du_ins + $du_scol;
                    ?>
                    <tr>
                        <td><strong><?= $row['nom'] ?></strong> (<?= $row['nom_class'] ?>)</td>
                        <td>
                            <span class="badge <?= ($du_ins <= 0) ? 'bg-success' : 'bg-danger' ?>">
                                <?= ($du_ins <= 0) ? 'Payé' : 'Reste : '.number_format($du_ins,0).' FCFA' ?>
                            </span>
                        </td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <?php $pct = ($row['paye_scol'] / $row['montant_scolarite']) * 100; ?>
                                <div class="progress-bar bg-success" style="width: <?= $pct ?>%"><?= round($pct) ?>%</div>
                            </div>
                        </td>
                        <td class="fw-bold text-<?= ($total_du > 0) ? 'danger' : 'success' ?>">
                            <?= number_format($total_du, 0) ?> FCFA
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
