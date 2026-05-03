<?php
// Chemins relatifs
require_once __DIR__ . '/../config/database.php'; // connexion PDO $conn
include __DIR__ . '/../views/sidebar.php';
include __DIR__ . '/../views/topbar.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Liste des tables autorisées pour export
$allowedTables = [
    'VENTILATION_ITEMS' => 'Ventilation SYSCOHADA',
    'ECRITURES_COMPTABLES' => 'Écritures Comptables',
    'PLAN_COMPTABLE_UEMOA' => 'Plan Comptable UEMOA'
];

// Gestion de la table sélectionnée
$table = $_GET['table'] ?? '';
if($table && !array_key_exists($table, $allowedTables)){
    die("Table non autorisée pour l'export.");
}

// Si formulaire soumis pour export
if(isset($_POST['export_table'])){
    $tableToExport = $_POST['table'] ?? '';
    if(!array_key_exists($tableToExport, $allowedTables)){
        die("Table non autorisée pour l'export.");
    }

    // Récupérer les données
    $stmt = $conn->query("SELECT * FROM `$tableToExport`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Entêtes
    if(!empty($rows)){
        $cols = array_keys($rows[0]);
        foreach($cols as $colIndex => $colName){
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cell, $colName);
        }

        // Données
        foreach($rows as $rowIndex => $row){
            foreach($cols as $colIndex => $colName){
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 2);
                $sheet->setCellValue($cell, $row[$colName]);
            }
        }
    }

    $filename = $tableToExport.'_'.date('Ymd_His').'.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

?>

<div class="container-fluid mt-4">
    <h1 class="h3 mb-4 text-gray-800">Export Excel</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Sélectionnez la table à exporter</h6>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3 align-items-center">
                <div class="col-auto">
                    <select name="table" class="form-select" required>
                        <option value="">-- Choisir une table --</option>
                        <?php foreach($allowedTables as $key => $label): ?>
                            <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" name="export_table" class="btn btn-success">Exporter Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>

