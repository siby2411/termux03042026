<!DOCTYPE html>
<html>
<head>
    <title>OMEGA INFORMATIQUE - Gestion Comptabilité</title>
    <style>
        /* Style du bandeau en-tête */
        .header {
            background-color: #4CAF50; /* Couleur verte */
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 24px;
        }

        /* Style des tableaux */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="header">
    OMEGA INFORMATIQUE - Gestion Comptabilité
</div>

<?php
// Connexion à la base de données
$conn = new mysqli("127.0.0.1", "root", "", "ohada");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Tableau des tables à lister, incluant journal_clients
$tables = [
    'journal_caisse' => 'Journal Caisse',
    'journal_banque' => 'Journal Banque',
    'journal_fournisseurs' => 'Journal Fournisseurs',
    'journal_achats' => 'Journal Achats',
    'journal_ventes' => 'Journal Ventes',
    'journal_clients' => 'Journal Clients'
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
function calculerSoldeCompte($conn, $table_name) {
    $solde_total = [];
    
    $sql = "SELECT numero_compte, 
                   SUM(CASE WHEN statut = 'debit' THEN montant ELSE 0 END) AS total_debit,
                   SUM(CASE WHEN statut = 'credit' THEN montant ELSE 0 END) AS total_credit
            FROM $table_name
            GROUP BY numero_compte";
    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Calculer le solde comme Crédits - Débits
            $solde_total[$row['numero_compte']] = [
                'total_debit' => $row['total_debit'],
                'total_credit' => $row['total_credit'],
                'solde' => $row['total_credit'] - $row['total_debit'] // Solde = Crédits - Débits
            ];
        }
    }
    
    return $solde_total;
}

// Fonction pour afficher la balance consolidée
function afficherBalanceConsolidee($conn, $tables) {
    $solde_global = [];

    // Calculer le solde de chaque table
    foreach ($tables as $table_name => $table_label) {
        $solde_compte = calculerSoldeCompte($conn, $table_name);
        
        foreach ($solde_compte as $numero_compte => $montants) {
            if (!isset($solde_global[$numero_compte])) {
                $solde_global[$numero_compte] = [
                    'cumul_debit' => 0,
                    'cumul_credit' => 0,
                    'solde' => 0
                ];
            }
            $solde_global[$numero_compte]['cumul_debit'] += $montants['total_debit'];
            $solde_global[$numero_compte]['cumul_credit'] += $montants['total_credit'];
            $solde_global[$numero_compte]['solde'] = $solde_global[$numero_compte]['cumul_credit'] - $solde_global[$numero_compte]['cumul_debit'];
        }
    }

    // Afficher le tableau des soldes consolidés
    echo "<h2>Balance Consolidée</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>
            <th>Numéro de Compte</th>
            <th>Cumul Débits</th>
            <th>Cumul Crédits</th>
            <th>Solde</th>
          </tr>";

    foreach ($solde_global as $numero_compte => $montants) {
        echo "<tr>
                <td>" . $numero_compte . "</td>
                <td>" . number_format($montants['cumul_debit'], 2) . "</td>
                <td>" . number_format($montants['cumul_credit'], 2) . "</td>
                <td>" . number_format($montants['solde'], 2) . "</td>
              </tr>";
    }
    echo "</table><br>";
}

// Lister les données de chaque table
foreach ($tables as $table_name => $table_label) {
    listerTable($conn, $table_name, $table_label);
}

// Afficher la balance consolidée
afficherBalanceConsolidee($conn, $tables);

$conn->close();
?>

</body>
</html>