<?php
// Éviter les inclusions multiples
if (!defined('HEADER_LOADED')) {
    define('HEADER_LOADED', true);
    
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
    <meta name="description" content="Cosmétique et Parfumerie de luxe - Omega Informatique CONSULTING">
    <title><?php echo $page_title ?? 'Omega Cosmetique'; ?> | Parfumerie & Cosmétique de Luxe</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .luxury-gradient { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); }
        .gold-gradient { background: linear-gradient(135deg, #D4AF37, #B8860B); }
        .btn-luxury { background: linear-gradient(135deg, #D4AF37, #B8860B); color: white; padding: 12px 30px; border-radius: 50px; font-weight: 600; transition: all 0.3s; border: none; cursor: pointer; display: inline-block; }
        .btn-luxury:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(212,175,55,0.4); }
        .product-card { background: white; border-radius: 15px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
        .stat-card { background: white; border-radius: 20px; padding: 20px; transition: transform 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'playfair': ['Playfair Display', 'serif'],
                        'poppins': ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body>
<!-- Top Bar -->
<div class="luxury-gradient text-white py-2">
    <div class="container mx-auto px-4 flex justify-between items-center text-sm">
        <div class="flex items-center gap-4">
            <span><i class="fas fa-phone-alt mr-2"></i> +221 78 000 00 00</span>
            <span><i class="fas fa-envelope mr-2"></i> contact@omegainfo.sn</span>
        </div>
        <div class="flex items-center gap-4">
            <?php if(function_exists('isLoggedIn') && isLoggedIn()): ?>
                <span><i class="fas fa-user-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></span>
                <a href="/logout.php" class="hover:text-yellow-400 transition"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php else: ?>
                <a href="/login.php" class="hover:text-yellow-400 transition"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Navigation -->
<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center gap-3 cursor-pointer" onclick="window.location.href='/'">
            <i class="fas fa-spa text-3xl gold-gradient bg-clip-text text-transparent"></i>
            <div>
                <h1 class="font-playfair text-2xl font-bold gold-gradient bg-clip-text text-transparent">Omega Cosmetique</h1>
                <p class="text-xs text-gray-500">Parfumerie & Cosmétique de Luxe</p>
            </div>
        </div>
        <div class="hidden md:flex gap-6">
            <a href="/index.php" class="text-gray-700 hover:text-yellow-600 transition font-medium">Accueil</a>
            <a href="/products.php" class="text-gray-700 hover:text-yellow-600 transition font-medium">Produits</a>
            <a href="/customers.php" class="text-gray-700 hover:text-yellow-600 transition font-medium">Clients</a>
            <a href="/orders.php" class="text-gray-700 hover:text-yellow-600 transition font-medium">Commandes</a>
            <?php if(function_exists('isLoggedIn') && isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <a href="/dashboard.php" class="text-gray-700 hover:text-yellow-600 transition font-medium">Dashboard</a>
            <?php endif; ?>
        </div>
        <div class="flex gap-4 items-center">
            <i class="fas fa-search text-xl text-gray-700 cursor-pointer hover:text-yellow-600"></i>
            <div class="relative cursor-pointer">
                <i class="fas fa-shopping-cart text-xl text-gray-700 hover:text-yellow-600"></i>
                <span id="cartCount" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
            </div>
        </div>
    </div>
</nav>

<main class="container mx-auto px-4 py-8">
