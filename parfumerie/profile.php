<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$page_title = "Mon Compte";
$db = getDB();

// Récupérer les infos utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include 'templates/header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
    <div class="text-center mb-6">
        <i class="fas fa-user-circle text-6xl text-yellow-600"></i>
        <h1 class="text-3xl font-playfair font-bold mt-3">Mon Compte</h1>
    </div>
    
    <div class="space-y-4">
        <div class="border-b pb-3">
            <label class="text-gray-500 text-sm">Nom complet</label>
            <p class="font-semibold text-lg"><?php echo htmlspecialchars($user['full_name']); ?></p>
        </div>
        <div class="border-b pb-3">
            <label class="text-gray-500 text-sm">Nom d'utilisateur</label>
            <p class="font-semibold text-lg"><?php echo htmlspecialchars($user['username']); ?></p>
        </div>
        <div class="border-b pb-3">
            <label class="text-gray-500 text-sm">Email</label>
            <p class="font-semibold text-lg"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="border-b pb-3">
            <label class="text-gray-500 text-sm">Rôle</label>
            <p class="font-semibold text-lg">
                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">
                    <?php echo $user['role']; ?>
                </span>
            </p>
        </div>
    </div>
    
    <div class="mt-8 flex gap-4">
        <button onclick="window.location.href='/dashboard.php'" class="btn-luxury flex-1 text-center">
            <i class="fas fa-chart-line mr-2"></i>Dashboard
        </button>
        <button onclick="window.location.href='/logout.php'" class="bg-red-500 text-white px-6 py-2 rounded-full hover:bg-red-600 transition">
            <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
        </button>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
