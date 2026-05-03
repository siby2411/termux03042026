<?php
// Connexion à la base de données
$conn = new mysqli("127.0.0.1", "root", "", "ohada");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Tableau des tables à lister
$tables = [
    'journal_caisse' => 'Journal Caisse',
    'journal_banque' => 'Journal Banque',
    'journal_fournisseurs' => 'Journal Fournisseurs',
    'journal_achats' => 'Journal Achats',
    'journal_ventes' => 'Journal Ventes'
];

// Fonction pour lister les données d'une table
function listerTable($conn, $table_name, $table_label) {
    echo "<h2>$table_label</h2>";

    $sql = "SELECT * FROM $table_name";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Afficher les données dans un tableau HTML
        echo "<table border='1' cellpadding='10'>";
        echo "<tr>
                <th>ID</th>
                <th>Date Opération</th>
                <th>Description</th>
                <th>Montant</th>
                <th>Numéro de Compte</th>
                <th>Statut</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row['id'] . "</td>
                    <td>" . $row['date_operation'] . "</td>
                    <td>" . $row['description'] . "</td>
                    <td>" . $row['montant'] . "</td>
                    <td>" . $row['numero_compte'] . "</td>
                    <td>" . $row['statut'] . "</td>
                  </tr>";
        }
        echo "</table><br>";
    } else {
        echo "<p>Aucune donnée disponible dans $table_label.</p>";
    }
}

// Lister les données de chaque table
foreach ($tables as $table_name => $table_label) {
    listerTable($conn, $table_name, $table_label);
}

$conn->close();
?>