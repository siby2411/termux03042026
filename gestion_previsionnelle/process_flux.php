<?php
include_once 'config/db.php';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $query = "INSERT INTO FluxTresorerie (DateFlux, TypeFlux, Montant, CodeCompte, Libelle) 
                  VALUES (:date, :type, :montant, :compte, :lib)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':date' => $_POST['DateComptable'],
            ':type' => $_POST['TypeFlux'],
            ':montant' => $_POST['Montant'],
            ':compte' => $_POST['CodeCompte'],
            ':lib' => $_POST['Libelle']
        ]);
        
        // Optionnel : On peut aussi créer une écriture auto dans le Grand Livre ici
        
        header("Location: dashboard_pilote.php?msg=flux_enregistre");
    } catch(PDOException $e) {
        die("Erreur lors de l'enregistrement du flux : " . $e->getMessage());
    }
}
?>
