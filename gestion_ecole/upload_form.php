<?php
$id = intval($_GET['id']);
?>
<div style="font-family: Arial; padding: 20px;">
    <h3>Upload Photo Étudiant (ID: <?= $id ?>)</h3>
    <form action="upload_photo.php?id=<?= $id ?>" method="post" enctype="multipart/form-data">
        <input type="file" name="photo" accept="image/*" required style="margin-bottom:10px;"><br>
        <button type="submit" style="padding: 10px 20px; background:#2c3e50; color:white; border:none; cursor:pointer;">Enregistrer la photo</button>
    </form>
</div>
