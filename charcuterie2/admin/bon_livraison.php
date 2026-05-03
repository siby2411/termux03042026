<?php
require_once 'header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pdo = getPDO();
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

if (!$id || !in_array($type, ['vente', 'appro'])) {
    die("Document introuvable");
}

if ($type == 'vente') {
    $stmt = $pdo->prepare("
        SELECT v.*, c.nom as client_nom, c.prenom as client_prenom, c.adresse as client_adresse, c.telephone
        FROM ventes v
        LEFT JOIN clients c ON v.client_id = c.id
        WHERE v.id = ?
    ");
    $stmt->execute([$id]);
    $document = $stmt->fetch();
    $title = "Bon de livraison - Vente";
    $items = $pdo->prepare("
        SELECT vl.*, p.nom as produit_nom
        FROM ventes_lignes vl
        JOIN produits p ON vl.produit_id = p.id
        WHERE vl.vente_id = ?
    ");
    $items->execute([$id]);
} else {
    $stmt = $pdo->prepare("
        SELECT a.*, p.nom as produit_nom, f.nom as fournisseur_nom
        FROM approvisionnements a
        LEFT JOIN produits p ON a.produit_id = p.id
        LEFT JOIN fournisseurs f ON a.fournisseur_id = f.id
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    $document = $stmt->fetch();
    $title = "Bon de réception - Approvisionnement";
    $items = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            .print-only { display: block; }
            body { padding: 20px; }
        }
        .print-only { display: none; }
        .bon-livraison {
            font-family: 'Times New Roman', serif;
            padding: 20px;
            border: 1px solid #ddd;
            margin: 20px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="bon-livraison">
        <div class="header">
            <h2>OMEGA CHARCUTERIE</h2>
            <p>Zone Commerciale, Dakar - Sénégal</p>
            <p>Tél: +221 33 123 45 67 | Email: contact@omega-charcuterie.sn</p>
            <h3 class="mt-4"><?= $title ?></h3>
            <p>N°: <?= $type == 'vente' ? $document['numero_vente'] : $document['reference'] ?></p>
            <p>Date: <?= formatDate($type == 'vente' ? $document['date_vente'] : $document['date_appro']) ?></p>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Client / Fournisseur</h5>
                <p>
                    <?php if ($type == 'vente'): ?>
                        <strong><?= escape($document['client_nom'] . ' ' . $document['client_prenom']) ?></strong><br>
                        Tél: <?= escape($document['telephone']) ?><br>
                        Adresse: <?= escape($document['client_adresse']) ?>
                    <?php else: ?>
                        <strong><?= escape($document['fournisseur_nom']) ?></strong><br>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6">
                <h5>Informations</h5>
                <p>
                    Référence: <?= $type == 'vente' ? $document['numero_vente'] : $document['reference'] ?><br>
                    Mode de paiement: <?= $type == 'vente' ? escape($document['mode_paiement']) : '-' ?><br>
                    Statut: <?= $type == 'vente' ? escape($document['statut']) : 'Validé' ?>
                </p>
            </div>
        </div>
        
        <table class="table table-bordered">
            <thead class="table-light">
                <tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th> </thead>
            <tbody>
                <?php if ($type == 'vente'): ?>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= escape($item['produit_nom']) ?></td>
                        <td class="text-center"><?= $item['quantite'] ?></td>
                        <td class="text-end"><?= formatMoney($item['prix_unitaire']) ?></td>
                        <td class="text-end"><?= formatMoney($item['total_ht']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-active">
                        <td colspan="3" class="text-end fw-bold">Total TTC</td>
                        <td class="text-end fw-bold"><?= formatMoney($document['total_ttc']) ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td><?= escape($document['produit_nom']) ?></td>
                        <td class="text-center"><?= $document['quantite'] ?></td>
                        <td class="text-end"><?= formatMoney($document['prix_unitaire']) ?></td>
                        <td class="text-end"><?= formatMoney($document['total']) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <div class="row">
                <div class="col-md-4">
                    <p>Signature du client</p>
                    <div class="mt-5">___________________</div>
                </div>
                <div class="col-md-4">
                    <p>Cachet de l'entreprise</p>
                    <div class="mt-5">___________________</div>
                </div>
                <div class="col-md-4">
                    <p>Date de livraison</p>
                    <div class="mt-5">___________________</div>
                </div>
            </div>
            <p class="mt-4 small text-muted">Document généré par OMEGA INFORMATIQUE CONSULTING</p>
        </div>
    </div>
    
    <div class="text-center mt-3 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-1"></i>Imprimer</button>
        <button onclick="window.close()" class="btn btn-secondary">Fermer</button>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
