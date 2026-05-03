<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Gestion des Produits";

// Récupérer les catégories
$categories = $db->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

// Traitement CRUD
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $product_code = generateCode('PIZ');
        $image_url = '';
        
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOAD_PATH . '/products/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $filename = $product_code . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $filename)) {
                $image_url = '/uploads/products/' . $filename;
            }
        }
        
        $stmt = $db->prepare("INSERT INTO products (product_code, product_name, description, category_id, unit_price, price_small, price_medium, price_large, ingredients, preparation_time, is_vegetarian, is_available, is_featured, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$product_code, $_POST['product_name'], $_POST['description'] ?? '', $_POST['category_id'], $_POST['unit_price'], $_POST['price_small'] ?? null, $_POST['price_medium'] ?? null, $_POST['price_large'] ?? null, $_POST['ingredients'] ?? '', $_POST['preparation_time'] ?? 15, $_POST['is_vegetarian'] ?? 0, 1, $_POST['is_featured'] ?? 0, $image_url]);
        $message = $result ? "✅ Produit ajouté avec succès" : "❌ Erreur lors de l'ajout";
    } elseif ($action == 'edit') {
        $stmt = $db->prepare("UPDATE products SET product_name=?, description=?, category_id=?, unit_price=?, price_small=?, price_medium=?, price_large=?, ingredients=?, preparation_time=?, is_vegetarian=?, is_featured=? WHERE id=?");
        $result = $stmt->execute([$_POST['product_name'], $_POST['description'] ?? '', $_POST['category_id'], $_POST['unit_price'], $_POST['price_small'] ?? null, $_POST['price_medium'] ?? null, $_POST['price_large'] ?? null, $_POST['ingredients'] ?? '', $_POST['preparation_time'] ?? 15, $_POST['is_vegetarian'] ?? 0, $_POST['is_featured'] ?? 0, $_POST['id']]);
        $message = $result ? "✅ Produit modifié avec succès" : "❌ Erreur lors de la modification";
    } elseif ($action == 'delete') {
        $stmt = $db->prepare("DELETE FROM products WHERE id=?");
        $result = $stmt->execute([$_POST['id']]);
        $message = $result ? "✅ Produit supprimé" : "❌ Erreur lors de la suppression";
    }
}

// Pagination
$page = $_GET['page'] ?? 1;
$limit = 12;
$offset = ($page - 1) * $limit;
$cat_filter = $_GET['cat'] ?? '';
$where = $cat_filter ? "WHERE category_id = $cat_filter" : "";
$products = $db->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset")->fetchAll();
$total = $db->query("SELECT COUNT(*) FROM products $where")->fetchColumn();
$total_pages = ceil($total / $limit);

include 'templates/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-playfair font-bold">🍕 Gestion des Produits</h1>
    <button onclick="openProductModal()" class="btn-pizza"><i class="fas fa-plus mr-2"></i>Nouveau produit</button>
</div>

<?php if($message): ?>
<div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Filtres par catégorie -->
<div class="flex gap-2 mb-6 flex-wrap">
    <a href="?cat=" class="px-4 py-2 rounded-full <?php echo !$cat_filter ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>">Tous</a>
    <?php foreach($categories as $cat): ?>
    <a href="?cat=<?php echo $cat['id']; ?>" class="px-4 py-2 rounded-full <?php echo $cat_filter == $cat['id'] ? 'bg-red-600 text-white' : 'bg-gray-200'; ?>"><?php echo $cat['category_name']; ?></a>
    <?php endforeach; ?>
</div>

<!-- Grille produits -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
<?php foreach($products as $p): ?>
<div class="product-card">
    <div class="h-48 bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center relative">
        <?php if($p['image_url'] && file_exists($p['image_url'])): ?>
            <img src="<?php echo $p['image_url']; ?>" class="h-full w-full object-cover">
        <?php else: ?>
            <i class="fas fa-pizza-slice text-6xl text-red-400"></i>
        <?php endif; ?>
        <?php if($p['discount_percentage'] > 0): ?>
            <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs">-<?php echo $p['discount_percentage']; ?>%</div>
        <?php endif; ?>
    </div>
    <div class="p-4">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="font-bold"><?php echo htmlspecialchars($p['product_name']); ?></h3>
                <p class="text-xs text-gray-500"><?php echo $p['category_name']; ?></p>
            </div>
            <p class="font-bold text-red-600"><?php echo formatPrice($p['unit_price']); ?></p>
        </div>
        <div class="flex justify-between items-center mt-3">
            <span class="text-sm text-gray-500"><i class="fas fa-clock mr-1"></i><?php echo $p['preparation_time'] ?? 15; ?> min</span>
            <div>
                <button onclick="editProduct(<?php echo $p['id']; ?>)" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></button>
                <button onclick="deleteProduct(<?php echo $p['id']; ?>)" class="text-red-600"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if($total_pages > 1): ?>
