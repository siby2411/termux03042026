<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '123', 'ohada');

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

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Livre Journal</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Compte Débit</th>
                        <th>Compte Crédit</th>
                        <th>Montant Débit</th>
                        <th>Montant Crédit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['date_operation']; ?></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['compte_debit']; ?></td>
                            <td><?php echo $row['compte_credit']; ?></td>
                            <td><?php echo number_format($row['montant_debit'], 2); ?></td>
                            <td><?php echo number_format($row['montant_credit'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucune opération trouvée.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
