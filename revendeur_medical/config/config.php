<?php
/**
 * Configuration principale — Revendeur Matériel Médical
 */

define('APP_NAME',    'MedEquip Pro');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE','Africa/Dakar');
define('DEVISE',      'FCFA');
define('TVA_DEFAULT', 18.00); // TVA 18% sur matériel médical au Sénégal

define('DB_HOST',   '127.0.0.1');
define('DB_PORT',   '3306');
define('DB_NAME',   'revendeur_medical');
define('DB_USER',   'root');
define('DB_PASS',   '');
define('DB_CHARSET','utf8mb4');

define('ROOT_PATH',    dirname(__DIR__));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('UPLOAD_PATH',  ROOT_PATH . '/uploads');
define('LOG_PATH',     ROOT_PATH . '/logs');
define('TMPL_PATH',    ROOT_PATH . '/templates');
define('SESSION_LIFETIME', 7200);

define('SOCIETE_NOM',     'MedEquip Sénégal SARL');
define('SOCIETE_ADRESSE', 'Dakar, Sénégal');
define('SOCIETE_NINEA',   'XXXXXXXXXXX');
define('SOCIETE_TEL',     '+221 XX XXX XX XX');

date_default_timezone_set(APP_TIMEZONE);
