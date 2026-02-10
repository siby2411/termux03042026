<!DOCTYPE html>
<html><head><title>Comptable Dashboard</title><style>body{font-family:Arial;padding:20px;}</style></head>
<body>
<h1>📊 Dashboard Comptable</h1>
<p>Sociétés disponibles:</p>
<ul>
<?php foreach($societes as $societe): ?>
    <li><?= htmlspecialchars($societe['nom_societe']) ?> (<?= $societe['exercice_courant'] ?>)</li>
<?php endforeach; ?>
</ul>
<a href="?action=logout">Déconnexion</a>
</body></html>

