<?php
include 'config.php'; // Connexion à la base de données

if (isset($_GET['id_classe'])) {
    $id_classe = intval($_GET['id_classe']);

    // Requête SQL pour récupérer les comptes liés à une classe
    $sql = "SELECT numero_compte, nom FROM comptes WHERE classe_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_classe]);

    $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($comptes) {
        echo json_encode($comptes); // Retourner les comptes sous forme JSON
    } else {
        echo json_encode(['error' => 'Aucun compte trouvé pour cette classe']);
    }
} else {
    echo json_encode(['error' => 'ID de classe non fourni']);
}
?>