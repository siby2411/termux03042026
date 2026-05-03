<?php
/**
 * Vérifie le seuil de stock et envoie une alerte interne si nécessaire
 */
function verifierAlerteStock($pdo, $produit_id) {
    // 1. Récupérer les infos du produit
    $stmt = $pdo->prepare("SELECT designation, stock_actuel, seuil_alerte FROM produits WHERE id = ?");
    $stmt->execute([$produit_id]);
    $p = $stmt->fetch();

    if ($p && $p['stock_actuel'] <= $p['seuil_alerte']) {
        $id_service_achats = 5; // ID du service Achats
        $robot_id = 1; // ID de l'administrateur ou compte système
        
        $contenu = "⚠️ ALERTE STOCK CRITIQUE : Le produit '" . $p['designation'] . "' est à " . $p['stock_actuel'] . " unités (Seuil: " . $p['seuil_alerte'] . "). Veuillez prévoir un réapprovisionnement.";

        // 2. Vérifier si une alerte identique n'a pas déjà été envoyée aujourd'hui (pour éviter le spam)
        $check = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE service_dest_id = ? AND contenu LIKE ? AND date_envoi > DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $check->execute([$id_service_achats, "%".$p['designation']."%"]);
        
        if ($check->fetchColumn() == 0) {
            // 3. Envoi du message interne
            $ins = $pdo->prepare("INSERT INTO messages (expediteur_id, service_dest_id, contenu) VALUES (?, ?, ?)");
            $ins->execute([$robot_id, $id_service_achats, $contenu]);
        }
    }
}
?>

/**
 * Enregistre une action dans le Journal de Bord
 */
function logAction($pdo, $action, $description) {
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO audit_trail (user_id, action_type, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $description, $ip]);
}
