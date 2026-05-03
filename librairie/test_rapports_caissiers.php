<?php
require_once 'includes/config.php';

echo "=== RAPPORTS PAR CAISSIER ===\n\n";

// Récupérer tous les caissiers
$caissiers = $pdo->query("SELECT id, username, nom, prenom FROM utilisateurs WHERE role IN ('caissier', 'admin')")->fetchAll();

foreach($caissiers as $caissier) {
    echo "Caissier: " . $caissier['prenom'] . " " . $caissier['nom'] . " (" . $caissier['username'] . ")\n";
    echo str_repeat("-", 50) . "\n";
    
    // Ventes du jour
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as nb, SUM(montant_total) as total 
        FROM ventes 
        WHERE utilisateur_id = ? AND DATE(date_vente) = CURDATE() AND statut = 'validee'
    ");
    $stmt->execute([$caissier['id']]);
    $jour = $stmt->fetch();
    
    // Ventes du mois
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as nb, SUM(montant_total) as total 
        FROM ventes 
        WHERE utilisateur_id = ? AND MONTH(date_vente) = MONTH(CURDATE()) AND YEAR(date_vente) = YEAR(CURDATE()) AND statut = 'validee'
    ");
    $stmt->execute([$caissier['id']]);
    $mois = $stmt->fetch();
    
    echo "Aujourd'hui: " . ($jour['nb'] ?? 0) . " ventes - " . number_format($jour['total'] ?? 0, 0, ',', ' ') . " FCFA\n";
    echo "Ce mois: " . ($mois['nb'] ?? 0) . " ventes - " . number_format($mois['total'] ?? 0, 0, ',', ' ') . " FCFA\n";
    
    // Dernières ventes
    $stmt = $pdo->prepare("
        SELECT numero_facture, montant_total, date_vente 
        FROM ventes 
        WHERE utilisateur_id = ? AND statut = 'validee'
        ORDER BY date_vente DESC 
        LIMIT 3
    ");
    $stmt->execute([$caissier['id']]);
    $dernieres = $stmt->fetchAll();
    
    echo "Dernières ventes:\n";
    foreach($dernieres as $vente) {
        echo "  - " . $vente['numero_facture'] . ": " . number_format($vente['montant_total'], 0, ',', ' ') . " FCFA (" . date('d/m/Y H:i', strtotime($vente['date_vente'])) . ")\n";
    }
    echo "\n";
}
?>
