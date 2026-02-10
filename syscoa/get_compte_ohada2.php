
<?php
/**
 * Fichier : get_compte_ohada.php
 * Rôle : Fournit dynamiquement les numéros et libellés de comptes OHADA
 * pour l'autocomplétion dans le formulaire de saisie comptable.
 */

// 1. Inclusion de la configuration
require 'config.php';

// Définir l'en-tête pour une réponse JSON
header('Content-Type: application/json');

// Vérifier si le terme de recherche est fourni
if (!isset($_GET['term']) || empty($_GET['term'])) {
    echo json_encode([]);
    exit;
}

$searchTerm = trim($_GET['term']);
$results = [];

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Préparation de la requête pour chercher dans la table 'comptes'
    // La recherche se fait sur le début du numéro de compte OU sur une partie du nom du compte.
    $sql = "SELECT numero_compte, nom 
            FROM comptes 
            WHERE numero_compte LIKE :term_num 
               OR nom LIKE :term_name 
            ORDER BY numero_compte ASC 
            LIMIT 10"; // Limiter à 10 résultats pour l'efficacité

    $stmt = $pdo->prepare($sql);
    
    // Ajout des jokers % pour la recherche LIKE
    $term_num = $searchTerm . '%';
    $term_name = '%' . $searchTerm . '%';

    $stmt->bindParam(':term_num', $term_num);
    $stmt->bindParam(':term_name', $term_name);
    
    $stmt->execute();
    
    // Formatage des résultats pour le JavaScript (objet {id, text})
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'id' => $row['numero_compte'], // Le numéro de compte pour la valeur
            'text' => $row['numero_compte'] . ' - ' . $row['nom'] // Le texte affiché
        ];
    }

    echo json_encode($results);

} catch (PDOException $e) {
    // En cas d'erreur de base de données, retourner un tableau vide et logguer l'erreur.
    error_log("DB Error: " . $e->getMessage());
    echo json_encode(['error' => 'Erreur de base de données.']);
}

?>




