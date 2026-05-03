<?php
// Connexion à la base de données
$conn = new mysqli('127.0.0.1', 'root', '', 'comptabilite');

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Requête pour récupérer le livre journal
$sql = "SELECT oc.date_operation, oc.description, cd.libelle_compte AS compte_debit, 
        cc.libelle_compte AS compte_credit, oc.montant_debit, oc.montant_credit
        FROM operations_comptables oc
        JOIN comptes cd ON oc.compte_debit = cd.numero_compte
        JOIN comptes cc ON oc.compte_credit = cc.numero_compte
        ORDER BY oc.date_operation";

$result = $conn->query($sql);

// Affichage des résultats
if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Compte Débit</th>
                <th>Compte Crédit</th>
                <th>Montant Débit</th>
                <th>Montant Crédit</th>
            </tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["date_operation"] . "</td>
                <td>" . $row["description"] . "</td>
                <td>" . $row["compte_debit"] . "</td>
                <td>" . $row["compte_credit"] . "</td>
                <td>" . $row["montant_debit"] . "</td>
                <td>" . $row["montant_credit"] . "</td>
            </tr>";
    }
    echo "</table>";
} else {
    echo "Aucune opération trouvée.";
}

$conn->close();
?>
