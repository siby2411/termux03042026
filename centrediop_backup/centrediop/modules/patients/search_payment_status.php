<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$status = $_GET['status'] ?? 'completed';
$serviceId = $_GET['service_id'] ?? 1;

$stmt = $pdo->prepare("
    SELECT p.*, py.status AS payment_status, s.name AS service_name
    FROM patients p
    JOIN payments py ON p.id = py.patient_id
    JOIN services s ON py.service_id = s.id
    WHERE py.status = ? AND s.id = ?
");
$stmt->execute([$status, $serviceId]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche par état de paiement</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Recherche par état de paiement</h1>
    </header>
    <main>
        <form method="get">
            <div class="form-group">
                <label for="status">État de paiement :</label>
                <select id="status" name="status">
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Payé</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>En attente</option>
                    <option value="refunded" <?= $status === 'refunded' ? 'selected' : '' ?>>Remboursé</option>
                </select>
            </div>
            <div class="form-group">
                <label for="service_id">Service :</label>
                <select id="service_id" name="service_id">
                    <?php foreach (getServices($pdo) as $service): ?>
                        <option value="<?= $service['id'] ?>" <?= $serviceId == $service['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($service['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Rechercher</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Service</th>
                    <th>État de paiement</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?= htmlspecialchars($patient['id']) ?></td>
                    <td><?= htmlspecialchars($patient['name']) ?></td>
                    <td><?= htmlspecialchars($patient['service_name']) ?></td>
                    <td><?= htmlspecialchars($patient['payment_status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
