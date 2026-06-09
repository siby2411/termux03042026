<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: index.php"); exit; }

require_once __DIR__ . '/../app/Models/Db.php';
require_once __DIR__ . '/../app/CsvParser.php';

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['csv_file'])){
    $parser = new \App\CsvParser($_FILES['csv_file']['tmp_name']);
    $pdo = \App\Models\Db::getInstance()->getConnection();
    foreach($parser->getData() as $row){
        $stmt = $pdo->prepare(
            "INSERT INTO ECRITURES_COMPTABLES (compte_debite_id, compte_credite_id, montant, date_operation) VALUES (?,?,?,?)"
        );
        $stmt->execute([$row['compte_debite'],$row['compte_credite'],$row['montant'],$row['date']]);
    }
    echo "<p>Import terminé !</p>";
}
?>
<form method="POST" enctype="multipart/form-data">
    Fichier CSV : <input type="file" name="csv_file" required>
    <button type="submit">Importer</button>
</form>
