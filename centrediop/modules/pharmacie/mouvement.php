<?php
// Exemple simplifié de traitement d'un mouvement
$type = $_POST['type']; // 'entree' ou 'sortie'
$produit_id = $_POST['produit_id'];
$quantite = $_POST['quantite'];

// Ici, vous ajouteriez la requête SQL :
// UPDATE stock SET quantite = quantite + $quantite WHERE id = $produit_id (si entrée)
// UPDATE stock SET quantite = quantite - $quantite WHERE id = $produit_id (si sortie)

echo "Mouvement $type enregistré avec succès pour le produit $produit_id.";
?>
