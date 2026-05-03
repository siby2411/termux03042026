<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Mon Panier";

$cart_items = [];
if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    
    foreach($products as $p) {
        $cart_items[] = [
            'product' => $p,
            'quantity' => $_SESSION['cart'][$p['id']]['quantity']
        ];
    }
}

include 'templates/header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-6 max-w-4xl mx-auto">
    <h1 class="text-3xl font-playfair font-bold mb-6">🛒 Mon Panier</h1>
    
    <?php if(empty($cart_items)): ?>
        <div class="text-center py-12">
            <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Votre panier est vide</p>
            <a href="/pos.php" class="btn-pizza inline-block mt-4">Commander maintenant</a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach($cart_items as $item): ?>
            <div class="flex justify-between items-center border-b pb-4">
                <div>
                    <h3 class="font-bold"><?php echo htmlspecialchars($item['product']['product_name']); ?></h3>
                    <p class="text-red-600 font-bold"><?php echo formatPrice($item['product']['unit_price']); ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <span>Quantité: <?php echo $item['quantity']; ?></span>
                    <button onclick="removeFromCart(<?php echo $item['product']['id']; ?>)" class="text-red-500">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="font-bold"><?php echo formatPrice($item['product']['unit_price'] * $item['quantity']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php 
        $total = 0;
        foreach($cart_items as $item) $total += $item['product']['unit_price'] * $item['quantity'];
        ?>
        <div class="border-t pt-4 mt-4">
            <div class="flex justify-between text-xl font-bold">
                <span>Total:</span>
                <span class="text-red-600"><?php echo formatPrice($total); ?></span>
            </div>
            <button onclick="checkout()" class="btn-pizza w-full mt-4 py-3">Passer la commande</button>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFromCart(id) {
    fetch('/api/remove_from_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + id
    }).then(() => location.reload());
}

function checkout() {
    window.location.href = '/pos.php';
}
</script>

<?php include 'templates/footer.php'; ?>
