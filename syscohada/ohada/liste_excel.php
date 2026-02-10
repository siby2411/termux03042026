


<?php include('config.php'); ?>



<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$excelFile = '/data/data/com.termux/files/home/storage/shared/comptes_ohada.xlsx';

// Vérifier si le fichier existe
if (!file_exists($excelFile)) {
    die("Le fichier Excel n'existe pas : " . $excelFile);
}

try {
    // Charger le fichier Excel
    $spreadsheet = IOFactory::load($excelFile);

    // Sélectionner la première feuille
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true); // Récupérer les données sous forme de tableau associatif

} catch (Exception $e) {
    echo "Erreur lors de la lecture du fichier Excel : " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Comptes OHADA - Fichier Excel</title>
    <!-- Intégration de Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Liste des Comptes OHADA (Lecture du fichier Excel)</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <?php
                    // Afficher les en-têtes du fichier Excel
                    if (!empty($data)) {
                        foreach ($data[1] as $columnHeader) {
                            echo "<th>" . htmlspecialchars($columnHeader !== null ? $columnHeader : 'N/A') . "</th>";
                        }
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Afficher les données du fichier Excel
                for ($i = 2; $i <= count($data); $i++) { // Démarrer à la ligne 2 pour ignorer les en-têtes
                    echo "<tr>";
                    foreach ($data[$i] as $cell) {
                        echo "<td>" . htmlspecialchars($cell !== null ? $cell : 'N/A') . "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Intégration de Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>