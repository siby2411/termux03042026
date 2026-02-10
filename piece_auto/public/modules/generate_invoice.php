<?php
// /var/www/piece_auto/public/modules/generate_invoice.php
// Génération et affichage d'une facture client (format HTML, prêt pour conversion PDF).

$page_title = "Génération de Facture";
require_once __DIR__ . '/../../config/Database.php';

// IMPORTANT : Cette page NE DOIT PAS inclure header.php ou footer.php 
// pour garantir un format de document propre pour l'impression ou la conversion PDF.

$database = new Database();
$db = $database->getConnection();
$message = '';

$id_commande = (int)($_GET['id_commande'] ?? 0);
$commande_data = null;
$client_data = null;
$lignes_vente = [];

if ($id_commande > 0) {
    try {
        // 1. Récupération des données de la commande
        $query_cmd = "SELECT * FROM COMMANDE_VENTE WHERE id_commande_vente = :id_commande";
        $stmt_cmd = $db->prepare($query_cmd);
        $stmt_cmd->execute([':id_commande' => $id_commande]);
        $commande_data = $stmt_cmd->fetch(PDO::FETCH_ASSOC);

        if ($commande_data) {
            // 2. Récupération des données du client
            $query_client = "SELECT nom, prenom, adresse, email, telephone FROM CLIENTS WHERE id_client = :id_client";
            $stmt_client = $db->prepare($query_client);
            $stmt_client->execute([':id_client' => $commande_data['id_client']]);
            $client_data = $stmt_client->fetch(PDO::FETCH_ASSOC);

            // 3. Récupération des lignes de vente (produits)
            $query_lignes = "
                SELECT
                    lv.quantite,
                    lv.prix_vente_unitaire AS prix_unitaire, -- CORRECTION ICI
                    lv.cout_total_ligne AS montant_ligne,   -- CORRECTION ICI
                    p.reference,
                    p.nom_piece
                FROM LIGNE_VENTE lv
                JOIN PIECES p ON lv.id_piece = p.id_piece
                WHERE lv.id_commande_vente = :id_commande
            ";
            $stmt_lignes = $db->prepare($query_lignes);
            $stmt_lignes->execute([':id_commande' => $id_commande]);
            $lignes_vente = $stmt_lignes->fetchAll(PDO::FETCH_ASSOC);

        } else {
            $message = '<div class="alert alert-warning">Commande n°' . $id_commande . ' non trouvée.</div>';
        }

    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Erreur de base de données : ' . $e->getMessage() . '</div>';
    }
} else {
    $message = '<div class="alert alert-info">Veuillez spécifier l\'ID d\'une commande (Ex: ajouter `?id_commande=1` dans l\'URL) pour générer la facture.</div>';
}

// =================================================================================
// 4. AFFICHAGE DE LA FACTURE
// =================================================================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture N°<?= htmlspecialchars($id_commande) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header-logo { max-width: 150px; }
        .invoice-footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; }
        @media print {
            .btn-print, .btn-back { display: none !important; }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    
    <?php if ($commande_data && $client_data && $lignes_vente): ?>
        
        <div class="row mb-5">
            <div class="col-6">
                <img src="/assets/img/logo.png" style="width:100%; max-width:150px;" class="header-logo" alt="Logo Société">
                <p class="mt-2">
                    **[Nom de Votre Société]**<br>
                    Adresse de l'entreprise<br>
                    Téléphone : 01 23 45 67 89<br>
                    Email : contact@votre-societe.com
                </p>
            </div>
            
            <div class="col-6 text-end">
                <h2>FACTURE</h2>
                <p class="fs-5">N°: **<?= htmlspecialchars($id_commande) ?>**</p>
                <p>Date: **<?= htmlspecialchars(date('d/m/Y', strtotime($commande_data['date_commande']))) ?>**</p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-6">
                <strong>FACTURÉ À :</strong>
                <address>
                    **<?= htmlspecialchars($client_data['nom'] . ' ' . $client_data['prenom']) ?>**<br>
                    <?= htmlspecialchars($client_data['adresse']) ?><br>
                    <?= htmlspecialchars($client_data['telephone']) ?><br>
                    <?= htmlspecialchars($client_data['email']) ?>
                </address>
            </div>
            <div class="col-6">
                <strong>CONDITIONS DE PAIEMENT :</strong>
                <p>
                    Méthode : Virement Bancaire<br>
                    Échéance : 30 jours<br>
                    Référence : CMD-<?= htmlspecialchars($id_commande) ?>
                </p>
            </div>
        </div>

        <table class="table table-bordered table-sm">
            <thead class="bg-light">
                <tr>
                    <th>Réf.</th>
                    <th>Désignation</th>
                    <th class="text-end">Qté</th>
                    <th class="text-end">Prix U. HT</th>
                    <th class="text-end">Total HT</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_ht = 0;
                foreach ($lignes_vente as $ligne): 
                    $total_ht += $ligne['montant_ligne'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($ligne['reference']) ?></td>
                    <td><?= htmlspecialchars($ligne['nom_piece']) ?></td>
                    <td class="text-end"><?= number_format($ligne['quantite'], 0, ',', ' ') ?></td>
                    <td class="text-end"><?= number_format($ligne['prix_unitaire'], 2, ',', ' ') ?> €</td>
                    <td class="text-end fw-bold"><?= number_format($ligne['montant_ligne'], 2, ',', ' ') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td>**TOTAL HORS TAXES :**</td>
                        <td class="text-end fw-bold"><?= number_format($total_ht, 2, ',', ' ') ?> €</td>
                    </tr>
                    <tr>
                        <td>TVA (20%) :</td>
                        <?php 
                        $tva = $total_ht * 0.20;
                        $total_ttc = $total_ht + $tva;
                        ?>
                        <td class="text-end"><?= number_format($tva, 2, ',', ' ') ?> €</td>
                    </tr>
                    <tr class="table-dark">
                        <td>**NET À PAYER (TTC) :**</td>
                        <td class="text-end fw-bold fs-5"><?= number_format($total_ttc, 2, ',', ' ') ?> €</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="invoice-footer">
            <p class="text-center">Arrêté la présente facture à la somme de **<?= number_format($total_ttc, 2, ',', ' ') ?> Euros TTC**.</p>
            <p class="text-center">Merci de votre confiance. Pour toute question, veuillez contacter le service comptabilité.</p>
        </div>

    <?php else: ?>
        <div class="text-center">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="javascript:history.back()" class="btn btn-secondary btn-back me-2">
            <i class="fas fa-chevron-left"></i> Retour
        </a>
        <button onclick="window.print()" class="btn btn-success btn-print">
            <i class="fas fa-print"></i> Imprimer / Enregistrer en PDF
        </button>
    </div>

</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
