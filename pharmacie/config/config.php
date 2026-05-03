<?php
/**
 * Configuration principale — Pharmacie
 * Adapté à l'environnement Termux/proot-distro/MariaDB
 */

define('APP_NAME',    'PharmaSen');
define('APP_VERSION', '1.0.0');
define('APP_LANG',    'fr');
define('APP_TIMEZONE','Africa/Dakar');
define('DEVISE',      'FCFA');
define('TVA_DEFAULT', 0);   // Médicaments exonérés TVA au Sénégal

// Base de données
define('DB_HOST',   '127.0.0.1');
define('DB_PORT',   '3306');
define('DB_NAME',   'pharmacie');
define('DB_USER',   'root');
define('DB_PASS',   '');
define('DB_CHARSET','utf8mb4');

// Chemins
define('ROOT_PATH',    dirname(__DIR__));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('UPLOAD_PATH',  ROOT_PATH . '/uploads');
define('LOG_PATH',     ROOT_PATH . '/logs');
define('TMPL_PATH',    ROOT_PATH . '/templates');

// Session
define('SESSION_LIFETIME', 3600); // 1 heure

// POS
define('TICKET_ENTETE',  "PHARMACIE — DAKAR\nTél : +221 XX XXX XX XX\nNINEA : XXXXXXXXXXX");
define('TICKET_PIED',    "Merci de votre visite\nConservez votre ticket");

date_default_timezone_set(APP_TIMEZONE);
