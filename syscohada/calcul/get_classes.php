<?php
$host = 'localhost';
$db = 'compta';
$user = 'root';
$pass = '123';  // Pas de mot de passe

// Connexion à la base de données via PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Requête pour récupérer les classes
try {
    // Utilisez le bon nom de table ici
    $sql = "SELECT id, nom FROM classe";
    $stmt = $pdo->query($sql);

    if ($stmt->rowCount() > 0) {
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($classes);
    } else {
        echo json_encode(['error' => 'Aucune classe trouvée']);
    }
} catch (PDOException $e) {
    die("Erreur dans la requête SQL : " . $e->getMessage());
}
?>