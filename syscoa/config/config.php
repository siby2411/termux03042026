<?php
// config/config.php
define('APP_NAME', 'SYSCO OHADA');
define('APP_VERSION', '2.0 Professional');
define('APP_AUTHOR', 'Sysco Solutions');
define('APP_YEAR', date('Y'));

// Configuration de l'application
$config = [
    // Base de données
    'database' => [
        'host' => 'localhost',
        'name' => 'sysco_ohada',
        'user' => 'root',
        'pass' => '123',
        'charset' => 'utf8mb4'
    ],
    
    // Application
    'app' => [
        'debug' => true,
        'timezone' => 'Africa/Douala',
        'locale' => 'fr_FR',
        'currency' => 'FCFA',
        'date_format' => 'd/m/Y'
    ],
    
    // Sécurité
    'security' => [
        'session_timeout' => 3600, // 1 heure
        'password_min_length' => 8,
        'password_require_special' => true,
        'max_login_attempts' => 5,
        'lockout_time' => 900 // 15 minutes
    ],
    
    // Modules
    'modules' => [
        'comptabilite' => [
            'name' => 'Comptabilité',
            'version' => '1.0',
            'requires' => ['ecritures', 'journaux', 'comptes_ohada']
        ],
        'banque' => [
            'name' => 'Module Banque',
            'version' => '1.0',
            'requires' => ['rapprochement', 'releves']
        ],
        'analyse' => [
            'name' => 'Analyse Financière',
            'version' => '1.0',
            'requires' => ['sig', 'bilans', 'ratios']
        ],
        'stock' => [
            'name' => 'Gestion des Stocks',
            'version' => '1.0',
            'requires' => ['articles', 'mouvements', 'inventaire']
        ],
        'cloture' => [
            'name' => 'Clôture Exercice',
            'version' => '1.0',
            'requires' => ['amortissements', 'provisions', 'regularisations']
        ]
    ]
];

// Application globals
$GLOBALS['config'] = $config;
