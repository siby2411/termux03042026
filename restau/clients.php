<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Ajouter un client
if($_POST && isset($_POST['ajouter_client'])) {
    $stmt = $conn->prepare("INSERT INTO clients (nom, prenom, telephone, email, adresse, preferences) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['telephone'], $_POST['email'], $_POST['adresse'], $_POST['preferences']]);
    echo '<div class="alert alert-success">✅ Client ajouté avec succès!</div>';
}

// Récupérer les clients
$query = $conn->query("SELECT * FROM clients ORDER BY nom, prenom");
$clients = $query->fetchAll();

// Statistiques
$total_clients = count($clients);
$clients_avec_email = count(array_filter($clients, function($c) { return !empty($c['email']); }));
$clients_avec_tel = count(array_filter($clients, function($c) { return !empty($c['telephone']); }));
?>

<div class="card">
    <h2>👥 Ajouter un Client</h2>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" required>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="telephone" placeholder="06 12 34 56 78">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="client@email.com">
            </div>
        </div>
        <div class="form-group">
            <label>Adresse</label>
            <textarea name="adresse" rows="2" placeholder="Adresse complète..."></textarea>
        </div>
        <div class="form-group">
            <label>Préférences alimentaires</label>
            <textarea name="preferences" rows="2" placeholder="Allergies, régimes spéciaux..."></textarea>
        </div>
        <button type="submit" name="ajouter_client" class="btn btn-primary">➕ Ajouter le client</button>
    </form>
</div>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $total_clients ?></div>
        <div>Total Clients</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $clients_avec_email ?></div>
        <div>Avec email</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $clients_avec_tel ?></div>
        <div>Avec téléphone</div>
    </div>
</div>

<div class="card">
    <h2>📋 Liste des Clients (<?= $total_clients ?>)</h2>
    
    <!-- Filtre -->
    <div style="margin-bottom: 1.5rem;">
        <input type="text" id="searchInput" placeholder="🔍 Rechercher un client..." style="width: 100%; padding: 0.75rem; border: 2px solid #e9ecef; border-radius: 8px;">
    </div>

    <div class="table-container">
        <table id="tableClients">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th>Date inscription</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($clients as $client): ?>
                <tr class="client-row" data-nom="<?= htmlspecialchars(strtolower($client['nom'] . ' ' . $client['prenom'])) ?>">
                    <td><strong><?= htmlspecialchars($client['nom']) ?></strong></td>
                    <td><?= htmlspecialchars($client['prenom']) ?></td>
                    <td>
                        <?php if($client['telephone']): ?>
                            <a href="tel:<?= $client['telephone'] ?>" class="btn btn-sm btn-primary">📞 <?= $client['telephone'] ?></a>
                        <?php else: ?>
                            <span style="color: #95a5a6;">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($client['email']): ?>
                            <a href="mailto:<?= $client['email'] ?>" class="btn btn-sm btn-info">📧 <?= $client['email'] ?></a>
                        <?php else: ?>
                            <span style="color: #95a5a6;">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($client['adresse']): ?>
                            <small><?= htmlspecialchars(substr($client['adresse'], 0, 30)) ?><?= strlen($client['adresse']) > 30 ? '...' : '' ?></small>
                        <?php else: ?>
                            <span style="color: #95a5a6;">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($client['date_creation'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if(empty($clients)): ?>
        <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
            <div style="font-size: 4rem;">👥</div>
            <h3>Aucun client enregistré</h3>
            <p>Commencez par ajouter votre premier client.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Filtrage des clients
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.client-row');
    
    rows.forEach(row => {
        const nom = row.getAttribute('data-nom');
        if (nom.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<style>
.table-container {
    overflow-x: auto;
}

.client-row:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .table-container {
        font-size: 0.9rem;
    }
    
    .table-container td:nth-child(4),
    .table-container th:nth-child(4),
    .table-container td:nth-child(5),
    .table-container th:nth-child(5) {
        display: none;
    }
}
</style>

<?php include 'footer.php'; ?>
