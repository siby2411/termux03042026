</main>

<!-- Footer -->
<footer class="luxury-gradient text-white py-12 mt-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-spa text-3xl text-yellow-400"></i>
                    <h3 class="font-playfair text-2xl font-bold">Omega Cosmetique</h3>
                </div>
                <p class="text-gray-300 text-sm">Votre destination premium pour les parfums et cosmétiques de luxe au Sénégal.</p>
            </div>
            
            <div>
                <h4 class="font-playfair text-xl font-bold mb-4">Liens Rapides</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/products.php" class="text-gray-300 hover:text-yellow-400 transition">Nos Produits</a></li>
                    <li><a href="/customers.php" class="text-gray-300 hover:text-yellow-400 transition">Clients</a></li>
                    <li><a href="/orders.php" class="text-gray-300 hover:text-yellow-400 transition">Commandes</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-playfair text-xl font-bold mb-4">Paiements</h4>
                <div class="flex gap-4 text-3xl">
                    <i class="fab fa-wifi"></i>
                    <i class="fas fa-mobile-alt"></i>
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            
            <div>
                <h4 class="font-playfair text-xl font-bold mb-4">Contact</h4>
                <p class="text-sm text-gray-300"><i class="fas fa-phone-alt mr-2"></i> +221 78 000 00 00</p>
                <p class="text-sm text-gray-300"><i class="fas fa-envelope mr-2"></i> contact@omegainfo.sn</p>
            </div>
        </div>
        
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm">
            <p>&copy; 2026 Omega Informatique CONSULTING. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script>
function updateCartCount() {
    fetch('/api/cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.getElementById('cartCount');
            if(cartCount) cartCount.innerText = data.count || 0;
        })
        .catch(err => console.log('Cart count error:', err));
}

updateCartCount();
setInterval(updateCartCount, 5000);
</script>
</body>
</html>
