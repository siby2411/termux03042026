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
                    <td>" . number_format($row['montant'], 2) . "</td>
                    <td>" . $row['numero_compte'] . "</td>
                    <td>" . $row['statut'] . "</td>
                  </tr>";
        }
        echo "</table><br>";
    } else {
        echo "<p>Aucune donnée disponible dans $table_label.</p>";
    }
}

// Fonction pour calculer le solde de chaque compte
function calculerSoldeCompte($conn, $table_name, $table_label) {
    echo "<h2>Balance pour $table_label</h2>";

    $sql = "SELECT numero_compte, 
                   SUM(CASE WHEN statut = 'debit' THEN montant ELSE 0 END) AS total_debit,
                   SUM(CASE WHEN statut = 'credit' THEN montant ELSE 0 END) AS total_credit
            FROM $table_name
            GROUP BY numero_compte";
    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Afficher les données de balance dans un tableau HTML
        echo "<table border='1' cellpadding='10'>";
        echo "<tr>
                <th>Numéro de Compte</th>
                <th>Total Débits</th>
                <th>Total Crédits</th>
                <th>Solde</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            // Calculer le solde comme Crédits - Débits
            $solde = $row['total_credit'] - $row['total_debit'];
            echo "<tr>
                    <td>" . $row['numero_compte'] . "</td>
                    <td>" . number_format($row['total_debit'], 2) . "</td>
                    <td>" . number_format($row['total_credit'], 2) . "</td>
                    <td>" . number_format($solde, 2) . "</td>
                  </tr>";
        }
        echo "</table><br>";
    } else {
        echo "<p>Aucune donnée disponible pour la balance dans $table_label.</p>";
    }
}

// Lister les données de chaque table
foreach ($tables as $table_name => $table_label) {
    listerTable($conn, $table_name, $table_label);
}

// Calculer et afficher le solde pour chaque table
foreach ($tables as $table_name => $table_label) {
    calculerSoldeCompte($conn, $table_name, $table_label);
}

$conn->close();
?>