<?php
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();
$date_jour = date('Y-m-d');

// Calcul des KPI du jour
$query = "SELECT SUM(prix_vente_unitaire * quantite_vendue) as ca,
                 SUM(cump_au_moment_vente * quantite_vendue) as cout
          FROM DETAIL_VENTE dv
          JOIN COMMANDE_VENTE cv ON dv.id_commande_vente = cv.id_commande_vente
          WHERE DATE(cv.date_commande) = :today";
$stmt = $db->prepare($query);
$stmt->execute([':today' => $date_jour]);
$kpi = $stmt->fetch(PDO::FETCH_ASSOC);
$ca = $kpi['ca'] ?? 0;
$marge = $ca - ($kpi['cout'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bilan ECE AUTO - <?= $date_jour ?></title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; background: white; }
            .card { border: none; }
        }
        body { background: #f4f7f6; padding: 20px; }
        .bilan-header { border-bottom: 3px solid #1a2a6c; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container bg-white p-5 shadow-sm">
        <div class="no-print text-end mb-4">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimer / Sauvegarder PDF</button>
            <a href="reporting_strategique.php" class="btn btn-secondary">Retour</a>
        </div>

        <div class="bilan-header text-center">
            <h1>ECE AUTO DAKAR</h1>
            <h4>Bilan Financier Journalier</h4>
            <p>Date : <?= date('d/m/Y') ?></p>
        </div>

        <div class="row text-center my-5">
            <div class="col-4"><h5>Ventes</h5><h3><?= number_format($ca, 0, ',', ' ') ?> <small>FCFA</small></h3></div>
            <div class="col-4"><h5>Marge</h5><h3><?= number_format($marge, 0, ',', ' ') ?> <small>FCFA</small></h3></div>
            <div class="col-4"><h5>Rentabilité</h5><h3><?= ($ca > 0) ? round(($marge/$ca)*100, 1) : 0 ?>%</h3></div>
        </div>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Pièce</th>
                    <th>Qté</th>
                    <th>Vente</th>
                    <th>Marge</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.nom_piece, dv.quantite_vendue, dv.prix_vente_unitaire, 
                               (dv.prix_vente_unitaire - dv.cump_au_moment_vente) as m_u
                        FROM DETAIL_VENTE dv
                        JOIN PIECES p ON dv.id_piece = p.id_piece
                        JOIN COMMANDE_VENTE cv ON dv.id_commande_vente = cv.id_commande_vente
                        WHERE DATE(cv.date_commande) = :today";
                $res = $db->prepare($sql);
                $res->execute([':today' => $date_jour]);
                while($row = $res->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $row['nom_piece'] ?></td>
                    <td><?= $row['quantite_vendue'] ?></td>
                    <td><?= number_format($row['prix_vente_unitaire'], 0) ?></td>
                    <td><?= number_format($row['m_u'] * $row['quantite_vendue'], 0) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="mt-5 text-center text-muted">
            <small>Document généré sur Termux ECE-Auto Cloud</small>
        </div>
    </div>
</body>
</html>
