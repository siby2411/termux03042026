<?php
// /commandes/traitement_commande.php
include_once __DIR__ . '/../config/db.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Démarrage de la transaction pour garantir l'intégrité des données (ACID)
    $db->beginTransaction();
    try {
        // 1. Insertion de l'entête de commande
        $query_cmd = "INSERT INTO Commandes (ClientID, DateCommande, ReferenceFacture, MontantTotalHT) 
                      VALUES (:cid, :date, :ref, :total)";
        $stmt = $db->prepare($query_cmd);
        $stmt->execute([
            ':cid' => $_POST['client_id'],
            ':date' => $_POST['date_commande'],
            ':ref' => $_POST['ref_facture'] ?? null,
            ':total' => $_POST['montant_total']
        ]);
        $commande_id = $db->lastInsertId();

        // 2. Traitement des lignes (Détails)
        if (isset($_POST['details']) && is_array($_POST['details'])) {
            foreach ($_POST['details'] as $ligne) {
                $pid = $ligne['produit_id'];
                $qty = (int)$ligne['quantite'];
                $prix = (float)$ligne['prix_vente'];
                $cump = (float)$ligne['cump'];

                // Insertion du détail de la ligne
                $query_det = "INSERT INTO CommandeDetails (CommandeID, ProduitID, Quantite, PrixVenteUnitaire, CUMP_Au_Moment_Vente) 
                              VALUES (?, ?, ?, ?, ?)";
                $db->prepare($query_det)->execute([$commande_id, $pid, $qty, $prix, $cump]);

                // Déduction du Stock Physique
                $query_stock = "UPDATE Produits SET StockActuel = StockActuel - ? WHERE ProduitID = ?";
                $db->prepare($query_stock)->execute([$qty, $pid]);
            }
        }

        $db->commit();
        // Redirection vers le formulaire avec un flag de succès
        header("Location: creer.php?action=success&id=" . $commande_id);
        exit();

    } catch (Exception $e) {
        // En cas d'erreur, on annule tout (pas de commande créée, pas de stock déduit)
        $db->rollBack();
        die("Erreur critique lors de l'enregistrement de la transaction : " . $e->getMessage());
    }
}
?>
