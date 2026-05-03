<?php
if (!defined('HEADER_LOADED')) {
    define('HEADER_LOADED', true);
    $cart_count = function_exists('getCartCount') ? getCartCount() : 0;
    
    if (!isset($db) && function_exists('getDB')) {
        $db = getDB();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Omega Pizzeria - La meilleure pizza au Sénégal">
    <title><?php echo $page_title ?? 'Omega Pizzeria'; ?> | Pizzeria & Restaurant</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f5f0 0%, #e8e0d5 100%); min-height: 100vh; }
        .pizza-gradient { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 50%, #d35400 100%); }
        .btn-pizza { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; padding: 12px 30px; border-radius: 50px; font-weight: 600; transition: all 0.3s; border: none; cursor: pointer; }
        .btn-pizza:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(231,76,60,0.4); }
        .product-card { background: white; border-radius: 20px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
        .stat-card { background: white; border-radius: 20px; padding: 20px; transition: transform 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stat-card:hover { transform: translateY(-5px); }
        .menu-dropdown { position: relative; display: inline-block; }
        .menu-dropdown-content { display: none; position: absolute; background: white; min-width: 220px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); border-radius: 10px; z-index: 100; }
        .menu-dropdown:hover .menu-dropdown-content { display: block; }
        .menu-dropdown-content a { color: #333; padding: 12px 16px; text-decoration: none; display: block; transition: 0.3s; border-radius: 10px; }
        .menu-dropdown-content a:hover { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; }
    </style>
</head>
<body>
<div class="pizza-gradient text-white py-2">
    <div class="container mx-auto px-4 flex justify-between items-center text-sm">
        <div class="flex items-center gap-4">
            <span><i class="fas fa-phone-alt mr-2"></i> 77 654 28 03</span>
            <span><i class="fas fa-envelope mr-2"></i> contact@omegapizzeria.sn</span>
        </div>
        <div class="flex items-center gap-4">
            <?php if(function_exists('isLoggedIn') && isLoggedIn()): ?>
                <span><i class="fas fa-user-circle mr-2"></i><?php echo $_SESSION['user_name'] ?? 'Utilisateur'; ?></span>
                <a href="/logout.php" class="hover:text-yellow-300"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php else: ?>
                <a href="/login.php" class="hover:text-yellow-300"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center flex-wrap">
            <div class="flex items-center gap-3 cursor-pointer" onclick="window.location.href='/'">
                <i class="fas fa-pizza-slice text-3xl text-red-600"></i>
                <div>
                    <h1 class="font-playfair text-2xl font-bold text-red-600">Omega Pizzeria</h1>
                    <p class="text-xs text-gray-500">La meilleure pizza du Sénégal</p>
                </div>
            </div>
            
            <div class="hidden lg:flex gap-4 flex-wrap">
                <a href="/index.php" class="text-gray-700 hover:text-red-600"><i class="fas fa-home mr-1"></i>Accueil</a>
                
                <!-- Menu Produits -->
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-pizza-slice mr-1"></i>Produits ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/products.php"><i class="fas fa-list mr-2"></i>Tous les produits</a>
                        <a href="/products.php?action=add"><i class="fas fa-plus mr-2"></i>Ajouter produit</a>
                    </div>
                </div>
                
                <!-- Menu Commandes -->
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-shopping-cart mr-1"></i>Commandes ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/orders.php"><i class="fas fa-list mr-2"></i>Toutes les commandes</a>
                        <a href="/pos.php"><i class="fas fa-cash-register mr-2"></i>Nouvelle commande (POS)</a>
                    </div>
                </div>
                
                <!-- Menu Clients -->
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-users mr-1"></i>Clients ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/customers.php"><i class="fas fa-list mr-2"></i>Tous les clients</a>
                        <a href="/customers.php?action=add"><i class="fas fa-user-plus mr-2"></i>Nouveau client</a>
                    </div>
                </div>
                
                <!-- Menu Réservations -->
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-calendar-alt mr-1"></i>Réservations ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/reservations.php"><i class="fas fa-plus-circle mr-2"></i>Nouvelle réservation</a>
                        <a href="/reservations_list.php"><i class="fas fa-list mr-2"></i>Liste des réservations</a>
                    </div>
                </div>
                
                <!-- Menu Gestion des stocks -->
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-boxes mr-1"></i>Stock ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/supplies.php"><i class="fas fa-boxes mr-2"></i>Gestion des stocks</a>
                        <a href="/supplies.php?tab=supply"><i class="fas fa-truck mr-2"></i>Approvisionnements</a>
                        <a href="/supplies.php?tab=issue"><i class="fas fa-sign-out-alt mr-2"></i>Sorties (consommation)</a>
                        <a href="/supplies.php?tab=return"><i class="fas fa-undo-alt mr-2"></i>Retours (fin journée)</a>
                        <a href="/supplies.php?tab=ingredient"><i class="fas fa-plus mr-2"></i>Nouvel ingrédient</a>
                    </div>
                </div>
                
                <!-- Menu Rapports & Statistiques -->
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-chart-line mr-1"></i>Statistiques ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/financial_reports.php"><i class="fas fa-chart-bar mr-2"></i>États financiers</a>
                        <a href="/financial_reports.php?period=daily"><i class="fas fa-calendar-day mr-2"></i>Ventes journalières</a>
                        <a href="/financial_reports.php?period=weekly"><i class="fas fa-calendar-week mr-2"></i>Ventes hebdomadaires</a>
                        <a href="/financial_reports.php?period=monthly"><i class="fas fa-calendar-alt mr-2"></i>Ventes mensuelles</a>
                        <a href="/financial_reports.php?period=yearly"><i class="fas fa-calendar-year mr-2"></i>Ventes annuelles</a>
                    </div>
                </div>
                
                <!-- Menu Paiements -->
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-credit-card mr-1"></i>Paiements ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/payments.php"><i class="fas fa-list mr-2"></i>Tous les paiements</a>
                        <a href="/payments.php?method=wave"><i class="fab fa-wifi mr-2"></i>Paiements Wave</a>
                        <a href="/payments.php?method=orange_money"><i class="fas fa-mobile-alt mr-2"></i>Orange Money</a>
                    </div>
                </div>
                
                <!-- Menu Gestion financière -->
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-chart-pie mr-1"></i>Finance ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/expenses.php"><i class="fas fa-money-bill-wave mr-2"></i>Gestion des charges</a>
                        <a href="/financial_reports.php"><i class="fas fa-file-invoice-dollar mr-2"></i>Bilan financier</a>
                    </div>
                </div>
                
                <!-- Menu Dashboard Admin -->
                <?php if(function_exists('isLoggedIn') && isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <div class="menu-dropdown">
                    <a href="#" class="text-gray-700 hover:text-red-600"><i class="fas fa-tachometer-alt mr-1"></i>Admin ▼</a>
                    <div class="menu-dropdown-content">
                        <a href="/dashboard.php"><i class="fas fa-chart-line mr-2"></i>Dashboard</a>
                        <a href="/users.php"><i class="fas fa-users-cog mr-2"></i>Utilisateurs</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="flex gap-4 items-center">
                <i class="fas fa-search text-xl cursor-pointer hover:text-red-600" onclick="toggleSearch()"></i>
                <div class="relative cursor-pointer" onclick="window.location.href='/cart.php'">
                    <i class="fas fa-shopping-cart text-xl hover:text-red-600"></i>
                    <span id="cartCount" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                </div>
                <i class="fas fa-bars text-2xl lg:hidden cursor-pointer" onclick="toggleMobileMenu()"></i>
            </div>
        </div>
        
        <div id="searchBar" class="hidden mt-3">
            <input type="text" id="searchInput" placeholder="Rechercher pizza, burger, boisson..." class="w-full px-4 py-3 border rounded-full focus:outline-none focus:border-red-500">
            <div id="searchResults" class="absolute z-50 w-full bg-white rounded-lg shadow-lg mt-1 hidden"></div>
        </div>
    </div>
</nav>

<div id="mobileSidebar" class="fixed left-[-280px] top-0 h-full w-64 bg-white z-50 transition-all duration-300 p-4 shadow-xl overflow-y-auto">
    <div class="flex justify-between mb-6"><h3 class="font-bold">Menu</h3><i class="fas fa-times cursor-pointer" onclick="toggleMobileMenu()"></i></div>
    <a href="/index.php" class="block py-2 border-b">🏠 Accueil</a>
    <a href="/products.php" class="block py-2 border-b">🍕 Produits</a>
    <a href="/customers.php" class="block py-2 border-b">👥 Clients</a>
    <a href="/orders.php" class="block py-2 border-b">📦 Commandes</a>
    <a href="/pos.php" class="block py-2 border-b">💰 Point de vente (POS)</a>
    <a href="/reservations.php" class="block py-2 border-b">📅 Réservations</a>
    <a href="/supplies.php" class="block py-2 border-b">📦 Gestion des stocks</a>
    <a href="/financial_reports.php" class="block py-2 border-b">📊 États financiers</a>
    <a href="/expenses.php" class="block py-2 border-b">💸 Charges</a>
</div>
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40" onclick="toggleMobileMenu()"></div>

<main class="container mx-auto px-4 py-8">

<script>
function toggleMobileMenu() {
    document.getElementById('mobileSidebar').classList.toggle('left-0');
    document.getElementById('overlay').classList.toggle('hidden');
}
function toggleSearch() { document.getElementById('searchBar').classList.toggle('hidden'); }
function updateCartCount() { fetch('/api/cart_count.php').then(r=>r.json()).then(d=>{if(d.count!==undefined) document.getElementById('cartCount').innerText=d.count;}); }
setInterval(updateCartCount, 3000);
</script>
<li class="nav-item">
    <a class="nav-link" href="/modules/supplies/index.php">
        <i class="fas fa-truck"></i> Approvisionnements
    </a>
</li>
