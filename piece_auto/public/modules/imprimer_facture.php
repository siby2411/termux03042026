<?php
// /var/www/piece_auto/public/modules/imprimer_facture.php
require_once __DIR__ . '/../../config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Nettoyage de l'ID pour éviter les caractères polluants
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("<div style='padding:50px; text-align:center; font-family:sans-serif;'>
            <h2>Erreur : ID de facture invalide.</h2>
            <a href='gestion_commandes_vente.php'>Retour à la liste</a>
         </div>");
}

try {
    // 1. Données Entête + Client (Correction colonnes nom/prenom)
    $query = "SELECT cv.*, c.nom, c.prenom, c.adresse, c.telephone, c.email 
              FROM COMMANDE_VENTE cv 
              JOIN CLIENTS c ON cv.id_client = c.id_client 
              WHERE cv.id_commande_vente = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
    $cmd = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cmd) { die("Facture #$id introuvable dans la base de données."); }

    // 2. Données Articles
    $query_lines = "SELECT dv.*, p.reference, p.nom_piece 
                    FROM DETAIL_VENTE dv 
                    JOIN PIECES p ON dv.id_piece = p.id_piece 
                    WHERE dv.id_commande_vente = :id";
    $stmt_lines = $db->prepare($query_lines);
    $stmt_lines->execute([':id' => $id]);
    $lignes = $stmt_lines->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur technique : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture_<?= $id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f0f0; padding: 20px; }
        .invoice-card { 
            background: white; max-width: 900px; margin: auto; 
            padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }
        .header-line { border-bottom: 3px solid #0d6efd; margin-bottom: 20px; padding-bottom: 20px; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .invoice-card { box-shadow: none; border: none; max-width: 100%; width: 100%; margin: 0; }
        }
    </style>
</head>
<body>

<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-success btn-lg me-2">
        <i class="fas fa-print"></i> Imprimer / Enregistrer PDF
    </button>
    <button onclick="window.history.back()" class="btn btn-secondary btn-lg">
        <i class="fas fa-arrow-left"></i> Retour
    </button>
</div>

<div class="invoice-card">
    <div class="header-line d-flex justify-content-between">
        <div>
            <h1 class="text-primary fw-bold mb-0">PIÈCE AUTO SERVICES</h1>
            <p class="text-muted">Expertise et Pièces détachées</p>
        </div>
        <div class="text-end">
            <h2 class="mb-0">FACTURE</h2>
            <p class="h4 text-secondary">N° #<?= $id ?></p>
            <p>Date : <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-6">
            <h5 class="text-muted border-bottom pb-1">Émetteur</h5>
            <strong>PIÈCE AUTO ERP</strong><br>
            Garage Central - Zone Industrielle<br>
            Contact : 01 23 45 67 89
        </div>
        <div class="col-6 text-end">
            <h5 class="text-muted border-bottom pb-1">Client</h5>
            <strong><?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?></strong><br>
            <?= htmlspecialchars($cmd['adresse'] ?? 'Adresse non fournie') ?><br>
            Tél : <?= htmlspecialchars($cmd['telephone']) ?>
        </div>
    </div>

    <table class="table table-striped mt-5">
        <thead class="bg-primary text-white">
            <tr>
                <th>Référence</th>
                <th>Désignation</th>
                <th class="text-center">Qté</th>
                <th class="text-end">Prix U. HT</th>
                <th class="text-end">Total HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $l): ?>
            <tr>
                <td><strong><?= htmlspecialchars($l['reference']) ?></strong></td>
                <td><?= htmlspecialchars($l['nom_piece']) ?></td>
                <td class="text-center"><?= $l['quantite_vendue'] ?></td>
                <td class="text-end"><?= number_format($l['prix_vente_unitaire'], 2, ',', ' ') ?> >
                <td class="text-end fw-bold"><?= number_format($l['quantite_vendue'] * $l['prix_vente_unitaire'], 2, ',', ' ') ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row mt-5">
        <div class="col-7">
            <p class="small text-muted">Conditions : Règlement à réception. <br> Aucun escompte pour paiement anticipé.</p>
        </div>
        <div class="col-5">
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>Total HT</span>
                <span><?= number_format($cmd['total_commande'], 2, ',', ' ') ?> €</span>
            </div>
            <div class="d-flex justify-content-between py-2 h4 fw-bold text-primary">
                <span>NET À PAYER</span>
                <span><?= number_format($cmd['total_commande'], 2, ',', ' ') ?> €</spa
            </div>
        </div>
    </div>
</div>

</body>
</html>
