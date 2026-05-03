<?php
session_start();
include_once 'db_connect.php';
$conn = db_connect();

if ($_POST['action'] == 'creer_facture') {
    $id_client = $_POST['id_client'];
    $id_vendeur = $_SESSION['id_vendeur'];
    
    // 1. Créer l'entête de facture (si vous avez une table factures)
    // Ici on simule le traitement des produits envoyés par le formulaire
    
    foreach ($_POST['produits'] as $id_p => $details) {
        $qte = $details['quantite'];
        
        // 2. Vérifier le stock
        $res = $conn->query("SELECT stock_actuel, designation FROM produits WHERE id_produit = $id_p");
        $p = $res->fetch_assoc();
        
        if ($p['stock_actuel'] >= $qte) {
            // 3. Soustraire du stock
            $conn->query("UPDATE produits SET stock_actuel = stock_actuel - $qte WHERE id_produit = $id_p");
        } else {
            $_SESSION['message'] = "Erreur : Stock insuffisant pour " . $p['designation'];
            header("Location: facturation.php");
            exit();
        }
    }
    
    $_SESSION['message'] = "Facture enregistrée et stock mis à jour !";
    header("Location: facturation.php");
}
?>
