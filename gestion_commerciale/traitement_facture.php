<?php
// Fichier : traitement_facture.php - Logique d'enregistrement transactionnel

session_start();
include_once 'db_connect.php';

if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'creer_document') {
    
    $conn = db_connect();
    
    // Récupération des données du POST
    $id_client = intval($_POST['id_client']);
    $id_vendeur = intval($_POST['id_vendeur']);
    $type_document = $_POST['type_document']; // FACTURE, BL, BC
    $total_facture_final = floatval($_POST['total_facture_final']);
    $produits_lignes = $_POST['produits']; // Tableau des lignes de produits
    
    // Validation minimale des données
    if ($id_client === 0 || empty($produits_lignes)) {
        $_SESSION['message'] = "Erreur: Client ou lignes de produits manquantes.";
        header("Location: facturation.php");
        exit();
    }
    
    // Début de la Transaction
    $conn->begin_transaction();
    $transaction_success = true;
    
    try {
        // 1. Insertion de l'Entête de Facture
        
        // Simuler la génération du numero_facture (Ex: FA-0001, BL-0001)
        // Ceci devrait être géré par une table de numérotation, mais ici nous simulons :
        $numero_base = ($type_document === 'FACTURE') ? 'FA-' : (($type_document === 'BL') ? 'BL-' : 'BC-');
        $result_count = $conn->query("SELECT COUNT(*) AS count FROM factures WHERE type_document = '$type_document'");
        $count = $result_count->fetch_assoc()['count'] + 1;
        $numero_facture = $numero_base . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        $sql_entete = "INSERT INTO factures (numero_facture, id_client, id_vendeur, total_facture, type_document) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_entete = $conn->prepare($sql_entete);
        $stmt_entete->bind_param("siids", $numero_facture, $id_client, $id_vendeur, $total_facture_final, $type_document);
        
        if (!$stmt_entete->execute()) {
            throw new Exception("Erreur d'insertion de l'entête.");
        }
        
        $id_facture = $conn->insert_id; // ID de l'entête nouvellement créé

        // 2. Insertion des Détails de Facture (Lignes de produits)
        $sql_detail = "INSERT INTO details_facture (id_facture, id_produit, quantite, prix_vente) 
                       VALUES (?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($sql_detail);
        
        foreach ($produits_lignes as $ligne) {
            $id_produit = intval($ligne['id_produit']);
            $quantite = intval($ligne['quantite']);
            $prix_vente = floatval($ligne['prix_unitaire']); // Prix utilisé au moment de la vente
            
            if ($id_produit > 0 && $quantite > 0) {
                // Le prix_vente vient du champ caché du formulaire (prix unitaire)
                $stmt_detail->bind_param("iidi", $id_facture, $id_produit, $quantite, $prix_vente);
                
                if (!$stmt_detail->execute()) {
                    throw new Exception("Erreur d'insertion des détails de facture.");
                }
                // NOTE: Le TRIGGER trig_maj_stock se déclenche ici, mettant à jour le stock
            }
        }
        
        // Si tout est OK, valider la transaction
        $conn->commit();
        $_SESSION['message'] = "Document $type_document n° $numero_facture créé avec succès. Le stock a été mis à jour (si ce n'est pas un BC).";
        
        // Redirection vers la page de visualisation du document
        header("Location: detail_document.php?id=$id_facture&type=$type_document");
        exit();

    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction et mettre le message
        $conn->rollback();
        $_SESSION['message'] = "Erreur de Transaction: " . $e->getMessage();
        header("Location: facturation.php");
        exit();
    }
    
    $conn->close();
}

header("Location: facturation.php");
exit();
?>
