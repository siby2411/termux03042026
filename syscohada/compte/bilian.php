<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'comptabilite');

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Requête pour récupérer les comptes regroupés par actif et passif
$sql = "SELECT 
            c.libelle_compte, 
            c.type_compte, 
            (SUM(oc.montant_debit) - SUM(oc.montant_credit)) AS solde
        FROM operations_comptables oc
        JOIN comptes c 
            ON oc.compte_debit = c.numero_compte 
            OR oc.compte_credit = c.numero_compte
        GROUP BY c.libelle_compte, c.type_compte
        ORDER BY c.type_compte, c.libelle_compte";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan Comptable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Bilan Comptable</h2>
        <div class="row">
            <div class="col-md-6">
                <h3>Actifs</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Compte</th>
                            <th>Solde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php if($row['type_compte'] == 'actif'): ?>
                                <tr>
                                    <td><?php echo $row['libelle_compte']; ?></td>
                                    <td><?php echo number_format($row['solde'], 2); ?> €</td>
                                </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h3>Passifs</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Compte</th>
                            <th>Solde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php if($row['type_compte'] == 'passif'): ?>
                                <tr>
                                    <td><?php echo $row['libelle_compte']; ?></td>
                                    <td><?php echo number_format($row['solde'], 2); ?> €</td>
                                </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
