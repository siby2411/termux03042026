<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// 1. Statistiques
$stats = $conn->query("SELECT COUNT(*) as total, 
                       SUM(CASE WHEN moyenne_annuelle >= 10 THEN 1 ELSE 0 END) as admis,
                       AVG(moyenne_annuelle) as moy_gen FROM bulletins")->fetch_assoc();

// 2. Filtre de recherche
$search = $_GET['search'] ?? '';
$sql = "SELECT e.code_etudiant, e.nom, e.prenom, b.moyenne_annuelle, 
               CASE WHEN b.moyenne_annuelle >= 10 THEN 'Admis' ELSE 'Ajourné' END as mention
        FROM etudiants e
        LEFT JOIN bulletins b ON e.code_etudiant = b.code_etudiant
        WHERE e.nom LIKE '%$search%' OR e.code_etudiant LIKE '%$search%'
        ORDER BY b.moyenne_annuelle DESC";
$result = $conn->query($sql);

include 'header_ecole.php';
?>

<div class="container mt-4">
    <h2 class="text-primary mb-4">Tableau de Bord</h2>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card bg-primary text-white p-3">Total : <?= $stats['total'] ?> étudiants</div></div>
        <div class="col-md-4"><div class="card bg-success text-white p-3">Admis : <?= $stats['admis'] ?></div></div>
        <div class="col-md-4"><div class="card bg-info text-white p-3">Moyenne Générale : <?= number_format($stats['moy_gen'], 2) ?></div></div>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-secondary">Filtrer</button>
        </form>
        <a href="export_csv.php" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Exporter CSV</a>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr><th>Code</th><th>Nom</th><th>Moyenne</th><th>Mention</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['code_etudiant'] ?></td>
                <td><?= $row['nom'] . ' ' . $row['prenom'] ?></td>
                <td><?= number_format($row['moyenne_annuelle'] ?? 0, 2) ?></td>
                <td><?= $row['mention'] ?></td>
                <td><a href="bulletin.php?code_etudiant=<?= $row['code_etudiant'] ?>" class="btn btn-sm btn-primary">Voir</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer_ecole.php'; ?>
