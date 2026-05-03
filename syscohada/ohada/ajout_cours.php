<?php
// Connexion à la base de données MYSQL
$host = '127.0.0.1';
$dbname = 'ohada';
$user = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification que le formulaire a été soumis
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupérer les données du formulaire
        $intitule = $_POST['intitule'];
        $cours = $_POST['cours'];

        // Vérifier si l'intitulé existe déjà
        $checkSql = "SELECT COUNT(*) FROM cours WHERE intitulé = :intitule";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':intitule', $intitule);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            echo "Erreur : Cet intitulé de cours existe déjà.";
        } else {
            // Préparer et exécuter la requête d'insertion
            $sql = "INSERT INTO cours (intitulé, cours) VALUES (:intitule, :cours)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':intitule', $intitule);
            $stmt->bindParam(':cours', $cours);

            if ($stmt->execute()) {
                echo "Cours ajouté avec succès !";
            } else {
                echo "Erreur lors de l'ajout du cours.";
            }
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>