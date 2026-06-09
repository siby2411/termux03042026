<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$id_vente = $_GET['id'] ?? null;
if (!$id_vente) die("ID de vente manquant.");

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Récupérer les infos de la vente et du client (Colonnes vérifiées via DESCRIBE)
    $queryVente = "SELECT cv.*, c.nom, c.prenom, c.telephone 
                   FROM COMMANDE_VENTE cv 
                   JOIN CLIENTS c ON cv.id_client = c.id_client 
                   WHERE cv.id_commande_vente = ?";
    $stmtVente = $db->prepare($queryVente);
    $stmtVente->execute([$id_vente]);
    $vente = $stmtVente->fetch(PDO::FETCH_ASSOC);

    if (!$vente) die("Vente introuvable.");

    // 2. Récupérer les articles (Colonnes vérifiées : id_piece, quantite, prix_vente_unitaire)
    $queryLignes = "SELECT lv.*, p.nom_piece, p.reference 
                    FROM LIGNE_VENTE lv 
                    JOIN PIECES p ON lv.id_piece = p.id_piece 
                    WHERE lv.id_commande_vente = ?";
    $stmtLignes = $db->prepare($queryLignes);
    $stmtLignes->execute([$id_vente]);
    $lignes = $stmtLignes->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur lors de la génération : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture OMEGA TECH #<?= $id_vente ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { .no-print { display: none; } }
        body { background: #f4f4f4; padding: 20px; }
        .invoice-card { background: white; padding: 40px; border-radius: 0; border: 1px solid #ddd; max-width: 900px; margin: auto; }
        .header-top { border-bottom: 2px solid #0d6efd; margin-bottom: 30px; padding-bottom: 20px; }
        .company-name { font-size: 32px; font-weight: 900; color: #0d6efd; }
    </style>
</head>
<body>
    <div class="container no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-print me-2"></i> IMPRIMER LA FACTURE
        </button>
        <a href="../index.php" class="btn btn-outline-secondary btn-lg ms-2">Retour Dashboard</a>
    </div>

    <div class="invoice-card shadow">
        <div class="header-top d-flex justify-content-between align-items-center">
            <div>
                <div class="company-name text-uppercase">OMEGA TECH</div>
                <p class="text-muted mb-0">Spécialiste Pièces Détachées Auto<br>Dakar, Sénégal<br>Tél: +221 77 XXX XX XX</p>
            </div>
            <div class="text-end">
                <h1 class="text-uppercase text-muted display-6">FACTURE</h1>
                <p class="h5 mb-0">#<?= str_pad($id_vente, 6, "0", STR_PAD_LEFT) ?></p>
                <p class="text-muted small">Date : <?= date('d/m/Y H:i', strtotime($vente['date_vente'])) ?></p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-6">
                <p class="text-uppercase small fw-bold text-muted mb-1">Facturé à :</p>
                <h4 class="mb-1"><?= strtoupper($vente['nom']) ?> <?= $vente['prenom'] ?></h4>
                <p class="text-muted"><i class="fas fa-phone small"></i> <?= $vente['telephone'] ?></p>
            </div>
        </div>

        <table class="table table-striped">
            <thead class="bg-light">
                <tr>
                    <th class="py-3">Référence</th>
                    <th class="py-3">Désignation</th>
                    <th class="py-3 text-center">Quantité</th>
                    <th class="py-3 text-end">PU (F)</th>
                    <th class="py-3 text-end">Total (F)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $l): ?>
                <tr>
                    <td class="py-3"><?= $l['reference'] ?></td>
                    <td class="py-3 fw-bold"><?= $l['nom_piece'] ?></td>
                    <td class="py-3 text-center"><?= $l['quantite'] ?></td>
                    <td class="py-3 text-end"><?= number_format($l['prix_vente_unitaire'], 0, ',', ' ') ?></td>
                    <td class="py-3 text-end"><?= number_format($l['prix_vente_unitaire'] * $l['quantite'], 0, ',', ' ') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="border-top border-dark">
                <tr>
                    <th colspan="4" class="text-end py-3 h4">MONTANT TOTAL</th>
                    <th class="text-end py-3 h4 text-primary"><?= number_format($vente['total_commande'], 0, ',', ' ') ?> FCFA</th>
                </tr>
            </tfoot>
        </table>

        <div class="mt-5 pt-4 border-top text-center small text-muted">
            <p>Facture générée par OMEGA PIÈCE AUTO ERP - Dakar.<br>Merci de votre visite !</p>
        </div>
    </div>
</body>
</html>
