<?php 
require('config/db.php'); 

// Logique de suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM annuaire_medical WHERE id = ?")->execute([$id]);
    header('Location: liste_partenaires.php?msg=deleted');
    exit;
}

// Récupération de tous les partenaires
$stmt = $pdo->query("SELECT * FROM annuaire_medical ORDER BY nom ASC");
$partenaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>OMEGA - Gestion de l'Annuaire</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #003366; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: bold; cursor: pointer; border: none; }
        .btn-add { background: #27ae60; color: white; }
        .btn-edit { background: #3498db; color: white; margin-right: 5px; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-back { background: #95a5a6; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #003366; color: white; text-align: left; padding: 12px; }
        td { padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        tr:hover { background: #f9f9f9; }
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .PHARMACIE { background: #dff9fb; color: #130f40; }
        .CLINIQUE { background: #ebfbee; color: #2b8a3e; }
        .DENTAIRE { background: #fff4e6; color: #d9480f; }
    </style>
</head>
<body>
<div class="container">
    <h2>
        Gestion de l'Annuaire Médical
        <div>
            <a href="index.php" class="btn btn-back">Retour</a>
            <a href="ajouter_partenaire.php" class="btn btn-add">+ Ajouter un partenaire</a>
        </div>
    </h2>

    <?php if(isset($_GET['msg'])) echo "<p style='color:green; font-weight:bold;'>Action effectuée avec succès !</p>"; ?>

    <table>
        <thead>
            <tr>
                <th>Nom de l'Etablissement</th>
                <th>Catégorie</th>
                <th>Téléphone</th>
                <th>Zone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($partenaires as $p): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($p['nom']); ?></strong></td>
                <td><span class="badge <?php echo $p['categorie']; ?>"><?php echo $p['categorie']; ?></span></td>
                <td><?php echo htmlspecialchars($p['telephone']); ?></td>
                <td><?php echo htmlspecialchars($p['zone_geographique']); ?></td>
                <td>
                    <a href="modifier_partenaire.php?id=<?php echo $p['id']; ?>" class="btn btn-edit">Modifier</a>
                    <a href="liste_partenaires.php?delete=<?php echo $p['id']; ?>" class="btn btn-delete" onclick="return confirm('Supprimer ce partenaire ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
