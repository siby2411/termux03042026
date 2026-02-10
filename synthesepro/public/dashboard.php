<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}
?>
<h1>Bienvenue sur SynthesePro</h1>
<p>Rôle : <?= htmlspecialchars($_SESSION['role']) ?></p>
<a href="upload.php">Importer CSV</a><br>
<a href="logout.php">Déconnexion</a>
