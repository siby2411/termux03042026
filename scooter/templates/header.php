<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Omega Scooter'; ?> | Vente de scooters</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
        .scooter-gradient { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
        .btn-scooter { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; padding: 12px 30px; border-radius: 50px; font-weight: 600; transition: all 0.3s; }
        .btn-scooter:hover { transform: scale(1.05); }
        .product-card { background: white; border-radius: 20px; overflow: hidden; transition: all 0.3s; }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .banner-slider { position: relative; overflow: hidden; border-radius: 20px; }
        .slide { display: none; animation: fade 0.5s; }
        .slide.active { display: block; }
        @keyframes fade { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>
<div class="scooter-gradient text-white py-2">
    <div class="container mx-auto px-4 flex justify-between">
        <span><i class="fas fa-phone-alt mr-2"></i> 77 654 28 03</span>
        <span><i class="fas fa-envelope mr-2"></i> contact@omegascooter.sn</span>
    </div>
</div>
<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center gap-3 cursor-pointer" onclick="window.location.href='/'">
            <i class="fas fa-motorcycle text-3xl text-red-600"></i>
            <h1 class="font-playfair text-2xl font-bold text-red-600">Omega Scooter</h1>
        </div>
        <div class="hidden lg:flex gap-6">
            <a href="/index.php" class="hover:text-red-600">Accueil</a>
            <a href="/products.php" class="hover:text-red-600">Scooters</a>
            <a href="/repairs.php" class="hover:text-red-600">Réparations</a>
            <a href="/pos.php" class="hover:text-red-600">Point de vente</a>
            <a href="/dashboard.php" class="hover:text-red-600">Dashboard</a>
        </div>
    </div>
</nav>
<main class="container mx-auto px-4 py-8">