<div class="flex justify-center mt-8 gap-2">
    <?php for($i=1; $i<=$total_pages; $i++): ?>
    <a href="?page=<?php echo $i; ?>&cat=<?php echo $cat_filter; ?>" class="px-3 py-1 border rounded <?php echo $i==$page ? 'bg-red-600 text-white' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Modal Ajout/Modification Produit -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4 pb-2 border-b">
            <h2 id="modalTitle" class="text-2xl font-playfair font-bold">Ajouter un produit</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="productId">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Nom du produit *</label>
                    <input type="text" name="product_name" id="product_name" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Catégorie *</label>
                    <select name="category_id" id="category_id" required class="w-full px-3 py-2 border rounded-lg">
                        <?php foreach($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo $c['category_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prix (CFA) *</label>
                    <input type="number" name="unit_price" id="unit_price" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prix small</label>
                    <input type="number" name="price_small" id="price_small" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prix medium</label>
                    <input type="number" name="price_medium" id="price_medium" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prix large</label>
                    <input type="number" name="price_large" id="price_large" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Temps préparation (min)</label>
                    <input type="number" name="preparation_time" id="preparation_time" value="15" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Ingrédients</label>
                    <textarea name="ingredients" id="ingredients" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Sauce tomate, mozzarella, basilic..."></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" id="description" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Image du produit</label>
                    <input type="file" name="product_image" id="product_image" accept="image/*" class="w-full px-3 py-2 border rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Formats acceptés: JPG, PNG, GIF. Max 2MB</p>
                </div>
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_vegetarian" id="is_vegetarian" value="1"> 
                        <span class="text-sm">🌱 Végétarien</span>
                    </label>
                </div>
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"> 
                        <span class="text-sm">⭐ Produit vedette</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Annuler</button>
                <button type="submit" class="btn-pizza px-6 py-2">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openProductModal() {
    document.getElementById('modalTitle').innerText = 'Ajouter un produit';
    document.getElementById('formAction').value = 'add';
    document.getElementById('productForm').reset();
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('productModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('productModal').classList.remove('flex');
    document.getElementById('productModal').classList.add('hidden');
}

function editProduct(id) {
    fetch(`/api/get_product.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data.success && data.product) {
                document.getElementById('modalTitle').innerText = 'Modifier le produit';
                document.getElementById('formAction').value = 'edit';
                document.getElementById('productId').value = data.product.id;
                document.getElementById('product_name').value = data.product.product_name;
                document.getElementById('category_id').value = data.product.category_id;
                document.getElementById('unit_price').value = data.product.unit_price;
                document.getElementById('price_small').value = data.product.price_small;
                document.getElementById('price_medium').value = data.product.price_medium;
                document.getElementById('price_large').value = data.product.price_large;
                document.getElementById('preparation_time').value = data.product.preparation_time;
                document.getElementById('ingredients').value = data.product.ingredients;
                document.getElementById('description').value = data.product.description;
                document.getElementById('is_vegetarian').checked = data.product.is_vegetarian == 1;
                document.getElementById('is_featured').checked = data.product.is_featured == 1;
                openProductModal();
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function deleteProduct(id) {
    Swal.fire({
        title: 'Confirmation',
        text: 'Voulez-vous vraiment supprimer ce produit ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('/api/product_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Supprimé!', 'Le produit a été supprimé.', 'success');
                    location.reload();
                } else {
                    Swal.fire('Erreur!', 'Erreur lors de la suppression', 'error');
                }
            });
        }
    });
}

// Soumission du formulaire
document.getElementById('productForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const response = await fetch('/api/product_crud.php', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    
    if(result.success) {
        Swal.fire('Succès', 'Produit enregistré avec succès', 'success').then(() => {
            location.reload();
        });
    } else {
        Swal.fire('Erreur', result.error || 'Erreur lors de l\'enregistrement', 'error');
    }
});
</script>

<?php include 'templates/footer.php'; ?>
