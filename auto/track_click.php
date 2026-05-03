<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Incrémentation du compteur de clics
    $conn->query("UPDATE voitures SET clics_whatsapp = clics_whatsapp + 1 WHERE id = $id");
    
    // Récupération des infos pour WhatsApp
    $res = $conn->query("SELECT marque, modele, immatriculation, prix_journalier FROM voitures WHERE id = $id");
    $row = $res->fetch_assoc();
    
    $votre_numero = "221776542803";
    $texte = "Bonjour *Omega Auto*, je suis vivement intéressé par :\n\n" .
             "🚗 *Modèle :* " . $row['marque'] . " " . $row['modele'] . "\n" .
             "🆔 *Immatriculation :* " . $row['immatriculation'] . "\n" .
             "💰 *Prix :* " . number_format($row['prix_journalier'], 0, ',', ' ') . " FCFA\n\n" .
             "Est-il toujours disponible ?";
             
    header("Location: https://wa.me/" . $votre_numero . "?text=" . urlencode($texte));
    exit();
}
?>
