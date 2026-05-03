<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Gestion des Produits";

// Pagination
$page = $_GET['page'] ?? 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$where = $search ? "WHERE product_name LIKE '%$search%' OR product_code LIKE '%$search%'" : "";
$products = $db->query("SELECT p.*, c.category_name, b.brand_name FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN brands b ON p.brand_id=b.id $where ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset")->fetchAll();
$total = $db->query("SELECT COUNT(*) FROM products $where")->fetchColumn();
$total_pages = ceil($total / $limit);

$categories = $db->query("SELECT * FROM categories WHERE is_active=1")->fetchAll();
$brands = $db->query("SELECT * FROM brands WHERE is_active=1")->fetchAll();

include 'templates/header.php';
?>
<div class="flex justify-between items-center mb-6"><h1 class="text-3xl font-playfair font-bold">Produits</h1><button onclick="openProductModal()" class="btn-luxury"><i class="fas fa-plus mr-2"></i>Nouveau Produit</button></div>

<div class="bg-white rounded-2xl p-4 mb-6"><form method="GET" class="flex gap-4"><input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>" class="flex-1 px-4 py-2 border rounded-lg"><button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg">Rechercher</button></form></div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
<?php foreach($products as $p): ?>
<div class="product-card"><div class="h-48 bg-gray-100 flex items-center justify-center relative"><?php if($p['image_url']): ?><img src="<?php echo $p['image_url']; ?>" class="h-full object-cover"><?php else: ?><i class="fas fa-perfume text-6xl text-gray-400"></i><?php endif; ?><?php if($p['discount_percentage']>0): ?><div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs">-<?php echo $p['discount_percentage']; ?>%</div><?php endif; ?></div>
<div class="p-4"><div class="flex justify-between"><div><h3 class="font-semibold"><?php echo htmlspecialchars($p['product_name']); ?></h3><p class="text-xs text-gray-500"><?php echo $p['brand_name'] ?? 'Sans marque'; ?></p></div><p class="font-bold text-yellow-600"><?php echo formatPrice($p['unit_price']); ?></p></div>
<div class="flex justify-between items-center mt-3"><span class="text-sm <?php echo $p['stock_quantity']<=5?'text-red-500':'text-gray-500'; ?>"><i class="fas fa-box mr-1"></i>Stock: <?php echo $p['stock_quantity']; ?></span>
<div><button onclick="editProduct(<?php echo $p['id']; ?>)" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></button><button onclick="deleteProduct(<?php echo $p['id']; ?>)" class="text-red-600"><i class="fas fa-trash"></i></button></div></div></div></div>
<?php endforeach; ?>
</div>

<?php if($total_pages>1): ?><div class="flex justify-center mt-8 gap-2"><?php for($i=1;$i<=$total_pages;$i++): ?><a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 border rounded <?php echo $i==$page?'bg-yellow-500 text-white':''; ?>"><?php echo $i; ?></a><?php endfor; ?></div><?php endif; ?>

<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"><div class="bg-white rounded-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto"><div class="flex justify-between mb-4"><h2 id="modalTitle" class="text-2xl font-playfair font-bold">Ajouter Produit</h2><i class="fas fa-times cursor-pointer text-2xl" onclick="closeModal()"></i></div>
<form id="productForm" method="POST" action="/api/product_crud.php"><input type="hidden" name="action" id="formAction" value="add"><input type="hidden" name="id" id="productId">
<div class="grid grid-cols-2 gap-4"><div><label>Nom *</label><input type="text" name="product_name" id="product_name" required class="w-full px-3 py-2 border rounded"></div><div><label>Prix (CFA) *</label><input type="number" name="unit_price" id="unit_price" required class="w-full px-3 py-2 border rounded"></div>
<div><label>Catégorie</label><select name="category_id" id="category_id" class="w-full px-3 py-2 border rounded"><option value="">Sélectionner</option><?php foreach($categories as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo $c['category_name']; ?></option><?php endforeach; ?></select></div>
<div><label>Marque</label><select name="brand_id" id="brand_id" class="w-full px-3 py-2 border rounded"><option value="">Sélectionner</option><?php foreach($brands as $b): ?><option value="<?php echo $b['id']; ?>"><?php echo $b['brand_name']; ?></option><?php endforeach; ?></select></div>
<div><label>Stock</label><input type="number" name="stock_quantity" id="stock_quantity" class="w-full px-3 py-2 border rounded"></div>
<div><label>Remise (%)</label><input type="number" name="discount_percentage" id="discount_percentage" step="0.01" class="w-full px-3 py-2 border rounded"></div>
<div class="col-span-2"><label>Description</label><textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border rounded"></textarea></div></div>
<div class="flex justify-end gap-3 mt-6"><button type="button" onclick="closeModal()" class="px-4 py-2 border rounded">Annuler</button><button type="submit" class="btn-luxury px-6 py-2">Enregistrer</button></div></form></div></div>

<script>
function openProductModal() { document.getElementById('modalTitle').innerText='Ajouter Produit'; document.getElementById('formAction').value='add'; document.getElementById('productForm').reset(); document.getElementById('productModal').classList.add('flex'); document.getElementById('productModal').classList.remove('hidden'); }
function closeModal() { document.getElementById('productModal').classList.remove('flex'); document.getElementById('productModal').classList.add('hidden'); }
function editProduct(id) { fetch(`/api/get_product.php?id=${id}`).then(r=>r.json()).then(d=>{if(d.success){document.getElementById('modalTitle').innerText='Modifier Produit'; document.getElementById('formAction').value='edit'; document.getElementById('productId').value=d.product.id; document.getElementById('product_name').value=d.product.product_name; document.getElementById('unit_price').value=d.product.unit_price; document.getElementById('category_id').value=d.product.category_id; document.getElementById('brand_id').value=d.product.brand_id; document.getElementById('stock_quantity').value=d.product.stock_quantity; document.getElementById('discount_percentage').value=d.product.discount_percentage; document.getElementById('description').value=d.product.description; openProductModal();}});}
function deleteProduct(id) { Swal.fire({title:'Confirmation',text:'Supprimer ce produit?',icon:'warning',showCancelButton:true}).then(r=>{if(r.isConfirmed){fetch('/api/product_crud.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete&id=${id}`}).then(()=>location.reload());}});}
document.getElementById('productForm')?.addEventListener('submit',async(e)=>{e.preventDefault();const fd=new FormData(e.target);const r=await fetch('/api/product_crud.php',{method:'POST',body:fd});const d=await r.json();if(d.success){Swal.fire('Succès','Produit enregistré','success').then(()=>location.reload());}else Swal.fire('Erreur',d.error,'error');});
</script>
<?php include 'templates/footer.php'; ?>
