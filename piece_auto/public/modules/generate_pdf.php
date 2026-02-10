<?php
// =================================================================================
// 1. INCLUSION ET CONNEXION BDD
// NOTE: L'implémentation complète nécessiterait une librairie comme TCPDF ou mPDF
// =================================================================================
require_once __DIR__ . '/../../config/Database.php'; 

$pdo = null; 
$id_commande = (int)($_GET['id'] ?? 0);

if ($id_commande === 0) {
    header('Location: gestion_commandes_achat.php');
    exit;
}

try {
    $db = new Database(); 
    $pdo = $db->getConnection(); 
    
    // 2. RÉCUPÉRATION DES DONNÉES DE LA COMMANDE
    $stmt_head = $pdo->prepare("
        SELECT 
            CA.id_commande_achat, CA.date_commande, 
            F.nom_fournisseur, F.adresse, F.telephone, F.email
        FROM COMMANDES_ACHAT CA
        INNER JOIN FOURNISSEURS F ON CA.id_fournisseur = F.id_fournisseur
        WHERE CA.id_commande_achat = ?
    ");
    $stmt_head->execute([$id_commande]);
    $commande = $stmt_head->fetch(PDO::FETCH_ASSOC);

    $stmt_lignes = $pdo->prepare("
        SELECT 
            LC.quantite, LC.prix_unitaire,
            P.reference_sku, P.nom_piece
        FROM LIGNES_COMMANDE_ACHAT LC
        INNER JOIN PIECES P ON LC.id_piece = P.id_piece
        WHERE LC.id_commande_achat = ?
    ");
    $stmt_lignes->execute([$id_commande]);
    $lignes = $stmt_lignes->fetchAll(PDO::FETCH_ASSOC);

    if (!$commande || empty($lignes)) {
        throw new Exception("Commande ou lignes introuvables pour le PDF.");
    }
    
} catch (Exception $e) {
    // Redirection simple en cas d'erreur
    header('Location: detail_commande_achat.php?id=' . $id_commande . '&msg=' . urlencode("Erreur PDF: " . $e->getMessage()));
    exit;
}

// =================================================================================
// 3. GÉNÉRATION DU CONTENU PDF (Simulation HTML pour l'exemple)
// =================================================================================

// En-têtes pour forcer le téléchargement PDF (si une librairie PDF était utilisée)
// header('Content-Type: application/pdf');
// header('Content-Disposition: attachment; filename="Commande_Achat_' . $id_commande . '.pdf"');

// ---------------------------------------------------------------------------------
// Pour cette implémentation simple, nous allons générer un HTML qui mime le PDF.
// Si vous intégrez réellement TCPDF ou mPDF, le code ci-dessous sera remplacé
// par les appels aux fonctions de la librairie.
// ---------------------------------------------------------------------------------

$total_commande = 0;
ob_start(); // Démarre la capture de sortie HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>Commande d'Achat N°<?= $id_commande ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .fournisseur, .details { width: 100%; margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h1>COMMANDE D'ACHAT N°<?= $id_commande ?></h1>
        <p>Date de Commande: <?= date('d/m/Y', strtotime($commande['date_commande'])) ?></p>
        <p>Généré le: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <div class="fournisseur">
        <h3>Fournisseur</h3>
        <p><strong><?= htmlspecialchars($commande['nom_fournisseur']) ?></strong></p>
        <p>Adresse: <?= htmlspecialchars($commande['adresse']) ?></p>
        <p>Contact: <?= htmlspecialchars($commande['telephone']) ?> | <?= htmlspecialchars($commande['email']) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Réf. SKU</th>
                <th style="width: 50%">Désignation de la Pièce</th>
                <th style="width: 10%" class="total">Quantité</th>
                <th style="width: 15%" class="total">Prix Unitaire HT</th>
                <th style="width: 10%" class="total">TOTAL HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $ligne): 
                $total_ligne = $ligne['quantite'] * $ligne['prix_unitaire'];
                $total_commande += $total_ligne;
            ?>
            <tr>
                <td><?= htmlspecialchars($ligne['reference_sku']) ?></td>
                <td><?= htmlspecialchars($ligne['nom_piece']) ?></td>
                <td class="total"><?= $ligne['quantite'] ?></td>
                <td class="total"><?= number_format($ligne['prix_unitaire'], 2, ',', ' ') ?> €</td>
                <td class="total"><?= number_format($total_ligne, 2, ',', ' ') ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total">TOTAL HT</td>
                <td class="total"><?= number_format($total_commande, 2, ',', ' ') ?> €</td>
            </tr>
            <tr>
                <td colspan="4" class="total">TVA (Taux à définir)</td>
                <td class="total">0.00 €</td> 
            </tr>
            <tr>
                <td colspan="4" class="total">TOTAL TTC</td>
                <td class="total"><?= number_format($total_commande, 2, ',', ' ') ?> €</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 50px; text-align: right;">
        <p>Signature (Achats)</p>
        <p>_________________________</p>
    </div>

</body>
</html>
<?php
$html_content = ob_get_clean();

// Pour simuler un PDF, nous allons simplement afficher le HTML généré.
// En production, vous passeriez $html_content à la librairie PDF.
echo $html_content;
?>
