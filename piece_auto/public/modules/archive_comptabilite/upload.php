<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}
require_once __DIR__ . '/../includes/db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])){
    $tmpName = $_FILES['csv_file']['tmp_name'];
    if(($file = fopen($tmpName,'r'))!==FALSE){
        while(($line = fgetcsv($file,1000,","))!==FALSE){
            $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (compte_debite_id, compte_credite_id, montant) VALUES (?,?,?)");
            $stmt->execute([$line[0], $line[1], $line[2]]);
        }
        fclose($file);
        echo "<p>Import terminé !</p>";
    } else {
        echo "<p>Impossible de lire le fichier.</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    Fichier CSV : <input type="file" name="csv_file" required>
    <button type="submit">Importer</button>
</form>
