<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Statistiques
$stats = [];
$query = $conn->query("SELECT COUNT(*) as total FROM clients");
$stats['clients'] = $query->fetch()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM commandes WHERE statut != 'recupere'");
$stats['commandes_encours'] = $query->fetch()['total'];

$query = $conn->query("SELECT SUM(total_ttc) as total FROM commandes WHERE DATE(date_commande) = CURDATE()");
$stats['ca_jour'] = $query->fetch()['total'] ?? 0;

$query = $conn->query("SELECT COUNT(*) as total FROM commandes WHERE statut = 'en_attente'");
$stats['commandes_attente'] = $query->fetch()['total'];
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $stats['clients'] ?></div>
        <div>Clients</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['commandes_encours'] ?></div>
        <div>Commandes en cours</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['ca_jour'], 2, ',', ' ') ?> €</div>
        <div>CA aujourd'hui</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['commandes_attente'] ?></div>
        <div>En attente</div>
    </div>
</div>

<div class="card">
    <h2>Commandes récentes</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Date</th>
                <th>Total</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = $conn->query("
                SELECT c.*, cl.nom, cl.prenom 
                FROM commandes c 
                LEFT JOIN clients cl ON c.client_id = cl.id 
                ORDER BY c.date_commande DESC 
                LIMIT 10
            ");
            while($row = $query->fetch(PDO::FETCH_ASSOC)): 
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['prenom'] . ' ' . $row['nom'] ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_commande'])) ?></td>
                <td><?= number_format($row['total_ttc'], 2, ',', ' ') ?> €</td>
                <td>
                    <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; 
                        background: <?= 
                            $row['statut'] == 'termine' ? '#27ae60' : 
                            ($row['statut'] == 'en_cours' ? '#3498db' : 
                            ($row['statut'] == 'recupere' ? '#95a5a6' : '#f39c12')) 
                        ?>; color: white;">
                        <?= str_replace('_', ' ', $row['statut']) ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
