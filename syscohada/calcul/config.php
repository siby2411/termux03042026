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
    $sql = "SELECT id, nom FROM classes";
    $stmt = $pdo->query($sql);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les classes au format JSON
    echo json_encode($classes);
} catch (PDOException $e) {
    die("Erreur dans la requête SQL : " . $e->getMessage());
}

?>