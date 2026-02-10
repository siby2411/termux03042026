<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Récupérer les tables et clients
$tables = $conn->query("SELECT * FROM tables ORDER BY numero")->fetchAll();
$clients = $conn->query("SELECT * FROM clients ORDER BY nom, prenom")->fetchAll();

// Ajouter une réservation
if($_POST && isset($_POST['ajouter_reservation'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO reservations (client_id, table_id, date_reservation, heure_debut, heure_fin, nombre_personnes, notes, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['client_id'],
            $_POST['table_id'], 
            $_POST['date_reservation'],
            $_POST['heure_debut'],
            $_POST['heure_fin'],
            $_POST['nombre_personnes'],
            $_POST['notes'],
            $_POST['statut']
        ]);
        
        // Marquer la table comme réservée
        $stmt = $conn->prepare("UPDATE tables SET statut = 'reservee' WHERE id = ?");
        $stmt->execute([$_POST['table_id']]);
        
        echo '<div class="alert alert-success">✅ Réservation créée avec succès!</div>';
    } catch(Exception $e) {
        echo '<div class="alert alert-error">❌ Erreur: ' . $e->getMessage() . '</div>';
    }
}

// Récupérer les réservations
$query = $conn->query("
    SELECT r.*, c.nom, c.prenom, c.telephone, t.numero as table_numero
    FROM reservations r
    LEFT JOIN clients c ON r.client_id = c.id
    LEFT JOIN tables t ON r.table_id = t.id
    WHERE r.date_reservation >= CURDATE()
    ORDER BY r.date_reservation, r.heure_debut
");
$reservations = $query->fetchAll();
?>

<div class="card">
    <h2>🗓️ Nouvelle Réservation</h2>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Client</label>
                <select name="client_id" required>
                    <option value="">Sélectionner un client</option>
                    <?php foreach($clients as $client): ?>
                    <option value="<?= $client['id'] ?>"><?= $client['prenom'] . ' ' . $client['nom'] ?> - <?= $client['telephone'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Table</label>
                <select name="table_id" required>
                    <option value="">Sélectionner une table</option>
                    <?php foreach($tables as $table): ?>
                    <option value="<?= $table['id'] ?>" 
                        <?= $table['statut'] != 'libre' ? 'disabled' : '' ?>>
                        Table <?= $table['numero'] ?> (<?= $table['capacite'] ?> pers) - 
                        <?= match($table['emplacement']) {
                            'terrasse' => '🌿 Terrasse',
                            'interieur' => '🏠 Intérieur',
                            'salon_prive' => '🚪 Salon privé'
                        } ?>
                        <?= $table['statut'] != 'libre' ? ' - ' . ucfirst($table['statut']) : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date_reservation" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Heure début</label>
                <input type="time" name="heure_debut" value="19:00" required>
            </div>
            <div class="form-group">
                <label>Heure fin</label>
                <input type="time" name="heure_fin" value="21:00" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Nombre de personnes</label>
                <input type="number" name="nombre_personnes" min="1" value="2" required>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="confirmee">✅ Confirmée</option>
                    <option value="en_attente">⏱️ En attente</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3" placeholder="Demandes spéciales, anniversaire..."></textarea>
        </div>
        
        <button type="submit" name="ajouter_reservation" class="btn btn-success">✅ Créer la réservation</button>
    </form>
</div>

<div class="card">
    <h2>📋 Réservations à venir</h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Table</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Personnes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reservations as $reservation): ?>
                <tr>
                    <td>
                        <strong><?= $reservation['prenom'] . ' ' . $reservation['nom'] ?></strong>
                        <?php if($reservation['telephone']): ?>
                            <br><small><?= $reservation['telephone'] ?></small>
                        <?php endif; ?>
                    </td>
                    <td>Table <?= $reservation['table_numero'] ?></td>
                    <td><?= date('d/m/Y', strtotime($reservation['date_reservation'])) ?></td>
                    <td><?= date('H:i', strtotime($reservation['heure_debut'])) ?> - <?= date('H:i', strtotime($reservation['heure_fin'])) ?></td>
                    <td><?= $reservation['nombre_personnes'] ?> pers.</td>
                    <td>
                        <span class="badge <?= $reservation['statut'] == 'confirmee' ? 'badge-success' : 'badge-warning' ?>">
                            <?= $reservation['statut'] == 'confirmee' ? '✅ Confirmée' : '⏱️ En attente' ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.25rem;">
                            <button class="btn btn-primary btn-sm">✏️</button>
                            <button class="btn btn-danger btn-sm">🗑️</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($reservations)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                        Aucune réservation à venir
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Vue des tables -->
<div class="card">
    <h2>🪑 État des Tables</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <?php foreach($tables as $table): ?>
        <div class="stat-card" style="border-left-color: 
            <?= match($table['statut']) {
                'libre' => '#27ae60',
                'occupee' => '#e74c3c', 
                'reservee' => '#f39c12',
                'nettoyage' => '#3498db'
            } ?>">
            <div class="stat-number"><?= $table['numero'] ?></div>
            <div><?= $table['capacite'] ?> pers.</div>
            <div>
                <span class="badge" style="background: 
                    <?= match($table['statut']) {
                        'libre' => '#27ae60',
                        'occupee' => '#e74c3c',
                        'reservee' => '#f39c12',
                        'nettoyage' => '#3498db'
                    } ?>">
                    <?= ucfirst($table['statut']) ?>
                </span>
            </div>
            <div><small><?= match($table['emplacement']) {
                'terrasse' => '🌿 Terrasse',
                'interieur' => '🏠 Intérieur',
                'salon_prive' => '🚪 Salon privé'
            } ?></small></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
