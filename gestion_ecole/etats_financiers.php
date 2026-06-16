<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// --- Logique d'Export CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=etats_financiers.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Filière', 'Total Perçu (FCFA)']);
    
    $query = "SELECT f.nom, SUM(p.montant_verse) as total FROM paiements_scolarite p 
              JOIN etudiants e ON p.etudiant_id = e.id 
              JOIN classes c ON e.classe_id = c.id 
              JOIN filieres f ON c.filiere_id = f.id 
              GROUP BY f.nom";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['nom'], $row['total']]);
    }
    fclose($output);
    exit();
}

include 'header_ecole.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-graph-up"></i> États Financiers Mensuels</h3>
        <a href="?export=csv" class="btn btn-success"><i class="bi bi-file-earmark-spreadsheet"></i> Exporter CSV</a>
    </div>
    
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr><th>Filière</th><th>Total Scolarité Perçu</th></tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT f.nom, SUM(p.montant_verse) as total 
                    FROM paiements_scolarite p 
                    JOIN etudiants e ON p.etudiant_id = e.id 
                    JOIN classes c ON e.classe_id = c.id 
                    JOIN filieres f ON c.filiere_id = f.id 
                    GROUP BY f.nom";
            $res = $conn->query($sql);
            while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nom']) ?></td>
                <td><?= number_format($row['total'], 0, ' ', ' ') ?> FCFA</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer_ecole.php'; ?>
