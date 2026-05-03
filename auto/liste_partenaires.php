<?php
// Fichier : liste_partenaires.php
include 'db_connect.php';

$message = "";

// --- Gérer la suppression d'un partenaire ---
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Vérifier si le partenaire possède des voitures
    $check = $conn->prepare("SELECT COUNT(*) FROM voitures WHERE partenaire_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result();
    if ($res->fetch_row()[0] > 0) {
        $message = "<p style='color:red;'>Erreur : Impossible de supprimer ce partenaire car des voitures lui sont liées.</p>";
    } else {
        $del = $conn->prepare("DELETE FROM partenaires WHERE id = ?");
        $del->bind_param("i", $id);
        if ($del->execute()) {
            $message = "<p style='color:green;'>Partenaire supprimé avec succès.</p>";
        }
    }
}

// --- Récupérer les partenaires ---
$sql = "SELECT p.*, (SELECT COUNT(*) FROM voitures WHERE partenaire_id = p.id) as nb_voitures FROM partenaires p";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Partenaires</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f4f4f4; }
        .btn-add { background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <header>
        <h1>Gestion des Partenaires</h1>
        <nav>
            <a href="index.php">Accueil</a> | 
            <a href="ajouter_partenaire.php">Ajouter un Partenaire</a> |
            <a href="liste_voitures.php">Liste Voitures</a>
        </nav>
    </header>

    <main style="padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Partenaires enregistrés</h2>
            <a href="ajouter_partenaire.php" class="btn-add">+ Nouveau Partenaire</a>
        </div>

        <?php echo $message; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom / Agence</th>
                    <th>Contact</th>
                    <th>Voitures en stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['nom']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td><?php echo $row['nb_voitures']; ?> véhicule(s)</td>
                            <td>
                                <a href="modifier_partenaire.php?id=<?php echo $row['id']; ?>">Modifier</a> | 
                                <a href="liste_partenaires.php?action=supprimer&id=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Supprimer ce partenaire ?');" style="color:red;">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">Aucun partenaire trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
