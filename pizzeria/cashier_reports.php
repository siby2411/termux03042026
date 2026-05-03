<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['cashier_id'])) {
    header('Location: cashier_login.php');
    exit;
}

$db = getDB();
$cashier_id = $_SESSION['cashier_id'];
$cashier_name = $_SESSION['cashier_name'];
$period = $_GET['period'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

switch($period) {
    case 'day': $where = "DATE(o.order_date) = CURDATE()"; $title = "Mes ventes du jour - " . date('d/m/Y'); break;
    case 'month': $where = "MONTH(o.order_date) = $month AND YEAR(o.order_date) = $year"; $title = "Mes ventes du mois - " . date('F Y', mktime(0,0,0,$month,1,$year)); break;
    case 'year': $where = "YEAR(o.order_date) = $year"; $title = "Mes ventes de l'année - $year"; break;
    default: $where = "DATE(o.order_date) = CURDATE()"; $title = "Mes ventes du jour";
}

$stats_sql = "SELECT COUNT(*) as total_orders, SUM(o.grand_total) as total_sales, AVG(o.grand_total) as avg_order FROM orders o WHERE o.cashier_id = ? AND $where AND o.order_status != 'cancelled' AND o.payment_status = 'paid'";
$stmt = $db->prepare($stats_sql);
$stmt->execute([$cashier_id]);
$stats = $stmt->fetch();

$commission_sql = "SELECT SUM(commission_amount) as total_commission FROM cashier_commissions WHERE cashier_id = ? AND $where";
$stmt = $db->prepare($commission_sql);
$stmt->execute([$cashier_id]);
$commission = $stmt->fetchColumn() ?: 0;

$orders_sql = "SELECT o.id, o.order_number, o.order_date, o.order_type, o.payment_method, o.grand_total, o.order_status, COUNT(oi.id) as items_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE o.cashier_id = ? AND $where AND o.order_status != 'cancelled' GROUP BY o.id ORDER BY o.order_date DESC";
$stmt = $db->prepare($orders_sql);
$stmt->execute([$cashier_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Rapports - Pizzeria</title>
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
            <a class="navbar-brand" href="pos.php"><i class="fas fa-pizza-slice"></i> Pizzeria POS</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white"><i class="fas fa-user"></i> <?= htmlspecialchars($cashier_name) ?></span>
                <a class="nav-link" href="pos.php"><i class="fas fa-cash-register"></i> POS</a>
                <a class="nav-link active" href="cashier_reports.php"><i class="fas fa-chart-line"></i> Mes ventes</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4"><div class="col"><h2><i class="fas fa-chart-line"></i> <?= $title ?></h2></div></div>

        <div class="stat-card mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="day" <?= $period == 'day' ? 'selected' : '' ?>>Aujourd'hui</option>
                        <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>Ce mois</option>
                        <option value="year" <?= $period == 'year' ? 'selected' : '' ?>>Cette année</option>
                    </select>
                </div>
                <?php if($period == 'month'): ?>
                <div class="col-md-2"><select name="month" class="form-select" onchange="this.form.submit()"><?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select></div>
                <div class="col-md-2"><select name="year" class="form-select" onchange="this.form.submit()"><?php for($y=date('Y');$y>=date('Y')-2;$y--): ?><option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select></div>
                <?php elseif($period == 'year'): ?>
                <div class="col-md-2"><select name="year" class="form-select" onchange="this.form.submit()"><?php for($y=date('Y');$y>=date('Y')-2;$y--): ?><option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select></div>
                <?php endif; ?>
            </form>
        </div>

        <div class="row mb-4">
            <div class="col-md-4"><div class="stat-card text-center"><div class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></div><div class="stat-label">Commandes</div></div></div>
            <div class="col-md-4"><div class="stat-card text-center"><div class="stat-value"><?= number_format($stats['total_sales'] ?? 0) ?> FCFA</div><div class="stat-label">Chiffre d'affaires</div></div></div>
            <div class="col-md-4"><div class="stat-card text-center"><div class="stat-value"><?= number_format($commission) ?> FCFA</div><div class="stat-label">Commission</div></div></div>
        </div>

        <div class="stat-card">
            <h5>Détail des commandes</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>N° Commande</th><th>Date</th><th>Type</th><th>Paiement</th><th>Total</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php if(empty($orders)): ?><tr><td colspan="6" class="text-center">Aucune commande</td></tr>
                        <?php else: foreach($orders as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['order_number']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><?= $order['order_type'] ?></td>
                            <td><?= $order['payment_method'] ?></td>
                            <td><strong><?= number_format($order['grand_total'], 0) ?> FCFA</strong></td>
                            <td><span class="badge bg-success"><?= $order['order_status'] ?></span></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
