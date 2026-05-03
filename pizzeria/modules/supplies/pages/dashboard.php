<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-truck"></i> Gestion des Approvisionnements</h1>
        <div>
            <a href="?page=orders&action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle commande</a>
            <a href="?page=ingredients&action=new" class="btn btn-success"><i class="fas fa-carrot"></i> Nouvel ingrédient</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Stock Critique</h5>
                    <h2 class="mb-0" id="critical_count">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Stock Faible</h5>
                    <h2 class="mb-0" id="low_count">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Commandes en cours</h5>
                    <h2 class="mb-0" id="pending_orders">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Valeur du Stock</h5>
                    <h2 class="mb-0" id="stock_value">-</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-triangle"></i> Alertes Stock</h5>
                </div>
                <div class="card-body" id="alerts_list" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-muted">Chargement...</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Mouvements récents</h5>
                </div>
                <div class="card-body" id="movements_list" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-muted">Chargement...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadDashboard() {
    fetch('ajax.php?action=dashboard_stats')
        .then(r => r.json())
        .then(data => {
            document.getElementById('critical_count').innerText = data.critical || 0;
            document.getElementById('low_count').innerText = data.low || 0;
            document.getElementById('pending_orders').innerText = data.pending_orders || 0;
            document.getElementById('stock_value').innerText = (data.stock_value || 0).toLocaleString() + ' CFA';
        });

    fetch('ajax.php?action=alerts')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('alerts_list');
            if (data.length === 0) {
                container.innerHTML = '<p class="text-success">✓ Tous les stocks sont suffisants</p>';
            } else {
                container.innerHTML = data.map(item => `
                    <div class="alert alert-${item.level === 'critical' ? 'danger' : 'warning'} mb-2">
                        <strong>${item.ingredient_name}</strong><br>
                        Stock: ${item.current_stock} ${item.unit} | Minimum: ${item.min_stock} ${item.unit}
                        <a href="?page=orders&ingredient=${item.id}" class="btn btn-sm btn-primary float-end">Commander</a>
                    </div>
                `).join('');
            }
        });

    fetch('ajax.php?action=recent_movements')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('movements_list');
            if (data.length === 0) {
                container.innerHTML = '<p class="text-muted">Aucun mouvement récent</p>';
            } else {
                container.innerHTML = '<table class="table table-sm">' + data.map(item => `
                    <tr>
                        <td>${item.date}</td>
                        <td>${item.ingredient_name}</td>
                        <td class="${item.type === 'in' ? 'text-success' : 'text-danger'}">${item.type === 'in' ? '+' : '-'} ${item.quantity} ${item.unit}</td>
                    </tr>
                `).join('') + '</table>';
            }
        });
}

loadDashboard();
setInterval(loadDashboard, 30000);
</script>
