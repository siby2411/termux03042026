<?php
// Vérifiez si le fichier existe
if (!file_exists('db_connect_ecole.php')) {
    die("ERREUR: Le fichier db_connect_ecole.php n'est pas trouvé.");
}
require_once 'db_connect_ecole.php';

// Vérifiez si le fichier existe
if (!file_exists('header_ecole.php')) {
    die("ERREUR: Le fichier header_ecole.php n'est pas trouvé.");
}
require_once 'header_ecole.php';

echo "<h2>Inclusions OK. Le code a démarré.</h2>";
// Si vous voyez ce message, l'erreur est plus bas dans le script.
?>
