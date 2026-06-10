<?php include 'header.php'; ?>
<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="text" name="nom" placeholder="Nom Complet" required>
    <input type="email" name="email" placeholder="Adresse Email" required>
    <input type="file" name="cv" accept=".pdf,.doc" required>
    <button type="submit">Soumettre CV</button>
</form>
<?php include 'footer.php'; ?>
