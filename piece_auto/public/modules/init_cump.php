<?php
$page_title = "Initialisation Globale CUMP";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$db = (new Database())->getConnection();

try {
    // Requête pour calculer la moyenne des achats par pièce
    $query = "SELECT id_piece, AVG(prix_achat_unitaire) as prix_moyen 
              FROM LIGNES_COMMANDE_ACHAT 
              GROUP BY id_piece";
    $stmt = $db->query($query);
    $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<div class='container mt-5'><h3>Rapport d'initialisation</h3><ul class='list-group'>";
    
    foreach ($updates as $u) {
        $upd = $db->prepare("UPDATE PIECES SET cump = :cump WHERE id_piece = :id AND (cump IS NULL OR cump = 0)");
        $upd->execute([':cump' => $u['prix_moyen'], ':id' => $u['id_piece']]);
        
        echo "<li class='list-group-item'>Pièce ID {$u['id_piece']} : CUMP initialisé à " . number_format($u['prix_moyen'], 2) . " FCFA</li>";
    }
    
    echo "</ul><div class='alert alert-success mt-3'>Initialisation terminée !</div></div>";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}

include '../../includes/footer.php';
?>
