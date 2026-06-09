<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_reception'])) {
    $id_commande = intval($_POST['id_commande']);
    
    try {
        // Début de la transaction pour garantir l'intégrité des données
        $db->beginTransaction();

        // 1. Récupérer toutes les lignes de cette commande d'achat
        $stmtLignes = $db->prepare("SELECT id_piece, quantite FROM LIGNES_COMMANDES_ACHAT WHERE id_commande_achat = ?");
        $stmtLignes->execute([$id_commande]);
        $lignes = $stmtLignes->fetchAll(PDO::FETCH_ASSOC);

        if (empty($lignes)) {
            throw new Exception("Aucun article trouvé dans cette commande.");
        }

        // 2. Préparer les requêtes de mise à jour
        // Mise à jour du stock physique
        $updateStock = $db->prepare("UPDATE PIECES SET stock_actuel = stock_actuel + ? WHERE id_piece = ?");
        
        // Enregistrement dans l'historique des mouvements
        $insertMouv = $db->prepare("INSERT INTO MOUVEMENTS_STOCK (id_piece, quantite_impact, type_mouvement, date_mouvement, source_id) VALUES (?, ?, 'Achat', NOW(), ?)");

        // 3. Boucle sur chaque article pour mise à jour
        foreach ($lignes as $ligne) {
            $updateStock->execute([$ligne['quantite'], $ligne['id_piece']]);
            $insertMouv->execute([$ligne['id_piece'], $ligne['quantite'], $id_commande]);
        }

        // 4. Marquer la commande comme "Reçu"
        $stmtUpdateStatut = $db->prepare("UPDATE COMMANDES_ACHAT SET statut = 'Reçu' WHERE id_commande = ?");
        $stmtUpdateStatut->execute([$id_commande]);

        // Validation finale
        $db->commit();
        
        // Redirection vers le stock avec message de succès
        header("Location: gestion_stock.php?msg=Stock_Mis_A_Jour");
        exit();

    } catch (Exception $e) {
        // Annulation en cas d'erreur
        $db->rollBack();
        die("Erreur fatale lors de la réception : " . $e->getMessage());
    }
} else {
    // Si accès direct sans POST
    header("Location: reception_achats.php");
    exit();
}
?>
