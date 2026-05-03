<?php
require_once '../../config/config.php';
session_start();

if (!isset($_SESSION['cashier_id'])) {
    header('Location: /cashier_login.php');
    exit;
}

$db = getDB();
$cashier_name = $_SESSION['cashier_name'] ?? 'Caissier';

// Récupérer les statistiques
$critical = $db->query("SELECT COUNT(*) FROM ingredients WHERE current_stock <= min_stock/2")->fetchColumn();
$low = $db->query("SELECT COUNT(*) FROM ingredients WHERE current_stock <= min_stock AND current_stock > min_stock/2")->fetchColumn();
$alerts = $db->query("SELECT * FROM ingredients WHERE current_stock <= min_stock ORDER BY (current_stock/min_stock) ASC")->fetchAll();
$ingredients = $db->query("SELECT * FROM ingredients ORDER BY ingredient_name")->fetchAll();
$stock_value = $db->query("SELECT SUM(current_stock * unit_price) FROM ingredients")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approvisionnements - Business Suite Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --accent: #e74c3c; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        .glass-card:hover { transform: translateY(-5px); }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 30px 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: bold; margin: 0.5rem 0; }
        .btn-luxury {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
        }
        .btn-luxury:hover {
            transform: scale(1.05);
            color: white;
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .badge-critical { background: #e74c3c; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; }
        .badge-warning { background: #f39c12; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; }
        .badge-success { background: #27ae60; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; }
    </style>
</head>
<body>
    <!-- Header Style Business Suite -->
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="text-white mb-0">
                        <i class="fas fa-truck me-2"></i>Gestion des Approvisionnements
                    </h1>
                    <p class="text-white-50 mt-2">Gestion des stocks et ingrédients</p>
                </div>
                <div>
                    <span class="text-white me-3">
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($cashier_name) ?>
                    </span>
                    <a href="/pos.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i>Retour POS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Cartes Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value text-danger"><?= $critical ?></div>
                    <div class="stat-label">Stock Critique</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value text-warning"><?= $low ?></div>
                    <div class="stat-label">Stock Faible</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon text-success">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-value text-success"><?= count($ingredients) ?></div>
                    <div class="stat-label">Total Ingrédients</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon text-info">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value text-info"><?= number_format($stock_value, 0) ?> CFA</div>
                    <div class="stat-label">Valeur du Stock</div>
                </div>
            </div>
        </div>

        <!-- Alertes Stock -->
        <?php if(count($alerts) > 0): ?>
        <div class="glass-card mb-4 p-4">
            <h3 class="mb-3">
                <i class="fas fa-bell text-warning me-2"></i>Alertes Stock
            </h3>
            <div class="row">
                <?php foreach($alerts as $alert): ?>
                <div class="col-md-6 mb-3">
                    <div class="alert <?= $alert['current_stock'] <= $alert['min_stock']/2 ? 'alert-danger' : 'alert-warning' ?> mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="fas fa-carrot me-1"></i><?= htmlspecialchars($alert['ingredient_name']) ?></strong><br>
                                <small>Stock: <?= $alert['current_stock'] ?> <?= $alert['unit'] ?> | Minimum: <?= $alert['min_stock'] ?> <?= $alert['unit'] ?></small>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="addStock(<?= $alert['id'] ?>, '<?= addslashes($alert['ingredient_name']) ?>')">
                                <i class="fas fa-plus"></i> Approvisionner
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste des Ingrédients -->
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">
                    <i class="fas fa-carrot me-2 text-success"></i>Inventaire des Ingrédients
                </h3>
                <button class="btn-luxury" onclick="showAddIngredientModal()">
                    <i class="fas fa-plus me-1"></i>Ajouter un ingrédient
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Ingrédient</th>
                            <th>Code</th>
                            <th>Unité</th>
                            <th>Stock actuel</th>
                            <th>Stock min</th>
                            <th>Prix unitaire</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ingredients as $ing): 
                            $status = '';
                            $statusClass = '';
                            if($ing['current_stock'] <= $ing['min_stock']/2) {
                                $status = 'CRITIQUE';
                                $statusClass = 'badge-critical';
                            } elseif($ing['current_stock'] <= $ing['min_stock']) {
                                $status = 'Faible';
                                $statusClass = 'badge-warning';
                            } else {
                                $status = 'OK';
                                $statusClass = 'badge-success';
                            }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($ing['ingredient_name']) ?></strong></td>
                            <td><?= htmlspecialchars($ing['ingredient_code'] ?? '-') ?></td>
                            <td><?= $ing['unit'] ?></td>
                            <td class="<?= $ing['current_stock'] <= $ing['min_stock'] ? 'text-danger fw-bold' : '' ?>">
                                <?= number_format($ing['current_stock'], 2) ?> <?= $ing['unit'] ?>
                            </td>
                            <td><?= number_format($ing['min_stock'], 2) ?> <?= $ing['unit'] ?></td>
                            <td><?= number_format($ing['unit_price'], 0) ?> CFA</td>
                            <td><span class="<?= $statusClass ?>"><?= $status ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-success me-1" onclick="addStock(<?= $ing['id'] ?>, '<?= addslashes($ing['ingredient_name']) ?>')" title="Ajouter du stock">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="removeStock(<?= $ing['id'] ?>, '<?= addslashes($ing['ingredient_name']) ?>')" title="Retirer du stock">
                                    <i class="fas fa-minus-circle"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Ingrédient -->
    <div class="modal fade" id="addIngredientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-carrot me-2"></i>Ajouter un ingrédient</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="ingredientForm">
                        <div class="mb-3">
                            <label>Nom de l'ingrédient</label>
                            <input type="text" class="form-control" id="ingredient_name" required>
                        </div>
                        <div class="mb-3">
                            <label>Code (optionnel)</label>
                            <input type="text" class="form-control" id="ingredient_code" placeholder="Ex: FAR-001">
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
                            <label>Stock minimum</label>
                            <input type="number" class="form-control" id="min_stock" value="10" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label>Prix unitaire (CFA)</label>
                            <input type="number" class="form-control" id="unit_price" value="0" step="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn-luxury" onclick="saveIngredient()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-4 text-white-50">
        <p>&copy; 2026 Business Suite Pro - Module Approvisionnements</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function addStock(id, name) {
        Swal.fire({
            title: 'Approvisionnement',
            text: `Ajouter du stock pour ${name}`,
            input: 'number',
            inputLabel: 'Quantité à ajouter',
            inputPlaceholder: 'Saisir la quantité',
            showCancelButton: true,
            confirmButtonText: 'Ajouter',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed && result.value > 0) {
                fetch(`ajax.php?action=add_stock&id=${id}&qty=${result.value}`)
                    .then(r => r.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire('Succès', 'Stock ajouté avec succès', 'success');
                            location.reload();
                        } else {
                            Swal.fire('Erreur', 'Impossible d\'ajouter le stock', 'error');
                        }
                    });
            }
        });
    }

    function removeStock(id, name) {
        Swal.fire({
            title: 'Retrait de stock',
            text: `Retirer du stock pour ${name}`,
            input: 'number',
            inputLabel: 'Quantité à retirer',
            inputPlaceholder: 'Saisir la quantité',
            showCancelButton: true,
            confirmButtonText: 'Retirer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#e74c3c'
        }).then((result) => {
            if (result.isConfirmed && result.value > 0) {
                fetch(`ajax.php?action=remove_stock&id=${id}&qty=${result.value}`)
                    .then(r => r.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire('Succès', 'Stock retiré avec succès', 'success');
                            location.reload();
                        } else {
                            Swal.fire('Erreur', data.message || 'Stock insuffisant', 'error');
                        }
                    });
            }
        });
    }

    function showAddIngredientModal() {
        document.getElementById('ingredientForm').reset();
        new bootstrap.Modal(document.getElementById('addIngredientModal')).show();
    }

    function saveIngredient() {
        const formData = new FormData();
        formData.append('action', 'save_ingredient');
        formData.append('ingredient_name', document.getElementById('ingredient_name').value);
        formData.append('ingredient_code', document.getElementById('ingredient_code').value);
        formData.append('unit', document.getElementById('unit').value);
        formData.append('min_stock', document.getElementById('min_stock').value);
        formData.append('unit_price', document.getElementById('unit_price').value);
        formData.append('current_stock', 0);

        fetch('ajax.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addIngredientModal')).hide();
                    Swal.fire('Succès', 'Ingrédient ajouté avec succès', 'success');
                    location.reload();
                } else {
                    Swal.fire('Erreur', data.message || 'Impossible d\'ajouter', 'error');
                }
            });
    }
    </script>
</body>
</html>
