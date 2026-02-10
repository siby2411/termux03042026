<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "123", "ohada");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupérer l'exercice comptable actuel
$sql_exercice = "SELECT id, annee FROM exercice WHERE statut = 'ouvert' LIMIT 1";
$result_exercice = $conn->query($sql_exercice);
$exercice = $result_exercice->fetch_assoc();

if ($exercice) {
    $id_exercice = $exercice['id'];
    $annee = $exercice['annee'];
    
    echo "<h1>Bilan d'Ouverture pour l'Exercice $annee</h1>";

    // Récupérer les actifs
    echo "<h2>Actifs</h2>";
    $sql_actifs = "SELECT libelle, montant, sous_type FROM bilan_ouverture WHERE type_element = 'actif' AND id_exercice = $id_exercice";
    $result_actifs = $conn->query($sql_actifs);
    
    if ($result_actifs->num_rows > 0) {
        echo "<table border='1'><tr><th>Libellé</th><th>Montant</th><th>Sous-Type</th></tr>";
        while ($row = $result_actifs->fetch_assoc()) {
            echo "<tr><td>{$row['libelle']}</td><td>{$row['montant']}</td><td>{$row['sous_type']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Aucun actif trouvé pour cet exercice.";
    }

    // Récupérer les passifs
    echo "<h2>Passifs</h2>";
    $sql_passifs = "SELECT libelle, montant, sous_type FROM bilan_ouverture WHERE type_element = 'passif' AND id_exercice = $id_exercice";
    $result_passifs = $conn->query($sql_passifs);
    
    if ($result_passifs->num_rows > 0) {
        echo "<table border='1'><tr><th>Libellé</th><th>Montant</th><th>Sous-Type</th></tr>";
        while ($row = $result_passifs->fetch_assoc()) {
            echo "<tr><td>{$row['libelle']}</td><td>{$row['montant']}</td><td>{$row['sous_type']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Aucun passif trouvé pour cet exercice.";
    }

    // Récupérer les opérations diverses
    echo "<h2>Opérations Diverses</h2>";
    $sql_operations = "SELECT description, montant, numero_compte, statut FROM operation_diverse WHERE date_operation BETWEEN '2024-01-01' AND '2024-12-31'";
    $result_operations = $conn->query($sql_operations);
    
    if ($result_operations->num_rows > 0) {
        echo "<table border='1'><tr><th>Description</th><th>Montant</th><th>Numéro de Compte</th><th>Statut</th></tr>";
        while ($row = $result_operations->fetch_assoc()) {
            echo "<tr><td>{$row['description']}</td><td>{$row['montant']}</td><td>{$row['numero_compte']}</td><td>{$row['statut']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Aucune opération diverse trouvée.";
    }

} else {
    echo "Aucun exercice ouvert trouvé.";
}

// Fermer la connexion
$conn->close();
?>