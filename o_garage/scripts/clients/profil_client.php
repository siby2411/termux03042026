<?php
// Redirection automatique vers le nouveau nom de fichier
header("Location: profil.php?" . $_SERVER['QUERY_STRING']);
exit();
