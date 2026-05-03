<?php
// Fichier : traitement_appro.php

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $conn = db_connect();
    
    // Nettoyage et validation des données d'entrée
    $id_produit = filter_input(INPUT_POST, 'id_produit', FILTER_VALIDATE_INT);
    $quantite_entree = filter_input(INPUT_POST, 'quantite_entree', FILTER_VALIDATE_INT);
    $id_vendeur = filter_input(INPUT_POST, 'id_vendeur', FILTER_VALIDATE_INT); // ID du vendeur en session
    
    // Vérification stricte des données
    if ($id_produit > 0 && $quantite_entree > 0 && $id_vendeur > 0) {
        
        // --- 1. Insertion dans la table 'approvisionnements' ---
        // Utilisation de la requête préparée
        $sql = "INSERT INTO approvisionnements (id_produit, quantite_entree, id_vendeur) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("iii", $id_produit, $quantite_entree, $id_vendeur);

            if ($stmt->execute()) {
                // Succès : Récupérer le stock actuel pour le message (Optionnel, mais utile)
                // Note: La transaction doit être rapide car le stock est mis à jour par le trigger
                $stock_stmt = $conn->prepare("SELECT stock_actuel, designation FROM produits WHERE id_produit = ?");
                $stock_stmt->bind_param("i", $id_produit);
                $stock_stmt->execute();
                $result_stock = $stock_stmt->get_result()->fetch_assoc();
                $stock_stmt->close();
                
                $designation = htmlspecialchars($result_stock['designation'] ?? 'Produit Inconnu');
                $stock_actuel = $result_stock['stock_actuel'] ?? 'N/A';
                
                $_SESSION['message'] = "✅ Approvisionnement de **$quantite_entree** pour **$designation** enregistré. Nouveau stock : **$stock_actuel**";
            } else {
                $_SESSION['message'] = "❌ Erreur lors de l'enregistrement de l'approvisionnement : " . $stmt->error;
            }
            $stmt->close();
        } else {
             $_SESSION['message'] = "❌ Erreur de préparation de la requête (Insertion Appro) : " . $conn->error;
        }

    } else {
        $_SESSION['message'] = "❌ Erreur : Données d'approvisionnement invalides ou manquantes.";
    }

    $conn->close();
}

// Redirection vers le formulaire
header("Location: crud_appro.php");
exit();
?>
