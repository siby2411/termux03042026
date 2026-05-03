<?php
// Informations de connexion à la base de données
$host = '127.0.0.1'; // Nom d'hôte du serveur MySQL
$dbname = 'ohada'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur MySQL (root sans mot de passe)
$password = '123'; // Pas de mot de passe

// Chemin vers lequel le fichier CSV sera exporté (modifier selon vos besoins)
$csv_file = '/data/data/com.termux/files/home/storage/shared/comptes_ohada.csv';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête pour sélectionner les données de la table comptes_ohada
    $sql = "SELECT * FROM comptes_ohada";
    
    // Préparation de la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Ouverture du fichier CSV en écriture
    $file = fopen($csv_file, 'w');

    // Ajout de l'en-tête (colonnes) au fichier CSV
    $header = array('id', 'num_compte', 'intitule', 'sous_classe_id', 'description');
    fputcsv($file, $header);

    // Boucle pour récupérer et écrire chaque ligne dans le fichier CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($file, $row);
    }

    // Fermeture du fichier
    fclose($file);

    echo "Les comptes OHADA ont été exportés dans le fichier $csv_file";

} catch (PDOException $e) {
    // Gestion des erreurs
    echo "Erreur : " . $e->getMessage();
}
?>