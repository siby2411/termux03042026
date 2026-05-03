<?php
require_once '../../includes/config.php';

if (!isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'État financier';

// Récupérer les filtres
$periode = $_GET['periode'] ?? 'mois';
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');
$caissier_id = $_GET['caissier_id'] ?? '';

// Gestion des périodes
switch($periode) {
    case 'jour':
        $date_debut = date('Y-m-d');
        $date_fin = date('Y-m-d');
        break;
    case 'semaine':
        $date_debut = date('Y-m-d', strtotime('monday this week'));
        $date_fin = date('Y-m-d');
        break;
    case 'mois':
        $date_debut = date('Y-m-01');
        $date_fin = date('Y-m-d');
        break;
    case 'trimestre':
        $date_debut = date('Y-m-01', strtotime('first day of this quarter'));
        $date_fin = date('Y-m-d');
        break;
    case 'annee':
        $date_debut = date('Y-01-01');
        $date_fin = date('Y-m-d');
        break;
}

// Récupérer la liste des caissiers pour le filtre
$caissiers = $pdo->query("SELECT id, nom, prenom FROM utilisateurs WHERE role IN ('admin', 'caissier', 'gestionnaire') ORDER BY nom")->fetchAll();

// Construire la requête avec filtre
$where = "v.date_vente BETWEEN ? AND ? AND v.statut = 'validee'";
$params = [$date_debut . ' 00:00:00', $date_fin . ' 23:59:59'];

if ($caissier_id) {
    $where .= " AND v.utilisateur_id = ?";
    $params[] = $caissier_id;
}

// Statistiques globales
$stmt = $pdo->prepare("
    SELECT 
        SUM(montant_total) as ca_total,
        COUNT(*) as nb_ventes,
        AVG(montant_total) as panier_moyen,
        SUM(montant_total) / COUNT(DISTINCT DATE(date_vente)) as ca_journalier
    FROM ventes v
    WHERE $where
");
$stmt->execute($params);
$stats = $stmt->fetch();

// Ventes par caissier
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.username,
        u.nom,
        u.prenom,
        COUNT(v.id) as nb_ventes,
        SUM(v.montant_total) as montant_total,
        AVG(v.montant_total) as moyenne_vente,
        MAX(v.date_vente) as derniere_vente
    FROM utilisateurs u
    LEFT JOIN ventes v ON u.id = v.utilisateur_id AND v.date_vente BETWEEN ? AND ? AND v.statut = 'validee'
    WHERE u.role IN ('admin', 'caissier', 'gestionnaire')
    GROUP BY u.id
    ORDER BY montant_total DESC
");
$stmt->execute([$date_debut . ' 00:00:00', $date_fin . ' 23:59:59']);
$stats_caissiers = $stmt->fetchAll();

// Ventes par jour
$stmt = $pdo->prepare("
    SELECT 
        DATE(v.date_vente) as date,
        COUNT(*) as nb_ventes,
        SUM(v.montant_total) as total_journalier
    FROM ventes v
    WHERE $where
    GROUP BY DATE(v.date_vente)
    ORDER BY date DESC
    LIMIT 30
");
$stmt->execute($params);
$ventes_journalieres = $stmt->fetchAll();

// Top ventes
$stmt = $pdo->prepare("
    SELECT 
        l.titre,
        l.auteur,
        SUM(vl.quantite) as quantite_vendue,
        SUM(vl.sous_total) as montant_total
    FROM ventes_lignes vl
    JOIN livres l ON vl.livre_id = l.id
    JOIN ventes v ON vl.vente_id = v.id
    WHERE v.date_vente BETWEEN ? AND ? AND v.statut = 'validee'
    GROUP BY l.id
    ORDER BY quantite_vendue DESC
    LIMIT 10
");
$stmt->execute([$date_debut . ' 00:00:00', $date_fin . ' 23:59:59']);
$top_livres = $stmt->fetchAll();

// Ventes par mode de paiement
$stmt = $pdo->prepare("
    SELECT 
        mode_paiement,
        COUNT(*) as nb_ventes,
        SUM(montant_total) as montant
    FROM ventes v
    WHERE $where
    GROUP BY mode_paiement
");
$stmt->execute($params);
$paiements = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-chart-line"></i> État financier</h4>
            </div>
            <div class="card-body">
                <!-- Filtres -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label>Période</label>
                        <select name="periode" class="form-control" onchange="this.form.submit()">
                            <option value="jour" <?php echo $periode == 'jour' ? 'selected' : ''; ?>>Aujourd'hui</option>
                            <option value="semaine" <?php echo $periode == 'semaine' ? 'selected' : ''; ?>>Cette semaine</option>
                            <option value="mois" <?php echo $periode == 'mois' ? 'selected' : ''; ?>>Ce mois</option>
                            <option value="trimestre" <?php echo $periode == 'trimestre' ? 'selected' : ''; ?>>Ce trimestre</option>
                            <option value="annee" <?php echo $periode == 'annee' ? 'selected' : ''; ?>>Cette année</option>
                            <option value="personnalise" <?php echo $periode == 'personnalise' ? 'selected' : ''; ?>>Personnalisé</option>
                        </select>
                    </div>
                    <?php if($periode == 'personnalise'): ?>
                    <div class="col-md-3">
                        <label>Date début</label>
                        <input type="date" name="date_debut" class="form-control" value="<?php echo $date_debut; ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Date fin</label>
                        <input type="date" name="date_fin" class="form-control" value="<?php echo $date_fin; ?>">
                    </div>
                    <?php endif; ?>
                    <div class="col-md-3">
                        <label>Caissier</label>
                        <select name="caissier_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Tous les caissiers</option>
                            <?php foreach($caissiers as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $caissier_id == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo $c['prenom'] . ' ' . $c['nom']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Cartes statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Chiffre d'affaires</h6>
                                <h3><?php echo number_format($stats['ca_total'] ?? 0, 0, ',', ' '); ?> FCFA</h3>
                                <small><?php echo $stats['nb_ventes'] ?? 0; ?> ventes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Panier moyen</h6>
                                <h3><?php echo number_format($stats['panier_moyen'] ?? 0, 0, ',', ' '); ?> FCFA</h3>
                                <small>Par transaction</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>CA journalier moyen</h6>
                                <h3><?php echo number_format($stats['ca_journalier'] ?? 0, 0, ',', ' '); ?> FCFA</h3>
                                <small>Moyenne par jour</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6>Nombre de transactions</h6>
                                <h3><?php echo $stats['nb_ventes'] ?? 0; ?></h3>
                                <small>Sur la période</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance par caissier -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-users"></i> Performance par caissier</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Caissier</th>
                                        <th>Nombre ventes</th>
                                        <th>Montant total</th>
                                        <th>Moyenne/vente</th>
                                        <th>Dernière vente</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $max_ventes = !empty($stats_caissiers) ? max(array_column($stats_caissiers, 'montant_total')) : 0;
                                    foreach($stats_caissiers as $caissier): 
                                    $performance = $max_ventes > 0 ? ($caissier['montant_total'] / $max_ventes) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo $caissier['prenom'] . ' ' . $caissier['nom']; ?></td>
                                        <td><?php echo $caissier['nb_ventes']; ?></td>
                                        <td><?php echo number_format($caissier['montant_total'], 0, ',', ' '); ?> FCFA</td>
                                        <td><?php echo number_format($caissier['moyenne_vente'], 0, ',', ' '); ?> FCFA</td>
                                        <td><?php echo $caissier['derniere_vente'] ? date('d/m/Y', strtotime($caissier['derniere_vente'])) : '-'; ?></td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?php echo $performance; ?>%">
                                                    <?php echo number_format($performance, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Ventes journalières -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-calendar-day"></i> Ventes journalières</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Nombre ventes</th>
                                                <th>Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($ventes_journalieres as $vj): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($vj['date'])); ?></td>
                                                <td><?php echo $vj['nb_ventes']; ?></td>
                                                <td><?php echo number_format($vj['total_journalier'], 0, ',', ' '); ?> FCFA</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top livres -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-trophy"></i> Top 10 des meilleures ventes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Livre</th>
                                                <th>Auteur</th>
                                                <th>Quantité</th>
                                                <th>Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($top_livres as $livre): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($livre['titre']); ?></strong></td>
                                                <td><?php echo $livre['auteur']; ?></td>
                                                <td><?php echo $livre['quantite_vendue']; ?></td>
                                                <td><?php echo number_format($livre['montant_total'], 0, ',', ' '); ?> FCFA</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modes de paiement -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-credit-card"></i> Répartition par mode de paiement</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mode</th>
                                        <th>Nombre</th>
                                        <th>Montant</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_global = $stats['ca_total'] ?? 0;
                                    foreach($paiements as $paiement): 
                                    $pourcentage = $total_global > 0 ? ($paiement['montant'] / $total_global) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo ucfirst($paiement['mode_paiement']); ?></td>
                                        <td><?php echo $paiement['nb_ventes']; ?></td>
                                        <td><?php echo number_format($paiement['montant'], 0, ',', ' '); ?> FCFA</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?php echo $pourcentage; ?>%">
                                                    <?php echo number_format($pourcentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
