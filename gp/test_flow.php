<?php
require_once 'db_connect.php';
include('header.php');
?>

<h2>🧪 Test du flux complet - Dieynaba GP Holding</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">1. Vérification des données</div>
            <div class="card-body">
                <?php
                $entites = $pdo->query("SELECT * FROM entites")->fetchAll();
                echo "<h5>🏢 Entités :</h5><ul>";
                foreach ($entites as $e) echo "<li>{$e['nom']} - {$e['ville']} ({$e['pays']})</li>";
                echo "</ul>";
                
                $colis = $pdo->query("SELECT c.*, e1.nom as origine, e2.nom as destination FROM colis c JOIN entites e1 ON c.entite_origine_id = e1.id JOIN entites e2 ON c.entite_destination_id = e2.id LIMIT 5")->fetchAll();
                echo "<h5>📦 Derniers colis :</h5><ul>";
                foreach ($colis as $c) echo "<li>{$c['numero_suivi']} - {$c['description']}<br>→ {$c['origine']} → {$c['destination']} (statut: {$c['statut']})</li>";
                echo "</ul>";
                ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">2. États financiers</div>
            <div class="card-body">
                <?php
                $paris_dakar = $pdo->query("SELECT SUM(montant_total) as total FROM operations_financieres o JOIN colis c ON o.colis_id = c.id WHERE c.sens = 'paris_dakar'")->fetch();
                $dakar_paris = $pdo->query("SELECT SUM(montant_total) as total FROM operations_financieres o JOIN colis c ON o.colis_id = c.id WHERE c.sens = 'dakar_paris'")->fetch();
                $total_charges = $pdo->query("SELECT SUM(montant) FROM charges")->fetchColumn();
                ?>
                <p><strong>Paris → Dakar :</strong> <?= number_format($paris_dakar['total'] ?? 0, 2) ?> €</p>
                <p><strong>Dakar → Paris :</strong> <?= number_format($dakar_paris['total'] ?? 0, 2) ?> €</p>
                <p><strong>Total charges :</strong> <?= number_format($total_charges ?? 0, 2) ?> €</p>
                <p><strong>Bénéfice net :</strong> <?= number_format(($paris_dakar['total'] + $dakar_paris['total'] - $total_charges), 2) ?> €</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-info text-white">3. URLs de test</div>
    <div class="card-body">
        <ul>
            <li><a href="creer_colis_holding.php">📝 Créer un colis (bi-directionnel)</a></li>
            <li><a href="etats_financiers.php">📊 États financiers</a></li>
            <li><a href="requetes_livraisons.php">🔍 Requêtes livraisons</a></li>
            <li><a href="gestion_geolocalisation.php">🗺️ Géolocalisation</a></li>
            <li><a href="gestion_charges.php">💰 Gestion des charges</a></li>
            <li><a href="suivi.php?numero=<?= $colis[0]['numero_suivi'] ?? '' ?>">📍 Suivi colis (exemple)</a></li>
        </ul>
    </div>
</div>

<?php include('footer.php'); ?>
