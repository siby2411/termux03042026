<?php 
require('config/db.php'); 

$message = "";
if (!isset($_GET['id'])) { header('Location: liste_partenaires.php'); exit; }

$id = (int)$_GET['id'];

// 1. Récupération des données actuelles
$stmt = $pdo->prepare("SELECT * FROM annuaire_medical WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) { die("Partenaire introuvable."); }

// 2. Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE annuaire_medical SET nom=?, specialite=?, adresse=?, telephone=?, zone_geographique=?, categorie=? WHERE id=?";
        $pdo->prepare($sql)->execute([
            $_POST['nom'], 
            $_POST['specialite'], 
            $_POST['adresse'], 
            $_POST['telephone'], 
            $_POST['zone'], 
            $_POST['categorie'], 
            $id
        ]);
        header('Location: liste_partenaires.php?msg=updated');
        exit;
    } catch (Exception $e) {
        $message = "<div style='color:red; padding:10px;'>Erreur : " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>OMEGA - Modifier Partenaire</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; max-width: 500px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #003366; border-bottom: 2px solid #003366; padding-bottom: 10px; }
        label { display: block; margin-top: 10px; font-weight: bold; font-size: 13px; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 6px; margin-top: 20px; cursor: pointer; font-weight: bold; }
        button:hover { background: #2980b9; }
        .back-link { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #95a5a6; font-size: 13px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Modifier l'établissement</h2>
    <?php echo $message; ?>
    <form method="POST">
        <label>Nom :</label>
        <input type="text" name="nom" value="<?php echo htmlspecialchars($p['nom']); ?>" required>
        
        <label>Catégorie :</label>
        <select name="categorie">
            <option value="PHARMACIE" <?php if($p['categorie']=='PHARMACIE') echo 'selected'; ?>>PHARMACIE</option>
            <option value="CLINIQUE" <?php if($p['categorie']=='CLINIQUE') echo 'selected'; ?>>CLINIQUE</option>
            <option value="URGENCE" <?php if($p['categorie']=='URGENCE') echo 'selected'; ?>>URGENCE</option>
            <option value="DENTAIRE" <?php if($p['categorie']=='DENTAIRE') echo 'selected'; ?>>CABINET DENTAIRE</option>
        </select>

        <label>Spécialité :</label>
        <input type="text" name="specialite" value="<?php echo htmlspecialchars($p['specialite']); ?>">

        <label>Téléphone :</label>
        <input type="text" name="telephone" value="<?php echo htmlspecialchars($p['telephone']); ?>" required>

        <label>Zone Géographique :</label>
        <input type="text" name="zone" value="<?php echo htmlspecialchars($p['zone_geographique']); ?>" required>

        <label>Adresse complète :</label>
        <textarea name="adresse" rows="2"><?php echo htmlspecialchars($p['adresse']); ?></textarea>

        <button type="submit">METTRE À JOUR</button>
    </form>
    <a href="liste_partenaires.php" class="back-link">Annuler et retourner à la liste</a>
</div>
</body>
</html>
