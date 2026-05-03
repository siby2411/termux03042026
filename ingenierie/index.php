<?php
require_once 'db_connect.php';

// 1. Récupération des infos du Trader (Mohamet SIBY)
try {
    $trader = $pdo->query("SELECT * FROM traders WHERE id = 1 LIMIT 1")->fetch();
    if (!$trader) {
        die("Erreur : Aucun compte trader trouvé. Veuillez initialiser la table 'traders'.");
    }

    // 2. Requête avancée : Actifs + Quantité possédée (Calcul dynamique Buy - Sell)
    $sql_assets = "SELECT a.*,
            (SELECT IFNULL(SUM(o.quantity), 0) FROM orders o WHERE o.trader_id = 1 AND o.asset_id = a.id AND o.type = 'BUY') -
            (SELECT IFNULL(SUM(o.quantity), 0) FROM orders o WHERE o.trader_id = 1 AND o.asset_id = a.id AND o.type = 'SELL') as qty_owned
            FROM assets a";
    $assets = $pdo->query($sql_assets)->fetchAll();

    // 3. Calcul de la valeur totale du portefeuille
    $portfolio_value = 0;
    foreach($assets as $as) { 
        $portfolio_value += ($as['qty_owned'] * $as['current_price']); 
    }

    // 4. Historique des 8 dernières transactions (Utilise maintenant 'order_date')
    $orders = $pdo->query("SELECT o.*, a.symbol FROM orders o JOIN assets a ON o.asset_id = a.id ORDER BY o.order_date DESC LIMIT 8")->fetchAll();

} catch (PDOException $e) {
    die("❌ Erreur Critique de Base de Données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA TRADING PRO | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0b0e11; color: #eaecef; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .trading-card { background: #1e2329; border: 1px solid #30363d; border-radius: 12px; padding: 25px; height: 100%; }
        .price-up { color: #02c076; font-weight: bold; }
        .text-warning { color: #f3ba2f !important; }
        .balance-box { background: #2b3139; padding: 15px; border-radius: 10px; border-top: 4px solid #f3ba2f; transition: transform 0.2s; }
        .balance-box:hover { transform: translateY(-5px); }
        .table-dark { --bs-table-bg: #1e2329; }
        .btn-success { background-color: #02c076; border: none; }
        .btn-danger { background-color: #cf304a; border: none; }
    </style>
</head>
<body class="p-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><span class="text-warning">Ω</span> OMEGA TRADING <span class="badge bg-secondary fs-6">PRO 2026</span></h2>
            <div>
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success py-1 px-3 mb-0"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger py-1 px-3 mb-0"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="balance-box">
                    <small class="text-secondary fw-bold">SOLDE CASH DISPONIBLE</small>
                    <h2 class="text-warning"><?php echo number_format($trader['balance'], 2, ',', ' '); ?> $</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="balance-box" style="border-top-color: #02c076;">
                    <small class="text-secondary fw-bold">VALEUR DU PORTEFEUILLE</small>
                    <h2><?php echo number_format($portfolio_value, 2, ',', ' '); ?> $</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="balance-box" style="border-top-color: #3498db;">
                    <small class="text-secondary fw-bold">CAPITAL NET TOTAL</small>
                    <h2 class="text-info"><?php echo number_format($trader['balance'] + $portfolio_value, 2, ',', ' '); ?> $</h2>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="trading-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Marché Spot</h4>
                        <a href="simulateur.php" class="btn btn-outline-warning btn-sm">Actualiser les prix 📈</a>
                    </div>
                    <table class="table table-dark table-hover mt-3 align-middle">
                        <thead>
                            <tr class="text-secondary">
                                <th>Actif</th>
                                <th>Prix Actuel</th>
                                <th>Quantité Détenue</th>
                                <th>Opération</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($assets as $asset): ?>
                            <tr>
                                <td><span class="badge bg-dark border border-secondary p-2"><?php echo $asset['symbol']; ?></span></td>
                                <td class="price-up"><?php echo number_format($asset['current_price'], 2); ?> $</td>
                                <td class="text-info fw-bold"><?php echo $asset['qty_owned']; ?></td>
                                <form method="POST">
                                    <td>
                                        <input type="number" name="quantity" step="0.01" value="0.1" min="0.01" class="form-control form-control-sm bg-dark text-white border-secondary" style="width: 80px;">
                                        <input type="hidden" name="asset_id" value="<?php echo $asset['id']; ?>">
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="submit" formaction="acheter.php" class="btn btn-sm btn-success px-3">ACHETER</button>
                                            <button type="submit" formaction="vendre.php" class="btn btn-sm btn-danger px-3">VENDRE</button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="trading-card">
                    <h4>Flux Transactions OMEGA</h4>
                    <hr class="border-secondary">
                    <div class="list-group list-group-flush">
                        <?php if(empty($orders)): ?>
                            <p class="text-secondary text-center my-4">Aucune transaction récente.</p>
                        <?php endif; ?>
                        <?php foreach($orders as $order): ?>
                        <div class="list-group-item bg-transparent text-white border-secondary px-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge <?php echo $order['type'] == 'BUY' ? 'bg-success' : 'bg-danger'; ?>-subtle <?php echo $order['type'] == 'BUY' ? 'text-success' : 'text-danger'; ?> p-2">
                                    <?php echo $order['type'] == 'BUY' ? 'ACHAT' : 'VENTE'; ?>
                                </span>
                                <span class="fw-bold"><?php echo number_format($order['price_at_order'], 2); ?> $</span>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-secondary"><?php echo $order['quantity']; ?> <?php echo $order['symbol']; ?></small>
                                <small class="text-muted"><?php echo date('H:i:s', strtotime($order['order_date'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
