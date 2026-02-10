<?php
// Fichier : rapports_ventes.php
// Outil d'Aide à la Décision (Rapports OLAP simples)

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();
$vendeurs = []; // Liste des vendeurs pour le filtre
$result_vendeurs = $conn->query("SELECT id_vendeur, nom FROM vendeurs ORDER BY nom ASC");
while ($row = $result_vendeurs->fetch_assoc()) {
    $vendeurs[] = $row;
}

// --- LOGIQUE DE FILTRAGE PAR PÉRIODE ---
$date_debut = $_GET['date_debut'] ?? date('Y-m-01'); // Par défaut : début du mois
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');      // Par défaut : aujourd'hui
$id_vendeur_filtre = intval($_GET['id_vendeur'] ?? 0);

// Assurez-vous que l'utilisateur est admin ou que le filtre est sur son propre ID
if (!isset($_SESSION['est_admin']) || !$_SESSION['est_admin']) {
    $id_vendeur_filtre = $_SESSION['id_vendeur'];
}

$ventes_vendeur = [];
$top_produits = [];

// ----------------------------------------------------
// 1. PERFORMANCE DES VENDEURS
// ----------------------------------------------------
$sql_perf = "
    SELECT
        v.nom AS nom_vendeur,
        COUNT(f.id_facture) AS nombre_documents,
        SUM(CASE WHEN f.type_document IN ('FACTURE', 'BL') THEN f.total_facture ELSE 0 END) AS chiffre_affaires_reels,
        SUM(f.total_facture) AS chiffre_affaires_total
    FROM vendeurs v
    JOIN factures f ON v.id_vendeur = f.id_vendeur
    WHERE 
        f.type_document != 'BC' AND
        f.date_facture BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY) 
        " . ($id_vendeur_filtre > 0 ? " AND v.id_vendeur = ?" : "") . "
    GROUP BY v.id_vendeur, v.nom
    ORDER BY chiffre_affaires_reels DESC
";

$params_perf = [$date_debut, $date_fin];
$types_perf = "ss";

if ($id_vendeur_filtre > 0) {
    $params_perf[] = $id_vendeur_filtre;
    $types_perf .= "i";
}

$stmt_perf = $conn->prepare($sql_perf);
$stmt_perf->bind_param($types_perf, ...$params_perf);
$stmt_perf->execute();
$result_perf = $stmt_perf->get_result();
while ($row = $result_perf->fetch_assoc()) {
    $ventes_vendeur[] = $row;
}

// ----------------------------------------------------
// 2. TOP 10 DES PRODUITS VENDUS
// ----------------------------------------------------
$sql_top = "
    SELECT
        p.code_produit,
        p.designation,
        SUM(df.quantite) AS total_quantite_vendue,
        SUM(df.quantite * df.prix_vente) AS total_montant_produit
    FROM produits p
    JOIN details_facture df ON p.id_produit = df.id_produit
    JOIN factures f ON df.id_facture = f.id_facture
    WHERE 
        f.type_document IN ('FACTURE', 'BL') AND
        f.date_facture BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
    GROUP BY p.id_produit, p.code_produit, p.designation
    ORDER BY total_quantite_vendue DESC
    LIMIT 10
";

$stmt_top = $conn->prepare($sql_top);
$stmt_top->bind_param("ss", $date_debut, $date_fin);
$stmt_top->execute();
$result_top = $stmt_top->get_result();
while ($row = $result_top->fetch_assoc()) {
    $top_produits[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapports de Ventes (Aide à la Décision)</title>
</head>
<body>
    <h1>Rapports de Ventes et Aide à la Décision</h1>
    <p><a href="dashboard.php">Retour au Tableau de Bord</a></p>

    <fieldset>
        <legend>Filtre par Période et Vendeur</legend>
        <form method="get" action="rapports_ventes.php">
            <label for="date_debut">Date Début:</label>
            <input type="date" name="date_debut" value="<?php echo htmlspecialchars($date_debut); ?>" required>
            
            <label for="date_fin">Date Fin:</label>
            <input type="date" name="date_fin" value="<?php echo htmlspecialchars($date_fin); ?>" required><br><br>

            <?php if (isset($_SESSION['est_admin']) && $_SESSION['est_admin']): ?>
            <label for="id_vendeur">Vendeur (Admin):</label>
            <select name="id_vendeur">
                <option value="0">Tous les Vendeurs</option>
                <?php foreach ($vendeurs as $v): ?>
                    <option value="<?php echo $v['id_vendeur']; ?>" <?php echo ($id_vendeur_filtre == $v['id_vendeur']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>
            <?php else: ?>
            <p>Rapport filtré sur votre compte.</p>
            <?php endif; ?>
            
            <button type="submit">Appliquer le Filtre</button>
        </form>
    </fieldset>

    <hr>

    <h3>1. Performance des Vendeurs (Ventes Réelles)</h3>
    <?php if (count($ventes_vendeur) > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Vendeur</th>
                <th>Nombre de Documents</th>
                <th>Chiffre d'Affaires Réel (Factures/BL)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventes_vendeur as $v): ?>
            <tr>
                <td><?php echo htmlspecialchars($v['nom_vendeur']); ?></td>
                <td><?php echo $v['nombre_documents']; ?></td>
                <td><strong><?php echo number_format($v['chiffre_affaires_reels'], 2, ',', ' '); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucune vente enregistrée pour la période sélectionnée.</p>
    <?php endif; ?>

    <hr>

    <h3>2. Top 10 des Produits Vendus (Ventes Réelles)</h3>
    <?php if (count($top_produits) > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Rang</th>
                <th>Code Produit</th>
                <th>Désignation</th>
                <th>Quantité Totale Vendue</th>
                <th>Montant Ventes Cumulé</th>
            </tr>
        </thead>
        <tbody>
            <?php $rank = 1; foreach ($top_produits as $p): ?>
            <tr>
                <td><?php echo $rank++; ?></td>
                <td><?php echo htmlspecialchars($p['code_produit']); ?></td>
                <td><?php echo htmlspecialchars($p['designation']); ?></td>
                <td><?php echo $p['total_quantite_vendue']; ?></td>
                <td><?php echo number_format($p['total_montant_produit'], 2, ',', ' '); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucun produit vendu pour la période sélectionnée.</p>
    <?php endif; ?>

</body>
</html>
