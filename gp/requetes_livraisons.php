<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>

<h2><i class="fas fa-search"></i> Requêtes des livraisons par période et direction</h2>

<?php
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-t');
$sens = $_GET['sens'] ?? 'all';
$statut = $_GET['statut'] ?? 'all';

$sql = "SELECT c.*, e.nom as expediteur_nom, d.nom as destinataire_nom 
        FROM colis c 
        LEFT JOIN clients e ON c.client_expediteur_id = e.id 
        LEFT JOIN clients d ON c.client_destinataire_id = d.id 
        WHERE c.derniere_mise_a_jour BETWEEN '$date_debut' AND '$date_fin 23:59:59'";
if ($sens != 'all') $sql .= " AND c.sens = '$sens'";
if ($statut != 'all') $sql .= " AND c.statut = '$statut'";
$sql .= " ORDER BY c.derniere_mise_a_jour DESC";
$colis = $pdo->query($sql)->fetchAll();

// Statistiques
$stats_paris_dakar = $pdo->query("SELECT COUNT(*) as total, SUM(poids_kg) as poids FROM colis WHERE sens = 'paris_dakar' AND derniere_mise_a_jour BETWEEN '$date_debut' AND '$date_fin 23:59:59'")->fetch();
$stats_dakar_paris = $pdo->query("SELECT COUNT(*) as total, SUM(poids_kg) as poids FROM colis WHERE sens = 'dakar_paris' AND derniere_mise_a_jour BETWEEN '$date_debut' AND '$date_fin 23:59:59'")->fetch();
?>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">Filtres de recherche</div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>">
            </div>
            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>">
            </div>
            <div class="col-md-2">
                <label>Direction</label>
                <select name="sens" class="form-select">
                    <option value="all">Tous</option>
                    <option value="paris_dakar" <?= $sens == 'paris_dakar' ? 'selected' : '' ?>>Paris → Dakar</option>
                    <option value="dakar_paris" <?= $sens == 'dakar_paris' ? 'selected' : '' ?>>Dakar → Paris</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Statut</label>
                <select name="statut" class="form-select">
                    <option value="all">Tous</option>
                    <option value="enregistre">Enregistré</option>
                    <option value="depart">Départ</option>
                    <option value="transit">Transit</option>
                    <option value="arrivee">Arrivée</option>
                    <option value="livre">Livré</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
            </div>
        </form>
    </div>
</div>

<!-- Cartes récapitulatives -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Paris → Dakar</h5>
                <h2><?= $stats_paris_dakar['total'] ?? 0 ?> colis</h2>
                <small>Poids total : <?= number_format($stats_paris_dakar['poids'] ?? 0, 1) ?> kg</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Dakar → Paris</h5>
                <h2><?= $stats_dakar_paris['total'] ?? 0 ?> colis</h2>
                <small>Poids total : <?= number_format($stats_dakar_paris['poids'] ?? 0, 1) ?> kg</small>
            </div>
        </div>
    </div>
</div>

<!-- Liste des colis -->
<div class="card">
    <div class="card-header bg-dark text-white">Résultats des livraisons</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr><th>N° suivi</th><th>Direction</th><th>Expéditeur</th><th>Destinataire</th><th>Poids</th><th>Statut</th><th>Date MAJ</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($colis as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['numero_suivi']) ?></td>
                        <td><?= $c['sens'] == 'paris_dakar' ? '<span class="badge bg-primary">Paris → Dakar</span>' : '<span class="badge bg-success">Dakar → Paris</span>' ?></td>
                        <td><?= htmlspecialchars($c['expediteur_nom']) ?></td>
                        <td><?= htmlspecialchars($c['destinataire_nom']) ?></td>
                        <td><?= $c['poids_kg'] ?> kg</td>
                        <td><span class="badge bg-secondary"><?= $c['statut'] ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($c['derniere_mise_a_jour'])) ?></td>
                        <td><a href="suivi_carte.php?numero=<?= urlencode($c['numero_suivi']) ?>" class="btn btn-sm btn-info">Carte</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
