<?php
include 'includes/db.php';
include 'includes/header.php';
include 'data_secteurs.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO offres (titre, description, date_limite, secteur) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['titre'], $_POST['description'], $_POST['date_limite'], $_POST['secteur']]);
        $message = "<div style='color:green; padding:10px; border:1px solid green;'>Offre publiée avec succès !</div>";
    } catch (PDOException $e) {
        $message = "<div style='color:red;'>Erreur lors de la publication : " . $e->getMessage() . "</div>";
    }
}
?>

<div class="admin-form" style="max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2>Publier une offre d'emploi</h2>
    <?php echo $message; ?>
    <form method="post">
        <input type="text" name="titre" placeholder="Titre du poste" required style="width:100%; margin-bottom:10px; padding:8px;">
        
        <textarea name="description" placeholder="Description détaillée de l'offre" required style="width:100%; height:150px; margin-bottom:10px; padding:8px;"></textarea>
        
        <label>Secteur d'activité :</label>
        <select name="secteur" required style="width:100%; margin-bottom:10px; padding:8px;">
            <?php foreach ($secteurs as $s): ?>
                <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
            <?php endforeach; ?>
        </select>
        
        <label>Date limite de candidature :</label>
        <input type="date" name="date_limite" required style="width:100%; margin-bottom:20px; padding:8px;">
        
        <button type="submit" style="background:#0056b3; color:white; padding:10px 20px; border:none; cursor:pointer; width:100%;">Publier l'offre</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
