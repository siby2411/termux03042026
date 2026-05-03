<?php
require_once 'config/config.php';
$db = getDB();
$page_title = "Gestion des Approvisionnements";

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add_supply':
                $supply_number = 'SUP-' . date('Ymd') . '-' . rand(1000, 9999);
                $total = $_POST['quantity'] * $_POST['unit_price'];
                $expiry = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
                
                $stmt = $db->prepare("INSERT INTO supplies (supply_number, ingredient_id, quantity, unit_price, total_amount, supply_date, expiry_date, supplier_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$supply_number, $_POST['ingredient_id'], $_POST['quantity'], $_POST['unit_price'], $total, $_POST['supply_date'], $expiry, $_POST['supplier_id'] ?? null, $_POST['notes'] ?? '']);
                
                // Mettre à jour le stock
                $stmt = $db->prepare("UPDATE ingredients SET current_stock = current_stock + ? WHERE id = ?");
                $stmt->execute([$_POST['quantity'], $_POST['ingredient_id']]);
                $success = "Approvisionnement enregistré";
                break;
                
            case 'add_issue':
                $issue_number = 'ISS-' . date('Ymd') . '-' . rand(1000, 9999);
                $stmt = $db->prepare("INSERT INTO stock_issues (issue_number, ingredient_id, quantity, issue_date, issue_type, requested_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$issue_number, $_POST['ingredient_id'], $_POST['quantity'], $_POST['issue_date'], $_POST['issue_type'], $_POST['requested_by'], $_POST['notes'] ?? '']);
                
                // Mettre à jour le stock
                $stmt = $db->prepare("UPDATE ingredients SET current_stock = current_stock - ? WHERE id = ?");
                $stmt->execute([$_POST['quantity'], $_POST['ingredient_id']]);
                $success = "Sortie enregistrée";
                break;
                
            case 'add_return':
                $return_number = 'RET-' . date('Ymd') . '-' . rand(1000, 9999);
                $stmt = $db->prepare("INSERT INTO stock_returns (return_number, ingredient_id, quantity, return_date, issue_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$return_number, $_POST['ingredient_id'], $_POST['quantity'], $_POST['return_date'], $_POST['issue_id'] ?? null, $_POST['notes'] ?? '']);
                
                // Mettre à jour le stock
                $stmt = $db->prepare("UPDATE ingredients SET current_stock = current_stock + ? WHERE id = ?");
                $stmt->execute([$_POST['quantity'], $_POST['ingredient_id']]);
                $success = "Retour enregistré";
                break;
                
            case 'add_ingredient':
                $code = 'ING-' . date('Ymd') . '-' . rand(1000, 9999);
                $stmt = $db->prepare("INSERT INTO ingredients (ingredient_code, ingredient_name, category, unit, unit_price, current_stock, min_stock, is_perishable, expiry_days) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $_POST['ingredient_name'], $_POST['category'], $_POST['unit'], $_POST['unit_price'], $_POST['current_stock'], $_POST['min_stock'], $_POST['is_perishable'] ?? 1, $_POST['expiry_days'] ?? 7]);
                $success = "Ingrédient ajouté";
                break;
        }
    }
}

// Récupération des données
$ingredients = $db->query("SELECT * FROM ingredients WHERE is_active=1 ORDER BY category, ingredient_name")->fetchAll();
$supplies = $db->query("SELECT s.*, i.ingredient_name FROM supplies s JOIN ingredients i ON s.ingredient_id=i.id ORDER BY s.supply_date DESC LIMIT 20")->fetchAll();
$issues = $db->query("SELECT si.*, i.ingredient_name FROM stock_issues si JOIN ingredients i ON si.ingredient_id=i.id ORDER BY si.issue_date DESC LIMIT 20")->fetchAll();
$returns = $db->query("SELECT sr.*, i.ingredient_name FROM stock_returns sr JOIN ingredients i ON sr.ingredient_id=i.id ORDER BY sr.return_date DESC LIMIT 20")->fetchAll();

// Statistiques
$total_stock_value = $db->query("SELECT SUM(current_stock * unit_price) FROM ingredients")->fetchColumn();
$perishable_alerts = $db->query("SELECT COUNT(*) FROM ingredients WHERE is_perishable=1 AND current_stock < min_stock")->fetchColumn();

include 'templates/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card"><div><p class="text-gray-500">Valeur du stock</p><h3 class="text-2xl font-bold text-red-600"><?php echo formatPrice($total_stock_value); ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Ingrédients</p><h3 class="text-2xl font-bold"><?php echo count($ingredients); ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Alertes stock</p><h3 class="text-2xl font-bold text-orange-600"><?php echo $perishable_alerts; ?></h3></div></div>
    <div class="stat-card"><div><p class="text-gray-500">Approvisionnements mois</p><h3 class="text-2xl font-bold"><?php echo count($supplies); ?></h3></div></div>
</div>

<!-- Onglets -->
<div class="mb-6">
    <div class="flex border-b flex-wrap">
        <button class="tab-btn active px-4 py-2 text-red-600 border-b-2 border-red-600" onclick="showTab('stock')">📊 Stock actuel</button>
        <button class="tab-btn px-4 py-2" onclick="showTab('supply')">📦 Approvisionnements</button>
        <button class="tab-btn px-4 py-2" onclick="showTab('issue')">📤 Sorties</button>
        <button class="tab-btn px-4 py-2" onclick="showTab('return')">🔄 Retours</button>
        <button class="tab-btn px-4 py-2" onclick="showTab('ingredient')">➕ Nouvel ingrédient</button>
    </div>
</div>

<!-- Tab Stock -->
<div id="tab-stock" class="tab-content">
    <div class="bg-white rounded-2xl shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr><th class="px-4 py-3">Ingrédient</th><th class="px-4 py-3">Catégorie</th><th class="px-4 py-3 text-right">Stock actuel</th><th class="px-4 py-3 text-right">Stock min</th><th class="px-4 py-3 text-right">Prix unitaire</th><th class="px-4 py-3 text-right">Valeur</th><th class="px-4 py-3">Statut</th></tr>
            </thead>
            <tbody>
                <?php foreach($ingredients as $i): ?>
                <tr class="border-b">
                    <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($i['ingredient_name']); ?><br><span class="text-xs text-gray-500"><?php echo $i['ingredient_code']; ?></span></td>
                    <td class="px-4 py-3"><?php echo $i['category']; ?> / <?php echo $i['unit']; ?></td>
                    <td class="px-4 py-3 text-right font-bold <?php echo $i['current_stock'] < $i['min_stock'] ? 'text-red-600' : ''; ?>"><?php echo $i['current_stock']; ?> <?php echo $i['unit']; ?></td>
                    <td class="px-4 py-3 text-right"><?php echo $i['min_stock']; ?> <?php echo $i['unit']; ?></td>
                    <td class="px-4 py-3 text-right"><?php echo formatPrice($i['unit_price']); ?>/<?php echo $i['unit']; ?></td>
                    <td class="px-4 py-3 text-right font-bold"><?php echo formatPrice($i['current_stock'] * $i['unit_price']); ?></td>
                    <td class="px-4 py-3"><?php if($i['current_stock'] < $i['min_stock']): ?><span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">⚠️ Stock bas</span><?php elseif($i['is_perishable'] && $i['expiry_days'] < 3): ?><span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">📅 Bientôt périmé</span><?php else: ?><span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">✓ OK</span><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tab Approvisionnement -->
<div id="tab-supply" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-4 shadow">
                <h3 class="font-bold mb-3">📦 Nouvel approvisionnement</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_supply">
                    <div class="mb-2">
                        <select name="ingredient_id" required class="w-full px-3 py-2 border rounded">
                            <option value="">Sélectionner un ingrédient</option>
                            <?php foreach($ingredients as $i): ?>
                            <option value="<?php echo $i['id']; ?>"><?php echo $i['ingredient_name']; ?> (<?php echo $i['category']; ?> - Stock actuel: <?php echo $i['current_stock']; ?> <?php echo $i['unit']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><input type="number" name="quantity" step="0.1" placeholder="Quantité" required class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2"><input type="number" name="unit_price" step="1" placeholder="Prix unitaire (CFA)" required class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2"><input type="date" name="supply_date" required class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2"><input type="date" name="expiry_date" class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2"><textarea name="notes" placeholder="Notes (facture, fournisseur...)" class="w-full px-3 py-2 border rounded"></textarea></div>
                    <button type="submit" class="btn-pizza w-full">Enregistrer l'approvisionnement</button>
                </form>
            </div>
        </div>
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow overflow-x-auto">
                <table class="w-full"><thead class="bg-gray-50"><tr><th class="px-4 py-2">Date</th><th>Ingrédient</th><th class="text-right">Qté</th><th class="text-right">Prix unit.</th><th class="text-right">Total</th><th>Péremption</th></tr></thead><tbody>
                <?php foreach($supplies as $s): ?><tr class="border-b"><td class="px-4 py-2"><?php echo $s['supply_date']; ?></td><td class="px-4 py-2 font-semibold"><?php echo $s['ingredient_name']; ?></td><td class="px-4 py-2 text-right"><?php echo $s['quantity']; ?></td><td class="px-4 py-2 text-right"><?php echo formatPrice($s['unit_price']); ?></td><td class="px-4 py-2 text-right font-bold"><?php echo formatPrice($s['total_amount']); ?></td><td class="px-4 py-2"><?php echo $s['expiry_date'] ?? '-'; ?></td></tr><?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>
</div>

<!-- Tab Sorties -->
<div id="tab-issue" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-4 shadow">
                <h3 class="font-bold mb-3">📤 Sortie de stock</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_issue">
                    <div class="mb-2">
                        <select name="ingredient_id" required class="w-full px-3 py-2 border rounded">
                            <option value="">Sélectionner un ingrédient</option>
                            <?php foreach($ingredients as $i): ?>
                            <option value="<?php echo $i['id']; ?>"><?php echo $i['ingredient_name']; ?> (Stock: <?php echo $i['current_stock']; ?> <?php echo $i['unit']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><input type="number" name="quantity" step="0.1" placeholder="Quantité utilisée" required class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2"><input type="date" name="issue_date" required class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2">
                        <select name="issue_type" class="w-full px-3 py-2 border rounded">
                            <option value="production">🍳 Production (préparation repas)</option>
                            <option value="perte">⚠️ Perte</option>
                            <option value="casse">💔 Casse</option>
                            <option value="don">🎁 Don</option>
                        </select>
                    </div>
                    <div class="mb-2"><input type="text" name="requested_by" placeholder="Demandeur (nom du cuisinier)" class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2"><textarea name="notes" placeholder="Notes (recette, utilisation...)" class="w-full px-3 py-2 border rounded"></textarea></div>
                    <button type="submit" class="btn-pizza w-full">Enregistrer la sortie</button>
                </form>
            </div>
        </div>
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow overflow-x-auto">
                <table class="w-full"><thead class="bg-gray-50"><tr><th>Date</th><th>Ingrédient</th><th class="text-right">Qté</th><th>Type</th><th>Demandeur</th></tr></thead><tbody>
                <?php foreach($issues as $i): ?><tr class="border-b"><td class="px-4 py-2"><?php echo $i['issue_date']; ?></td><td class="px-4 py-2 font-semibold"><?php echo $i['ingredient_name']; ?></td><td class="px-4 py-2 text-right font-bold text-red-600">-<?php echo $i['quantity']; ?></td><td class="px-4 py-2"><?php echo $i['issue_type']; ?></td><td class="px-4 py-2"><?php echo $i['requested_by']; ?></td></tr><?php endforeach; ?>
                </tbody>｜DSML｜
            </div>
        </div>
    </div>
</div>

<!-- Tab Retours -->
<div id="tab-return" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-4 shadow">
                <h3 class="font-bold mb-3">🔄 Retour de stock (fin de journée)</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_return">
                    <div class="mb-2">
                        <select name="ingredient_id" required class="w-full px-3 py-2 border rounded">
                            <option value="">Sélectionner un ingrédient</option>
                            <?php foreach($ingredients as $i): ?>
                            <option value="<?php echo $i['id']; ?>"><?php echo $i['ingredient_name']; ?> (Stock actuel: <?php echo $i['current_stock']; ?> <?php echo $i['unit']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><input type="number" name="quantity" step="0.1" placeholder="Quantité retournée" required class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2"><input type="date" name="return_date" required class="w-full px-3 py-2 border rounded"></div>
                    <div class="mb-2"><textarea name="notes" placeholder="Motif du retour (restant fin de service...)" class="w-full px-3 py-2 border rounded"></textarea></div>
                    <button type="submit" class="btn-pizza w-full">Enregistrer le retour</button>
                </form>
            </div>
        </div>
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow overflow-x-auto">
                <table class="w-full"><thead class="bg-gray-50"><tr><th>Date</th><th>Ingrédient</th><th class="text-right">Qté retournée</th><th>Notes</th></tr></thead><tbody>
                <?php foreach($returns as $r): ?><tr class="border-b"><td class="px-4 py-2"><?php echo $r['return_date']; ?></td><td class="px-4 py-2 font-semibold"><?php echo $r['ingredient_name']; ?></td><td class="px-4 py-2 text-right font-bold text-green-600">+<?php echo $r['quantity']; ?></td><td class="px-4 py-2"><?php echo $r['notes']; ?></td></tr><?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>
</div>

<!-- Tab Nouvel ingrédient avec liste déroulante -->
<div id="tab-ingredient" class="tab-content hidden">
    <div class="bg-white rounded-2xl p-6 shadow max-w-2xl mx-auto">
        <h3 class="font-bold text-xl mb-4">➕ Ajouter un nouvel ingrédient</h3>
        
        <!-- Message important -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-sm text-blue-700">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Conseil :</strong> Si votre ingrédient existe déjà dans la liste, allez dans l'onglet "Approvisionnements" pour ajouter du stock.
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add_ingredient">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nom ingrédient avec liste déroulante pré-définie -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Nom de l'ingrédient *</label>
                    <select name="ingredient_name" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                        <option value="">-- Sélectionner un ingrédient --</option>
                        <optgroup label="🥗 Légumes">
                            <option value="Tomates">🍅 Tomates</option>
                            <option value="Oignons">🧅 Oignons</option>
                            <option value="Poivrons">🫑 Poivrons</option>
                            <option value="Champignons">🍄 Champignons</option>
                            <option value="Courgettes">🥒 Courgettes</option>
                            <option value="Aubergines">🍆 Aubergines</option>
                            <option value="Salade">🥬 Salade</option>
                            <option value="Pommes de terre">🥔 Pommes de terre</option>
                            <option value="Ail">🧄 Ail</option>
                            <option value="Persil">🌿 Persil</option>
                        </optgroup>
                        <optgroup label="🧀 Fromages">
                            <option value="Mozzarella">🧀 Mozzarella</option>
                            <option value="Parmesan">🧀 Parmesan</option>
                            <option value="Cheddar">🧀 Cheddar</option>
                            <option value="Chèvre">🧀 Chèvre</option>
                            <option value="Roquefort">🧀 Roquefort</option>
                        </optgroup>
                        <optgroup label="🥩 Viandes">
                            <option value="Jambon">🍖 Jambon</option>
                            <option value="Pepperoni">🌶️ Pepperoni</option>
                            <option value="Poulet">🍗 Poulet</option>
                            <option value="Bœuf haché">🥩 Bœuf haché</option>
                            <option value="Saucisses">🌭 Saucisses</option>
                            <option value="Bacon">🥓 Bacon</option>
                        </optgroup>
                        <optgroup label="🥫 Sauces">
                            <option value="Sauce tomate">🥫 Sauce tomate</option>
                            <option value="Crème fraîche">🥛 Crème fraîche</option>
                            <option value="Sauce BBQ">🍖 Sauce BBQ</option>
                            <option value="Sauce samouraï">🌶️ Sauce samouraï</option>
                            <option value="Sauce algérienne">🥫 Sauce algérienne</option>
                            <option value="Ketchup">🍅 Ketchup</option>
                            <option value="Mayonnaise">🥚 Mayonnaise</option>
                        </optgroup>
                        <optgroup label="🌾 Farines et pâtes">
                            <option value="Farine type 00">🌾 Farine type 00</option>
                            <option value="Farine complète">🌾 Farine complète</option>
                            <option value="Levure">🧪 Levure</option>
                            <option value="Sel">🧂 Sel</option>
                            <option value="Sucre">🍬 Sucre</option>
                        </optgroup>
                        <optgroup label="🫒 Huiles">
                            <option value="Huile d'olive">🫒 Huile d'olive</option>
                            <option value="Huile de tournesol">🌻 Huile de tournesol</option>
                        </optgroup>
                        <optgroup label="🥤 Boissons">
                            <option value="Coca-Cola">🥤 Coca-Cola</option>
                            <option value="Fanta">🍊 Fanta</option>
                            <option value="Sprite">🥤 Sprite</option>
                            <option value="Jus de bissap">🌺 Jus de bissap</option>
                            <option value="Jus de bouye">🍊 Jus de bouye</option>
                            <option value="Eau minérale">💧 Eau minérale</option>
                        </optgroup>
                        <optgroup label="📦 Autres">
                            <option value="Emballages pizza">📦 Emballages pizza</option>
                            <option value="Serviettes">🧻 Serviettes</option>
                            <option value="Gants jetables">🧤 Gants jetables</option>
                        </optgroup>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Si votre ingrédient n'est pas dans la liste, sélectionnez "Autre" et complétez le champ ci-dessous</p>
                </div>
                
                <!-- Champ pour saisie libre si non trouvé -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Ou saisir un nom personnalisé</label>
                    <input type="text" name="ingredient_name_custom" id="ingredient_name_custom" placeholder="Ex: Piment antillais, Sauce spéciale..." class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Catégorie *</label>
                    <select name="category" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="legume">🥗 Légume</option>
                        <option value="fromage">🧀 Fromage</option>
                        <option value="viande">🥩 Viande</option>
                        <option value="sauce">🥫 Sauce</option>
                        <option value="farine">🌾 Farine</option>
                        <option value="huile">🫒 Huile</option>
                        <option value="boisson">🥤 Boisson</option>
                        <option value="emballage">📦 Emballage</option>
                        <option value="autre">📦 Autre</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Unité *</label>
                    <select name="unit" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="kg">Kilogramme (kg)</option>
                        <option value="L">Litre (L)</option>
                        <option value="g">Gramme (g)</option>
                        <option value="unité">Unité (pièce)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Prix unitaire (CFA) *</label>
                    <input type="number" name="unit_price" step="1" required placeholder="Ex: 1500" class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Stock initial *</label>
                    <input type="number" name="current_stock" step="0.1" required placeholder="Ex: 20" class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Stock minimum (alerte) *</label>
                    <input type="number" name="min_stock" step="0.1" value="5" required placeholder="Ex: 5" class="w-full px-3 py-2 border rounded-lg">
                    <p class="text-xs text-gray-500">En dessous de ce seuil, vous serez alerté</p>
                </div>
                
                <div class="col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_perishable" value="1" checked class="w-4 h-4"> 
                        <span class="text-sm">🍃 Produit périssable (se détériore avec le temps)</span>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Jours avant péremption</label>
                    <input type="number" name="expiry_days" value="7" class="w-full px-3 py-2 border rounded-lg">
                    <p class="text-xs text-gray-500">Pour les produits périssables (ex: 7 jours pour les tomates)</p>
                </div>
            </div>
            
            <button type="submit" class="btn-pizza w-full mt-6 py-3">➕ Ajouter l'ingrédient</button>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
    document.getElementById(`tab-${tabName}`).classList.remove('hidden');
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active', 'text-red-600', 'border-b-2', 'border-red-600'));
    event.target.classList.add('active', 'text-red-600', 'border-b-2', 'border-red-600');
}

// Auto-remplissage si on sélectionne un ingrédient dans la liste
document.querySelector('select[name="ingredient_name"]')?.addEventListener('change', function() {
    if(this.value && this.value !== '') {
        // Optionnel: pré-remplir la catégorie selon l'ingrédient
        const ingredient = this.value;
        if(['Tomates', 'Oignons', 'Poivrons', 'Champignons', 'Courgettes', 'Aubergines', 'Salade', 'Pommes de terre', 'Ail', 'Persil'].includes(ingredient)) {
            document.querySelector('select[name="category"]').value = 'legume';
        } else if(['Mozzarella', 'Parmesan', 'Cheddar', 'Chèvre', 'Roquefort'].includes(ingredient)) {
            document.querySelector('select[name="category"]').value = 'fromage';
        } else if(['Jambon', 'Pepperoni', 'Poulet', 'Bœuf haché', 'Saucisses', 'Bacon'].includes(ingredient)) {
            document.querySelector('select[name="category"]').value = 'viande';
        }
    }
});
</script>

<?php include 'templates/footer.php'; ?>
