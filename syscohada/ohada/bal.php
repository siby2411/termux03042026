<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données MySQL
$servername = "localhost";
$username = "root";
$password = "123"; // Remplacez par votre mot de passe
$dbname = "ohada"; // Remplacez par le nom de votre base de données

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Fonction pour générer la balance comptable
function genererBalance($conn) {
    $sql = "SELECT numero_compte, description, SUM(montant) AS solde
            FROM balance
            GROUP BY numero_compte, description";
    
    $result = $conn->query($sql);

    echo "<h2>Balance Comptable</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Numéro de Compte</th><th>Description</th><th>Solde</th></tr>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['numero_compte']}</td><td>{$row['description']}</td><td>{$row['solde']}</td></tr>";
        }
    } else {
        echo "<tr><td colspan='3'>Aucune donnée disponible</td></tr>";
    }
    echo "</table>";
}

// Fonction pour générer le bilan
function genererBilan($conn) {
    $sql = "SELECT compte_debit, SUM(debit) AS total_debit, compte_credit, SUM(credit) AS total_credit
            FROM ecritures
            GROUP BY compte_debit, compte_credit";
    
    $result = $conn->query($sql);

    echo "<h2>Bilan</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Compte Débit</th><th>Total Débit</th><th>Compte Crédit</th><th>Total Crédit</th></tr>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['compte_debit']}</td><td>{$row['total_debit']}</td><td>{$row['compte_credit']}</td><td>{$row['total_credit']}</td></tr>";
        }
    } else {
        echo "<tr><td colspan='4'>Aucune donnée disponible</td></tr>";
    }
    echo "</table>";
}

// Appel des fonctions pour générer la balance et le bilan
genererBalance($conn);
genererBilan($conn);

// Fermer la connexion
$conn->close();
?>