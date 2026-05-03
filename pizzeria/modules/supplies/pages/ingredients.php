<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-carrot"></i> Gestion des Ingrédients</h1>
        <button class="btn btn-primary" onclick="showIngredientModal()"><i class="fas fa-plus"></i> Ajouter un ingrédient</button>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="ingredientsTable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Ingrédient</th>
                        <th>Unité</th>
                        <th>Stock actuel</th>
                        <th>Stock min</th>
                        <th>Prix unitaire</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ingredientsList"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ingredient -->
<div class="modal fade" id="ingredientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ingrédient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ingredientForm">
                    <input type="hidden" id="ingredient_id" name="id">
                    <div class="mb-3">
                        <label>Nom de l'ingrédient</label>
                        <input type="text" class="form-control" id="ingredient_name" required>
                    </div>
                    <div class="mb-3">
                        <label>Code</label>
                        <input type="text" class="form-control" id="ingredient_code">
                    </div>
                    <div class="mb-3">
                        <label>Unité</label>
                        <select class="form-control" id="unit">
                            <option value="kg">Kilogramme (kg)</option>
                            <option value="g">Gramme (g)</option>
                            <option value="L">Litre (L)</option>
                            <option value="ml">Millilitre (ml)</option>
                            <option value="piece">Pièce</option>
                            <option value="sac">Sac</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Stock initial</label>
                        <input type="number" class="form-control" id="current_stock" value="0">
                    </div>
                    <div class="mb-3">
                        <label>Stock minimum</label>
                        <input type="number" class="form-control" id="min_stock" value="10">
                    </div>
                    <div class="mb-3">
                        <label>Prix unitaire (CFA)</label>
                        <input type="number" class="form-control" id="unit_price" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="saveIngredient()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadIngredients() {
    fetch('ajax.php?action=list_ingredients')
        .then(r => r.json())
        .then(data => {
            document.getElementById('ingredientsList').innerHTML = data.map(item => `
                <tr class="${item.current_stock <= item.min_stock ? 'table-warning' : ''}">
                    <td>${item.ingredient_code || '-'}</td>
                    <td><strong>${item.ingredient_name}</strong></td>
                    <td>${item.unit}</td>
                    <td class="${item.current_stock <= item.min_stock ? 'text-danger fw-bold' : ''}">${item.current_stock} ${item.unit}</td>
                    <td>${item.min_stock} ${item.unit}</td>
                    <td>${parseFloat(item.unit_price).toLocaleString()} CFA</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editIngredient(${item.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-success" onclick="addStock(${item.id}, '${item.ingredient_name}')"><i class="fas fa-plus-circle"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteIngredient(${item.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        });
}

function showIngredientModal() {
    document.getElementById('ingredient_id').value = '';
    document.getElementById('ingredientForm').reset();
    new bootstrap.Modal(document.getElementById('ingredientModal')).show();
}

function editIngredient(id) {
    fetch(`ajax.php?action=get_ingredient&id=${id}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('ingredient_id').value = data.id;
            document.getElementById('ingredient_name').value = data.ingredient_name;
            document.getElementById('ingredient_code').value = data.ingredient_code || '';
            document.getElementById('unit').value = data.unit;
            document.getElementById('current_stock').value = data.current_stock;
            document.getElementById('min_stock').value = data.min_stock;
            document.getElementById('unit_price').value = data.unit_price;
            new bootstrap.Modal(document.getElementById('ingredientModal')).show();
        });
}

function saveIngredient() {
    const formData = new FormData();
    formData.append('action', 'save_ingredient');
    formData.append('id', document.getElementById('ingredient_id').value);
    formData.append('ingredient_name', document.getElementById('ingredient_name').value);
    formData.append('ingredient_code', document.getElementById('ingredient_code').value);
    formData.append('unit', document.getElementById('unit').value);
    formData.append('current_stock', document.getElementById('current_stock').value);
    formData.append('min_stock', document.getElementById('min_stock').value);
    formData.append('unit_price', document.getElementById('unit_price').value);

    fetch('ajax.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('ingredientModal')).hide();
                loadIngredients();
                showAlert('success', data.message);
            } else {
                showAlert('danger', data.message);
            }
        });
}

function addStock(id, name) {
    const qty = prompt(`Ajouter du stock pour ${name}:`, '0');
    if (qty && parseFloat(qty) > 0) {
        fetch(`ajax.php?action=add_stock&id=${id}&qty=${qty}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    loadIngredients();
                    showAlert('success', `${qty} ajouté au stock`);
                }
            });
    }
}

function deleteIngredient(id) {
    if (confirm('Supprimer cet ingrédient ?')) {
        fetch(`ajax.php?action=delete_ingredient&id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    loadIngredients();
                    showAlert('success', 'Ingrédient supprimé');
                }
            });
    }
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}

loadIngredients();
</script>
