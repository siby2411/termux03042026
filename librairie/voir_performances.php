<?php
require_once 'includes/config.php';

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           PERFORMANCES DES CAISSIERS - OMEGA CONSULTING       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$caissiers = $pdo->query("
    SELECT 
        u.id,
        u.username,
        u.nom,
        u.prenom,
        COUNT(v.id) as nb_ventes,
        SUM(v.montant_total) as total_ventes,
        AVG(v.montant_total) as moyenne_vente,
        MAX(v.date_vente) as derniere_vente
    FROM utilisateurs u
    LEFT JOIN ventes v ON u.id = v.utilisateur_id AND v.statut = 'validee'
    WHERE u.role IN ('caissier', 'admin')
    GROUP BY u.id
    ORDER BY total_ventes DESC
")->fetchAll();

foreach($caissiers as $c) {
    $barre = str_repeat("█", min(50, ($c['total_ventes'] / 1000)));
    echo "👤 " . $c['prenom'] . " " . $c['nom'] . " (" . $c['username'] . ")\n";
    echo "   ├─ Ventes: " . ($c['nb_ventes'] ?? 0) . " transactions\n";
    echo "   ├─ CA total: " . number_format($c['total_ventes'] ?? 0, 0, ',', ' ') . " FCFA\n";
    echo "   ├─ Moyenne: " . number_format($c['moyenne_vente'] ?? 0, 0, ',', ' ') . " FCFA/vente\n";
    echo "   └─ Dernière vente: " . ($c['derniere_vente'] ? date('d/m/Y H:i', strtotime($c['derniere_vente'])) : 'Aucune') . "\n";
    echo "   📊 " . $barre . " " . number_format(($c['total_ventes'] ?? 0), 0, ',', ' ') . " FCFA\n\n";
}

// Statistiques globales
$global = $pdo->query("
    SELECT 
        SUM(montant_total) as total,
        COUNT(*) as nb,
        AVG(montant_total) as moyenne
    FROM ventes 
    WHERE statut = 'validee'
")->fetch();

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    STATISTIQUES GLOBALES                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "📊 Total des ventes: " . number_format($global['total'] ?? 0, 0, ',', ' ') . " FCFA\n";
echo "📈 Nombre de ventes: " . ($global['nb'] ?? 0) . "\n";
echo "💰 Panier moyen: " . number_format($global['moyenne'] ?? 0, 0, ',', ' ') . " FCFA\n";
?>
