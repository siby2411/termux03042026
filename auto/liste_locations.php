<?php
include 'db_connect.php';
$sql = "SELECT l.*, c.nom, c.prenom, v.marque, v.modele 
        FROM locations l 
        JOIN clients c ON l.client_id = c.id 
        JOIN voitures v ON l.voiture_id = v.id 
        ORDER BY l.date_location DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omega Auto | Liste des Locations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; padding: 40px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 12px; background: #f1f5f9; color: #475569; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; text-decoration: none; }
        .bg-success { background: #dcfce7; color: #166534; }
        .bg-danger { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h1><i class="fas fa-list"></i> Suivi des Locations</h1>
            <a href="ajouter_location.php" style="background:#2563eb; color:white; padding:10px 20px; border-radius:8px; text-decoration:none;">+ Nouvelle Location</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Véhicule</th>
                    <th>Total</th>
                    <th>Paiement</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo $row['nom']." ".$row['prenom']; ?></td>
                    <td><?php echo $row['marque']." ".$row['modele']; ?></td>
                    <td><?php echo number_format($row['cout_total'], 0); ?> FCFA</td>
                    <td>
                        <a href="valider_paiement.php?id=<?php echo $row['id']; ?>" 
                           class="badge <?php echo ($row['statut_paiement'] == 'Payé') ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo ($row['statut_paiement'] == 'Payé') ? '✔ Payé' : '⌛ En attente'; ?>
                        </a>
                    </td>
                    <td><a href="generer_facture.php?id=<?php echo $row['id']; ?>"><i class="fas fa-file-invoice"></i></a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <p><a href="dashboard.php">← Retour au Dashboard</a></p>
    </div>
</body>
</html>
