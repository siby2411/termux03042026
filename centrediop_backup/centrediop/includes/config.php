<?php
// Configuration du site
define('SITE_NAME', 'Centre de Santé Mamadou Diop');
define('SITE_URL', 'http://192.168.1.3:8000');

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'centrediop');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration des rôles
$roles = [
    'admin' => 'Administrateur',
    'medecin' => 'Médecin',
    'sagefemme' => 'Sage-femme',
    'caissier' => 'Caissier',
    'pharmacien' => 'Pharmacien'
];

// Configuration des services
$services_colors = [
    'Pédiatrie' => '#e74c3c',
    'Odontologie' => '#f39c12',
    'Gynécologie' => '#9b59b6',
    'Médecine générale' => '#1abc9c',
    'Pharmacie' => '#e67e22',
    'Caisse' => '#2ecc71',
    'Accueil' => '#3498db'
];
?>
