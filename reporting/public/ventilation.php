<?php
require_once __DIR__ . '/../config/database.php'; // connexion $conn
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ventilation SYSCOHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include __DIR__ . '/../views/sidebar.php'; ?>
<?php include __DIR__ . '/../views/topbar.php'; ?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Ventilation SYSCOHADA</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <span class="m-0 fw-bold text-primary">Liste des Items de Ventilation</span>
            <div>
                <a href="export_excel.php?table=VENTILATION_ITEMS" class="btn btn-success btn-sm">Exporter Excel</a>
                <a href="export_pdf.php?table=VENTILATION_ITEMS" class="btn btn-danger btn-sm">Exporter PDF</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Label</th>
                            <th>Type</th>
                            <th>Criteria</th>
                            <th>Ordre d'affichage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->query("SELECT * FROM VENTILATION_ITEMS ORDER BY display_order ASC, item_id ASC");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>".$row['item_id']."</td>";
                            echo "<td>".$row['item_code']."</td>";
                            echo "<td>".$row['item_label']."</td>";
                            echo "<td>".$row['item_type']."</td>";
                            echo "<td>".htmlspecialchars($row['criteria'])."</td>";
                            echo "<td>".$row['display_order']."</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

