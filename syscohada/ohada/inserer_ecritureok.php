<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$host = 'localhost';
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
    // Récupérer les données
    $date_operation = $_POST['date_operation'];
    $description = !empty($_POST['description']) ? trim($_POST['description']) : null;
    $montant = !empty($_POST['montant']) ? (float)$_POST['montant'] : 0.00;
    $compte_debit = trim($_POST['compte_debit']);
    $compte_credit = trim($_POST['compte_credit']);

    // Validation des champs obligatoires
    if (empty($compte_debit) || empty($compte_credit)) {
        echo "Les champs 'Compte Débit' et 'Compte Crédit' sont obligatoires.";
        exit;
    }

    if ($montant <= 0) {
        echo "Le montant doit être supérieur à 0.";
        exit;
    }

    try {
        // Récupérer l'intitulé du compte débit
        $sql_debit = "SELECT intitule FROM comptes_ohada WHERE num_compte = ?";
        $stmt_debit = $pdo->prepare($sql_debit);
        $stmt_debit->execute([$compte_debit]);
        $result_debit = $stmt_debit->fetch(PDO::FETCH_ASSOC);

        if (!$result_debit) {
            echo "Le compte débit n'existe pas.";
            exit;
        }
        $intitule_debit = $result_debit['intitule'];

        // Récupérer l'intitulé du compte crédit
        $sql_credit = "SELECT intitule FROM comptes_ohada WHERE num_compte = ?";
        $stmt_credit = $pdo->prepare($sql_credit);
        $stmt_credit->execute([$compte_credit]);
        $result_credit = $stmt_credit->fetch(PDO::FETCH_ASSOC);

        if (!$result_credit) {
            echo "Le compte crédit n'existe pas.";
            exit;
        }
        $intitule_credit = $result_credit['intitule'];

        // Préparation de la requête d'insertion
        $sql = "INSERT INTO ecritures (date_operation, description, debit, credit, compte_debit, intitule_debit, compte_credit, intitule_credit) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        // Exécution de la requête
        $stmt->execute([$date_operation, $description, $montant, $montant, $compte_debit, $intitule_debit, $compte_credit, $intitule_credit]);

        // Redirection vers la page liste_ecriture.php après l'insertion réussie
        header("Location: liste_ecriture.php");
        exit;
    } catch (PDOException $e) {
        echo "Erreur lors de l'insertion : " . $e->getMessage();
    }
}
?>