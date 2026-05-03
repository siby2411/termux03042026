<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['cashier_id'])) {
    header('Location: cashier_login.php');
    exit;
}

$db = getDB();
$period = $_GET['period'] ?? 'day';
$cashier_filter = $_GET['cashier_id'] ?? 'all';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

$cashiers = $db->query("SELECT id, full_name, username FROM cashiers WHERE is_active = 1 ORDER BY full_name")->fetchAll();

switch($period) {
    case 'day': $where = "DATE(o.order_date) = CURDATE()"; $title = "Rapport du Jour - " . date('d/m/Y'); break;
    case 'month': $where = "MONTH(o.order_date) = $month AND YEAR(o.order_date) = $year"; $title = "Rapport du Mois - " . date('F Y', mktime(0,0,0,$month,1,$year)); break;
    case 'year': $where = "YEAR(o.order_date) = $year"; $title = "Rapport Annuel - $year"; break;
    default: $where = "DATE(o.order_date) = CURDATE()"; $title = "Rapport du Jour";
}

if ($cashier_filter !== 'all') { $where .= " AND o.cashier_id = " . intval($cashier_filter); }

$stats_sql = "SELECT COUNT(*) as total_orders, SUM(o.grand_total) as total_revenue, AVG(o.grand_total) as avg_order FROM orders o WHERE $where AND o.order_status != 'cancelled' AND o.payment_status = 'paid'";
$stats = $db->query($stats_sql)->fetch();

$orders_sql = "SELECT o.id, o.order_number, o.order_date, o.order_type, o.payment_method, o.grand_total, o.order_status, c.full_name as cashier_name FROM orders o LEFT JOIN cashiers c ON o.cashier_id = c.id WHERE $where AND o.order_status != 'cancelled' ORDER BY o.order_date DESC";
$orders = $db->query($orders_sql)->fetchAll();

$cashier_stats_sql = "SELECT c.id, c.full_name, COUNT(o.id) as order_count, SUM(o.grand_total) as total_sales FROM cashiers c LEFT JOIN orders o ON o.cashier_id = c.id AND ($where) WHERE c.is_active = 1 GROUP BY c.id ORDER BY total_sales DESC";
$cashier_stats = $db->query($cashier_stats_sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports Financiers - Pizzeria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .stat-card { background: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-value { font-size: 2rem; font-weight: bold; color: #e74c3c; }
        .stat-label { color: #7f8c8d; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="pos.php"><i class="fas fa-pizza-slice"></i> Pizzeria Manager</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="pos.php"><i class="fas fa-cash-register"></i> POS</a>
                <a class="nav-link active" href="financial_reports.php"><i class="fas fa-chart-line"></i> Rapports</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-chart-line"></i> <?= $title ?></h2>

        <div class="stat-card mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="day" <?= $period=='day'?'selected':'' ?>>Journalier</option>
                        <option value="month" <?= $period=='month'?'selected':'' ?>>Mensuel</option>
                        <option value="year" <?= $period=='year'?'selected':'' ?>>Annuel</option>
                    </select>
                </div>
                <?php if($period=='month'): ?>
                <div class="col-md-2"><select name="month" class="form-select" onchange="this.form.submit()"><?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select></div>
                <div class="col-md-2"><select name="year" class="form-select" onchange="this.form.submit()"><?php for($y=date('Y');$y>=date('Y')-2;$y--): ?><option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select></div>
                <?php elseif($period=='year'): ?>
                <div class="col-md-2"><select name="year" class="form-select" onchange="this.form.submit()"><?php for($y=date('Y');$y>=date('Y')-2;$y--): ?><option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select></div>
                <?php endif; ?>
                <div class="col-md-3"><select name="cashier_id" class="form-select" onchange="this.form.submit()"><option value="all">Tous les caissiers</option><?php foreach($cashiers as $c): ?><option value="<?= $c['id'] ?>" <?= $cashier_filter==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['full_name']?:$c['username']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrer</button></div>
            </form>
        </div>

        <div class="row mb-4">
            <div class="col-md-4"><div class="stat-card text-center"><div class="stat-value"><?= number_format($stats['total_orders']??0) ?></div><div class="stat-label">Commandes</div></div></div>
            <div class="col-md-4"><div class="stat-card text-center"><div class="stat-value"><?= number_format($stats['total_revenue']??0) ?> FCFA</div><div class="stat-label">CA Total</div></div></div>
            <div class="col-md-4"><div class="stat-card text-center"><div class="stat-value"><?= number_format($stats['avg_order']??0) ?> FCFA</div><div class="stat-label">Panier moyen</div></div></div>
        </div>

        <?php if(!empty($cashier_stats)): ?>
        <div class="stat-card mb-4">
            <h5><i class="fas fa-users"></i> Performance par caissier</h5>
            <table class="table table-hover"><thead><tr><th>Caissier</th><th>Commandes</th><th>CA</th><th>Panier moyen</th></tr></thead><tbody>
            <?php foreach($cashier_stats as $cs): if($cs['order_count']>0): ?>
            <tr><td><?= htmlspecialchars($cs['full_name']) ?></td><td><?= $cs['order_count'] ?></td><td><?= number_format($cs['total_sales'],0) ?> FCFA</td><td><?= number_format($cs['total_sales']/$cs['order_count'],0) ?> FCFA</td></tr>
            <?php endif; endforeach; ?>
            </tbody></table>
        </div>
        <?php endif; ?>

        <div class="stat-card">
            <h5>Détail des commandes</h5>
            <div class="table-responsive">
                <table class="table table-hover"><thead><tr><th>N°</th><th>Date</th><th>Caissier</th><th>Type</th><th>Paiement</th><th>Total</th></tr></thead><tbody>
                <?php if(empty($orders)): ?><tr><td colspan="6" class="text-center">Aucune commande</td></tr>
                <?php else: foreach($orders as $order): ?>
                <tr><td>#<?= htmlspecialchars($order['order_number']) ?></td><td><?= date('d/m/Y H:i',strtotime($order['order_date'])) ?></td><td><?= htmlspecialchars($order['cashier_name']??'N/A') ?></td><td><?= $order['order_type'] ?></td><td><?= $order['payment_method'] ?></td><td><strong><?= number_format($order['grand_total'],0) ?> FCFA</strong></td></tr>
                <?php endforeach; endif; ?>
                </tbody></table>
            </div>
        </div>
    </div>
</body>
</html>
