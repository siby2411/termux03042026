<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mise à jour Photo</title>
</head>
<body>
    <h2>Mettre à jour la photo de l'étudiant</h2>
    <form action="upload_photo.php" method="POST" enctype="multipart/form-data">
        <label>ID Étudiant :</label>
        <input type="number" name="etudiant_id" required><br><br>
        <label>Choisir une photo (JPG uniquement) :</label>
        <input type="file" name="photo" accept="image/jpeg" required><br><br>
        <button type="submit">Envoyer la photo</button>
    </form>
</body>
</html>
