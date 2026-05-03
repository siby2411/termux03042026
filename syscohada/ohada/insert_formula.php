<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données MySQL
$servername = "127.0.0.1";
$username = "root";
$password = "123";
$dbname = "ohada";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// S'assurer que l'encodage est UTF-8
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $libelle = $_POST['libelle'] ?? '';
    $formule = $_POST['formule'] ?? '';

    // Nettoyer les chaînes pour éviter les caractères non valides
    $formule = preg_replace('/[^\x20-\x7E]/', '', $formule);  // Retirer les caractères non imprimables

    // Afficher les données pour déboguer
    var_dump($libelle, $formule);

    // Valider que les champs ne sont pas vides
    if (empty($libelle) || empty($formule)) {
        die("Les champs 'libelle' et 'formule' sont obligatoires.");
    }

    // Préparer la requête d'insertion
    $stmt = $conn->prepare("INSERT INTO formule_comptabilite (libelle, formule) VALUES (?, ?)");

    // Vérifier si la requête est correctement préparée
    if (!$stmt) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    // Liaison des paramètres
    $stmt->bind_param("ss", $libelle, $formule);

    // Exécuter la requête
    if ($stmt->execute()) {
        echo "Formule ajoutée avec succès.";
    } else {
        echo "Erreur lors de l'ajout de la formule : " . $stmt->error;
    }

    // Fermer la requête préparée
    $stmt->close();
}

// Fermer la connexion
$conn->close();
?>