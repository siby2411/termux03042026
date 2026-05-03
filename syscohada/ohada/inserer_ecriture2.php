<?php


// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'ohada';
$username = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérification si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_operation = $_POST['date_operation'];
    $description = $_POST['description'];
    $debit = !empty($_POST['montant_debit']) ? (float)$_POST['montant_debit'] : 0.00;
    $credit = !empty($_POST['montant_credit']) ? (float)$_POST['montant_credit'] : 0.00;
    $compte_debit = $_POST['compte_debit'];
    $compte_credit = $_POST['compte_credit'];
    $intitule_debit = $_POST['intitule_debit'];
    $intitule_credit = $_POST['intitule_credit'];
    
    // Debugging: afficher les montants
    echo "Débit : " . $debit . "<br>";
    echo "Crédit : " . $credit . "<br>";

    // Préparation de la requête d'insertion
    $sql = "INSERT INTO ecritures (date_operation, description, debit, credit, compte_debit, intitule_debit, compte_credit, intitule_credit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    // Exécution de la requête
    try {
        $stmt->execute([$date_operation, $description, $debit, $credit, $compte_debit, $intitule_debit, $compte_credit, $intitule_credit]);
        echo "Insertion réussie.";
    } catch (PDOException $e) {
        echo "Erreur lors de l'insertion : " . $e->getMessage();
    }
}