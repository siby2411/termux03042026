<?php
// Configuration OMEGA PHARMA
if(!defined('APP_NAME')) define('APP_NAME', 'Omega Sen Pharma');
if(!defined('APP_VERSION')) define('APP_VERSION', '2.0.2');
if(!defined('APP_TIMEZONE')) define('APP_TIMEZONE', 'Africa/Dakar');
if(!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600);

// Base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmacie');
define('DB_USER', 'root');
define('DB_PASS', '');

date_default_timezone_set(APP_TIMEZONE);
