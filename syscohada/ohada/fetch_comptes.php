<?php
include('config.php');

// Vérifiez si l'ID de la sous-classe est passé en paramètre
if (isset($_GET['sous_classe_id'])) {
    $sous_classe_id = $_GET['sous_classe_id'];

    // Préparez et exécutez la requête pour récupérer les numéros de compte
    $stmt = $pdo->prepare("SELECT * FROM comptes_ohada WHERE sous_classe_id = :sous_classe_id");
    $stmt->execute(['sous_classe_id' => $sous_classe_id]);

    // Récupérez les résultats
    $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifiez si des comptes ont été trouvés
    if ($comptes) {
        foreach ($comptes as $compte) {
            echo "<option value='" . htmlspecialchars($compte['num_compte'], ENT_QUOTES) . "'>" . htmlspecialchars($compte['num_compte'], ENT_QUOTES) . "</option>";
        }
    } else {
        echo "<option value=''>Aucun numéro de compte trouvé</option>";
    }
} else {
    echo "<option value=''>Sous-classe non spécifiée</option>";
}
?>