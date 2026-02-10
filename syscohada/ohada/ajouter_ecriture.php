<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'ohada';
$user = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les données du formulaire
    $date_operation = $_POST['date_operation'];
    $numero_compte = $_POST['numero_compte'];
    $description = $_POST['description'];
    $debit = $_POST['debit'] ?? 0;
    $credit = $_POST['credit'] ?? 0;
    
    // Insertion dans la table des écritures
    $sql = "INSERT INTO ecritures (date_operation, numero_compte, description, debit, credit) 
            VALUES (:date_operation, :numero_compte, :description, :debit, :credit)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':date_operation' => $date_operation,
        ':numero_compte' => $numero_compte,
        ':description' => $description,
        ':debit' => $debit,
        ':credit' => $credit
    ]);

    echo "Écriture comptable ajoutée avec succès !";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
