<?php
// /var/www/piece_auto/config/globals.php

// Correction critique : Définition de l'URL racine de l'application.
// Nous incluons /public car le serveur web est configuré pour l'utiliser comme DocumentRoot virtuel.
// Tous les liens de redirection et de menu utiliseront désormais ce chemin.
$GLOBALS['app_root'] = 'http://192.168.1.33:8080/piece_auto/public'; 
?>
