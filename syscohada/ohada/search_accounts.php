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
    $num_compte = $_GET['num_compte'] . '%'; // Ajoute le caractère % pour la recherche par préfixe

    // Préparer la requête SQL pour récupérer tous les attributs
    $query = $db->prepare("SELECT * FROM comptes_ohada WHERE num_compte LIKE :num_compte");
    $query->execute(['num_compte' => $num_compte]);
    $comptes = $query->fetchAll(PDO::FETCH_ASSOC); // Récupérer tous les résultats

    // Retourner les résultats au format JSON
    header('Content-Type: application/json');
    echo json_encode($comptes);
} else {
    echo json_encode([]); // Retourne un tableau vide si aucun numéro n'est passé
}
?>