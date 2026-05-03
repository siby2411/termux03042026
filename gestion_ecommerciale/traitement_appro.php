<?php
// Fichier : traitement_appro.php

session_start();
include_once 'db_connect.php';

// Vérification de la session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $conn = db_connect();
    
    $id_produit = intval($_POST['id_produit']);
    $quantite_entree = intval($_POST['quantite_entree']);
    $id_vendeur = intval($_POST['id_vendeur']); // Id du vendeur en session

    if ($id_produit > 0 && $quantite_entree > 0) {
        
        // Insertion dans la table 'approvisionnements'
        // Le TRIGGER 'trig_appro_stock' s'occupe de mettre à jour la table 'produits'
        $sql = "INSERT INTO approvisionnements (id_produit, quantite_entree, id_vendeur) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $id_produit, $quantite_entree, $id_vendeur);

        if ($stmt->execute()) {
            // Récupérer le stock actuel pour le message (Optionnel)
            $stock_actuel = $conn->query("SELECT stock_actuel FROM produits WHERE id_produit = $id_produit")->fetch_assoc()['stock_actuel'];
            $_SESSION['message'] = "Approvisionnement enregistré. Stock mis à jour : " . $stock_actuel;
        } else {
            $_SESSION['message'] = "Erreur lors de l'enregistrement de l'approvisionnement: " . $conn->error;
        }

    } else {
        $_SESSION['message'] = "Erreur : Données d'approvisionnement invalides.";
    }

    $conn->close();
}

header("Location: crud_appro.php");
exit();
?>
