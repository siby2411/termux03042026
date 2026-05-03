<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Récupérer les infos du client et de la location avant de valider
    $sql = "SELECT l.*, c.telephone, c.prenom, v.marque, v.modele 
            FROM locations l 
            JOIN clients c ON l.client_id = c.id 
            JOIN voitures v ON l.voiture_id = v.id 
            WHERE l.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
        $conn->query("UPDATE locations SET statut_paiement = 'Payé' WHERE id = $id");
        
        // Préparer le message WhatsApp
        $tel = preg_replace('/[^0-9]/', '', $data['telephone']); // Nettoyer le numéro
        $message = "Bonjour " . $data['prenom'] . ", votre paiement de " . $data['cout_total'] . "€ pour la location de la " . $data['marque'] . " " . $data['modele'] . " a bien été reçu. Merci de votre confiance ! - OMEGA AUTO";
        $url_wa = "https://wa.me/" . $tel . "?text=" . urlencode($message);
        
        // Redirection vers WhatsApp (l'admin valide l'envoi d'un clic)
        header("Location: $url_wa");
        exit();
    }
}
?>
