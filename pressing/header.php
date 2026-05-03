<?php
// Démarrer la session au tout début du fichier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration de la base de données
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Pressing Pro</title>
    
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f8f9fa; 
            color: #333; 
            line-height: 1.6;
        }
        
        .header { 
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); 
            color: white; 
            padding: 1rem 0; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo { 
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logo h1 { 
            font-size: 1.8rem; 
            font-weight: 700;
            margin: 0;
        }
        
        .logo-icon {
            font-size: 2rem;
        }
        
        .nav { 
            display: flex; 
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .nav a { 
            color: white; 
            text-decoration: none; 
            padding: 0.75rem 1.25rem; 
            border-radius: 8px; 
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        
        .nav a:hover { 
            background: rgba(255,255,255,0.15); 
            transform: translateY(-2px);
        }
        
        .nav a.active {
            background: rgba(255,255,255,0.2);
            font-weight: 600;
        }
        
        .container { 
            max-width: 1200px; 
            margin: 2rem auto; 
            padding: 0 1rem; 
        }
        
        .card { 
            background: white; 
            border-radius: 12px; 
            padding: 1.5rem; 
            margin-bottom: 1.5rem; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e9ecef;
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .btn { 
            padding: 0.75rem 1.5rem; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .btn-primary { 
            background: #3498db; 
            color: white; 
        }
        
        .btn-primary:hover { 
            background: #2980b9; 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }
        
        .btn-success { 
            background: #27ae60; 
            color: white; 
        }
        
        .btn-success:hover { 
            background: #219a52; 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(39, 174, 96, 0.3);
        }
        
        .btn-danger { 
            background: #e74c3c; 
            color: white; 
        }
        
        .btn-danger:hover { 
            background: #c0392b; 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }
        
        .btn-warning { 
            background: #f39c12; 
            color: white; 
        }
        
        .btn-warning:hover { 
            background: #d35400; 
            transform: translateY(-2px);
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 1rem; 
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        th, td { 
            padding: 1rem; 
            text-align: left; 
            border-bottom: 1px solid #e9ecef; 
        }
        
        th { 
            background: #f8f9fa; 
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .form-group { 
            margin-bottom: 1.25rem; 
        }
        
        label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: 600;
            color: #2c3e50;
        }
        
        input, select, textarea { 
            width: 100%; 
            padding: 0.75rem; 
            border: 2px solid #e9ecef; 
            border-radius: 8px; 
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 1.5rem; 
            margin-bottom: 2rem; 
        }
        
        .stat-card { 
            background: white; 
            padding: 1.5rem; 
            border-radius: 12px; 
            text-align: center; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number { 
            font-size: 2.5rem; 
            font-weight: bold; 
            color: #2c3e50; 
            margin-bottom: 0.5rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #27ae60;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border-color: #e74c3c;
            color: #721c24;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-color: #f39c12;
            color: #856404;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-color: #3498db;
            color: #0c5460;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav {
                justify-content: center;
            }
            
            .nav a {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }
            
            .card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 1rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .logo h1 {
                font-size: 1.5rem;
            }
            
            .nav {
                gap: 0.25rem;
            }
            
            .nav a {
                padding: 0.4rem 0.75rem;
                font-size: 0.8rem;
            }
            
            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }
        }
        
        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success { background: #27ae60; color: white; }
        .badge-warning { background: #f39c12; color: white; }
        .badge-danger { background: #e74c3c; color: white; }
        .badge-info { background: #3498db; color: white; }
        
        /* Utility classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .p-1 { padding: 0.5rem; }
        .p-2 { padding: 1rem; }
        .p-3 { padding: 1.5rem; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <span class="logo-icon">👔</span>
                <h1>Pressing Pro</h1>
            </div>
            <nav class="nav">
                <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    🏠 Dashboard
                </a>
                <a href="clients.php" class="<?= basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : '' ?>">
                    👥 Clients
                </a>
                <a href="commandes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'commandes.php' ? 'active' : '' ?>">
                    📦 Commandes
                </a>
                <a href="services.php" class="<?= basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : '' ?>">
                    🔧 Services
                </a>
                <a href="factures.php" class="<?= basename($_SERVER['PHP_SELF']) == 'factures.php' ? 'active' : '' ?>">
                    🧾 Facturation
                </a>
                <a href="etat_financier.php" class="<?= basename($_SERVER['PHP_SELF']) == 'etat_financier.php' ? 'active' : '' ?>">
                    📊 État Financier
                </a>
            </nav>
        </div>
    </div>
    <div class="container">
