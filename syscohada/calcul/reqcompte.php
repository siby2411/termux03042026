<?php   
include 'config.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si la requête est de type POST et si le numéro de compte est présent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['numero_compte'])) {
    $numero_compte = $_POST['numero_compte'];

    // Validation du numéro de compte
    if (!empty($numero_compte) && is_numeric($numero_compte)) {
        // Préparer la requête pour sélectionner les informations du compte
        $stmt = $pdo->prepare("SELECT c.numero_compte, c.nom, cl.nom AS classe_nom 
                                FROM comptes c 
                                JOIN classe cl ON c.id_classe = cl.id 
                                WHERE c.numero_compte = ?");
        $stmt->execute([$numero_compte]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Vérifier si des résultats ont été trouvés
        if ($result) {
            echo "<h2>Résultats de la recherche</h2>";
            echo "<table border='1'>
                    <tr>
                        <th>Numéro de Compte</th>
                        <th>Nom</th>
                        <th>Classe</th>
                    </tr>";

            // Afficher les résultats dans le tableau
            foreach ($result as $row) {
                echo "<tr>
                        <td>{$row['numero_compte']}</td>
                        <td>{$row['nom']}</td>
                        <td>{$row['classe_nom']}</td>
                    </tr>";
            }

            echo "</table>";
        } else {
            echo "<h2>Aucun compte trouvé avec le numéro de compte : $numero_compte</h2>";
        }
    } else {
        echo "<h2>Numéro de compte invalide.</h2>";
    }
} else {
    echo "<h2>Veuillez soumettre un numéro de compte.</h2>";
}
?>