<?php
include_once 'config/db.php';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $query = "INSERT INTO Ecritures (DateComptable, Montant, CompteDebiteur, CompteCrediteur, Libelle) 
                  VALUES (:date, :montant, :deb, :cred, :lib)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':date' => $_POST['DateComptable'],
            ':montant' => $_POST['Montant'],
            ':deb' => $_POST['CompteDebiteur'],
            ':cred' => $_POST['CompteCrediteur'],
            ':lib' => $_POST['Libelle']
        ]);
        header("Location: list_grandlivre.php?success=1");
    } catch(PDOException $e) {
        die("Erreur comptable : " . $e->getMessage());
    }
}
?>
