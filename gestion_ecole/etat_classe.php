<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'header_ecole.php'; 
require_once 'db_connect_ecole.php'; 
$conn = db_connect_ecole();

$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 1;

// 1. Vérification de la classe avec le nom de colonne correct (nom_class)
$q_classe = $conn->query("SELECT nom_class FROM classes WHERE id = $classe_id");
if (!$q_classe || $q_classe->num_rows == 0) {
    echo "<div class='container mt-5 alert alert-danger'>Erreur : Classe non trouvée (ID: $classe_id).</div>";
    include 'footer_ecole.php';
    exit();
}
$classe = $q_classe->fetch_assoc();

// 2. Requête de synthèse
$sql = "SELECT e.id, e.nom, e.prenom, e.code_etudiant, c.montant_scolarite,
        (SELECT IFNULL(SUM(montant_verse), 0) FROM paiements_scolarite WHERE etudiant_id = e.id) as total_scol,
        (SELECT IFNULL(SUM(montant_verse), 0) FROM paiements_inscription WHERE code_etudiant = e.code_etudiant) as total_inscr
        FROM etudiants e
        JOIN classes c ON e.classe_id = c.id
        WHERE e.classe_id = $classe_id";

$res = $conn->query($sql);
?>

<div class="container mt-4">
    <h4 class="mb-4">État financier : Classe de <?= htmlspecialchars($classe['nom_class']) ?></h4>
    <div class="card shadow p-4">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Étudiant</th>
                    <th>Dû (Scol.)</th>
                    <th>Versé (Total)</th>
                    <th>Reste à payer</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($res && $res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) { 
                        $verse = $row['total_scol'] + $row['total_inscr'];
                        $reste = $row['montant_scolarite'] - $verse;
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['code_etudiant']) ?> - <?= htmlspecialchars($row['nom'] . ' ' . $row['prenom']) ?></td>
                    <td><?= number_format($row['montant_scolarite'], 0, ' ', ' ') ?></td>
                    <td><?= number_format($verse, 0, ' ', ' ') ?></td>
                    <td><strong class="<?= $reste > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= number_format($reste, 0, ' ', ' ') ?>
                    </strong></td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='4' class='text-center'>Aucun étudiant trouvé dans cette classe.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
