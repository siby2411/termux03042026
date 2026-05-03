<?php
// Fichier : crud_appro.php
// Formulaire pour enregistrer un approvisionnement + Historique

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

// 1. Établir la connexion UNE SEULE FOIS
$conn = db_connect();

// --- LOGIQUE POUR LA LISTE DÉROULANTE DES PRODUITS ---
$produits = [];
$result = $conn->query("SELECT id_produit, designation, code_produit, stock_actuel FROM produits ORDER BY designation ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
    }
}

// --- LOGIQUE D'AFFICHAGE DE L'HISTORIQUE (READ) ---
$historique_appro = [];
// Cette requête utilise la connexion $conn qui est maintenant ouverte.
$sql_hist = "SELECT a.id_appro, a.quantite_entree, a.date_appro, p.designation, v.nom AS nom_vendeur
             FROM approvisionnements a
             JOIN produits p ON a.id_produit = p.id_produit
             JOIN vendeurs v ON a.id_vendeur = v.id_vendeur
             ORDER BY a.date_appro DESC LIMIT 10"; // 10 dernières transactions

$result_hist = $conn->query($sql_hist);
if ($result_hist && $result_hist->num_rows > 0) {
    while ($row = $result_hist->fetch_assoc()) {
        $historique_appro[] = $row;
    }
}

// 2. Fermer la connexion APRÈS toutes les requêtes
$conn->close();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Approvisionnements</title>
</head>
<body>
    <h1>Enregistrement d'un Approvisionnement</h1>
    <p><a href="dashboard.php">Retour au Tableau de Bord</a></p>

    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h2>Ajouter une Réception de Stock</h2>
    <form action="traitement_appro.php" method="post">
        
        <input type="hidden" name="id_vendeur" value="<?php echo $_SESSION['id_vendeur']; ?>">
        
        <label for="id_produit">Produit Reçu:</label><br>
        <select name="id_produit" required>
            <option value="">-- Sélectionner un produit --</option>
            <?php foreach ($produits as $p): ?>
                <option value="<?php echo $p['id_produit']; ?>">
                    <?php echo htmlspecialchars($p['designation']) . " (" . $p['code_produit'] . ") - Stock: " . $p['stock_actuel']; ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        
        <label for="quantite_entree">Quantité Reçue:</label>
        <input type="number" name="quantite_entree" required min="1"><br><br>
        
        <button type="submit">Enregistrer l'Approvisionnement</button>
    </form>

    <hr>
    
    <h2>Historique des 10 Dernières Réceptions</h2>
    <?php if (count($historique_appro) > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID Appro</th>
                <th>Produit</th>
                <th>Quantité Entrée</th>
                <th>Date</th>
                <th>Enregistré par</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historique_appro as $h): ?>
            <tr>
                <td><?php echo $h['id_appro']; ?></td>
                <td><?php echo htmlspecialchars($h['designation']); ?></td>
                <td><?php echo $h['quantite_entree']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($h['date_appro'])); ?></td>
                <td><?php echo htmlspecialchars($h['nom_vendeur']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucun approvisionnement enregistré récemment.</p>
    <?php endif; ?>

</body>
</html>
