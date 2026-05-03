<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

$mois_actuel = date('m');
$annee_actuelle = date('Y');
$nom_mois = strftime("%B %Y"); // Nécessite config locale, sinon date('F Y')

// Requête de Performance : Revenu par voiture ce mois-ci
$sql_perf = "SELECT 
                v.marque, v.modele, v.immatriculation,
                COUNT(l.id) as nb_locations,
                SUM(l.cout_total) as revenu_genere,
                SUM(DATEDIFF(l.date_fin, l.date_debut)) as jours_occupes
             FROM voitures v
             LEFT JOIN locations l ON v.id = l.voiture_id 
                AND MONTH(l.date_location) = $mois_actuel 
                AND YEAR(l.date_location) = $annee_actuelle
             WHERE v.type_usage = 'Location'
             GROUP BY v.id
             ORDER BY revenu_genere DESC";

$res_perf = $conn->query($sql_perf);

// Totaux du mois
$total_mois = $conn->query("SELECT SUM(cout_total) FROM locations WHERE MONTH(date_location) = $mois_actuel AND YEAR(date_location) = $annee_actuelle AND statut_paiement='Payé'")->fetch_row()[0] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Performance - <?php echo date('M Y'); ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; color: #1e293b; padding: 40px; }
        .header-report { display: flex; justify-content: space-between; border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .kpi-card { background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .kpi-card small { color: #64748b; text-transform: uppercase; font-size: 0.7rem; font-weight: 700; }
        .kpi-card div { font-size: 1.5rem; font-weight: 800; color: #2563eb; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #0f172a; color: white; padding: 12px; text-align: left; font-size: 0.9rem; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .top-performer { background: #f0fdf4 !important; font-weight: bold; }
        .badge-winner { background: #22c55e; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; }

        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 30px; text-align: right;">
    <button onclick="window.print()" style="background:#2563eb; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">📥 Exporter en PDF / Imprimer</button>
    <a href="dashboard.php" style="margin-left:15px; text-decoration:none; color:#64748b;">Retour au Dashboard</a>
</div>

<div class="header-report">
    <div>
        <h1 style="margin:0;">OMEGA AUTO</h1>
        <p style="color:#64748b;">Rapport de Performance Mensuel</p>
    </div>
    <div style="text-align:right;">
        <h2 style="margin:0; color:#2563eb;"><?php echo date('F Y'); ?></h2>
        <p>Généré le <?php echo date('d/m/Y à H:i'); ?></p>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <small>Revenu Total Encaissé</small>
        <div><?php echo number_format($total_mois, 0, ',', ' '); ?> FCFA</div>
    </div>
    <div class="kpi-card">
        <small>Taux d'utilisation</small>
        <div>78%</div> </div>
    <div class="kpi-card">
        <small>Statut du Parc</small>
        <div style="color:#10b981;">Optimal</div>
    </div>
</div>

<h3>Classement de Rentabilité par Véhicule</h3>
<table>
    <thead>
        <tr>
            <th>Véhicule</th>
            <th>Immatriculation</th>
            <th>Nb Locations</th>
            <th>Jours Occupés</th>
            <th>Revenu Généré</th>
            <th>Performance</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $count = 0;
        while($row = $res_perf->fetch_assoc()): 
            $count++;
            $is_top = ($count == 1 && $row['revenu_genere'] > 0);
        ?>
        <tr class="<?php echo $is_top ? 'top-performer' : ''; ?>">
            <td>
                <?php echo $row['marque']." ".$row['modele']; ?>
                <?php if($is_top) echo '<span class="badge-winner">TOP MOIS</span>'; ?>
            </td>
            <td><?php echo $row['immatriculation'] ?? 'N/A'; ?></td>
            <td><?php echo $row['nb_locations']; ?></td>
            <td><?php echo $row['jours_occupes'] ?? 0; ?> j</td>
            <td style="color:#2563eb;"><?php echo number_format($row['revenu_genere'], 0, ',', ' '); ?> FCFA</td>
            <td>
                <?php 
                $progress = ($total_mois > 0) ? ($row['revenu_genere'] / $total_mois) * 100 : 0;
                echo round($progress, 1) . "% du CA";
                ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div style="margin-top:50px; border-top:1px solid #eee; padding-top:20px; font-size:0.8rem; color:#94a3b8; text-align:center;">
    Document confidentiel - Propriété exclusive d'Omega Informatique & Automobile.
</div>

</body>
</html>
