<?php
// Connexion à la base de données
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=ohada', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Vérifier si un numéro de compte est passé en paramètre GET
if (isset($_GET['num_compte']) && !empty($_GET['num_compte'])) {
    $num_compte = $_GET['num_compte'];

    // Afficher le numéro de compte pour débogage
    echo "<p>Numéro de compte recherché : " . htmlspecialchars($num_compte) . "</p>";

    // Rechercher les détails du compte dans la table comptes_ohada
    $query = $db->prepare("SELECT * FROM comptes_ohada WHERE num_compte = :num_compte");
    
    // Exécuter la requête
    try {
        $query->execute(['num_compte' => $num_compte]);
        $compte = $query->fetch(PDO::FETCH_ASSOC);

        if ($compte) {
            // Afficher les détails du compte
            echo "<h3>Détails du compte OHADA</h3>";
            echo "<p><strong>Numéro de compte :</strong> " . htmlspecialchars($compte['num_compte']) . "</p>";
            echo "<p><strong>Intitulé :</strong> " . htmlspecialchars($compte['intitule']) . "</p>";
            echo "<p><strong>Sous-classe ID :</strong> " . htmlspecialchars($compte['sous_classe_id']) . "</p>";
            echo "<p><strong>Description :</strong> " . htmlspecialchars($compte['description']) . "</p>";
        } else {
            echo "Aucun compte trouvé avec ce numéro.";
        }
    } catch (PDOException $e) {
        echo "Erreur lors de la requête : " . $e->getMessage();
    }
} else {
    echo "Veuillez sélectionner un numéro de compte.";
}
?>