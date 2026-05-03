<?php include '../../includes/header.php'; ?>
<h2 class="mb-4">État de Performance Magasin</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card border-danger mb-4 shadow-sm">
            <div class="card-header bg-danger text-white">Alertes Stock / Ruptures</div>
            <table class="table mb-0">
                <thead><tr><th>Pièce</th><th>Stock</th><th>Seuil</th></tr></thead>
                <tbody>
                <?php
                require_once '../../includes/classes/Database.php';
                $db = (new Database())->getConnection();
                $query = $db->query("SELECT * FROM pieces_detachees WHERE stock_actuel <= seuil_alerte");
                while($p = $query->fetch()) {
                    echo "<tr class='table-warning'><td>{$p['libelle']}</td><td>{$p['stock_actuel']}</td><td>{$p['seuil_alerte']}</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-primary shadow-sm">
            <div class="card-header bg-primary text-white">Top 5 Pièces les plus vendues</div>
            <div class="card-body">
                <p class="text-muted small text-center">Analyse basée sur les mouvements de stock récents.</p>
            </div>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
