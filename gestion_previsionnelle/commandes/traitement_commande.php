<?php
// traitement_commande.php
include_once __DIR__ . '/../config/db.php';

$database = new Database();
$db = $database->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: index.php');
    exit;
}

// 1. Récupération des données du formulaire
$client_id = $_POST['client_id'] ?? null;
$date_commande = $_POST['date_commande'] ?? date('Y-m-d');
$ref_facture = $_POST['ref_facture'] ?? null;
$montant_total = floatval($_POST['montant_total'] ?? 0);
$details = $_POST['details'] ?? [];

$is_valid = $client_id && $montant_total > 0 && !empty($details);

if (!$is_valid) {
    // Redirection avec message d'erreur simple
    header('Location: creer.php?action=error&msg=Données+incomplètes+ou+montant+nul.');
    exit;
}

try {
    // Démarrer la transaction pour garantir que tout réussit ou que tout échoue
    $db->beginTransaction();

    // --- ÉTAPE 1: Insertion de la Commande (Master) ---
    $query_commande = "INSERT INTO Commandes (ClientID, DateCommande, MontantTotal, Statut, ReferenceFacture) 
                       VALUES (:client_id, :date_commande, :montant_total, 'LIVREE', :ref_facture)";
    $stmt_commande = $db->prepare($query_commande);
    $stmt_commande->bindParam(':client_id', $client_id);
    $stmt_commande->bindParam(':date_commande', $date_commande);
    $stmt_commande->bindParam(':montant_total', $montant_total);
    $stmt_commande->bindParam(':ref_facture', $ref_facture);
    $stmt_commande->execute();

    // Récupérer l'ID de la nouvelle commande
    $commande_id = $db->lastInsertId();

    // --- ÉTAPE 2: Insertion des Détails et Mise à Jour du Stock ---
    $query_detail = "INSERT INTO DetailsCommande (CommandeID, ProduitID, Quantite, PrixVenteUnitaire, CUMP_Au_Moment_Vente) 
                     VALUES (:commande_id, :produit_id, :quantite, :prix_vente, :cump)";
    $stmt_detail = $db->prepare($query_detail);

    $query_stock = "UPDATE Produits SET StockActuel = StockActuel - :quantite WHERE ProduitID = :produit_id AND StockActuel >= :quantite";
    $stmt_stock = $db->prepare($query_stock);

    foreach ($details as $row) {
        $produit_id = $row['produit_id'];
        $quantite = intval($row['quantite']);
        $prix_vente = floatval($row['prix_vente']);
        $cump = floatval($row['cump']); // CUMP est récupéré du formulaire (issu de la DB)

        if ($quantite <= 0) continue; 
        
        // A) Insertion du Détail
        $stmt_detail->bindParam(':commande_id', $commande_id);
        $stmt_detail->bindParam(':produit_id', $produit_id);
        $stmt_detail->bindParam(':quantite', $quantite);
        $stmt_detail->bindParam(':prix_vente', $prix_vente);
        $stmt_detail->bindParam(':cump', $cump);
        $stmt_detail->execute();

        // B) Mise à jour du Stock
        $stmt_stock->bindParam(':quantite', $quantite);
        $stmt_stock->bindParam(':produit_id', $produit_id);
        
        // S'assurer qu'il y a assez de stock avant de décrémenter
        if ($stmt_stock->execute() === false || $stmt_stock->rowCount() === 0) {
            // Si la mise à jour n'a affecté aucune ligne (stock insuffisant ou ID incorrect)
            throw new Exception("Stock insuffisant pour le Produit ID: " . $produit_id);
        }
    }

    // --- ÉTAPE 3: Validation de la Transaction ---
    $db->commit();
    header('Location: creer.php?action=success&id=' . $commande_id);
    exit;

} catch (Exception $e) {
    // Annulation si une étape a échoué (rollback)
    $db->rollBack();
    $error_msg = urlencode($e->getMessage());
    header('Location: creer.php?action=error&msg=' . $error_msg);
    exit;
}
?>
