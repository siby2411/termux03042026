<?php
require_once 'db_connect.php';

// Nettoyage des anciennes données
$pdo->exec("DELETE FROM statuts_suivi");
$pdo->exec("DELETE FROM notifications_whatsapp");
$pdo->exec("DELETE FROM colis");
$pdo->exec("DELETE FROM produits");
$pdo->exec("DELETE FROM vols");
$pdo->exec("DELETE FROM clients");

// Clients
$clients = [
    ['Dieynaba Keita', '+33758686348', 'dieynaba@example.com', 'Paris, France', 'both'],
    ['Moussa Diop', '+221771234567', 'moussa@example.com', 'Dakar, Sénégal', 'expediteur'],
    ['Fatou Sow', '+33712345678', 'fatou@example.com', 'Lyon, France', 'destinataire'],
    ['Amadou Ba', '+221776543210', 'amadou@example.com', 'Thiès, Sénégal', 'expediteur'],
    ['Claire Martin', '+33698765432', 'claire@example.com', 'Marseille, France', 'destinataire']
];
$stmt = $pdo->prepare("INSERT INTO clients (nom, telephone, email, adresse, type) VALUES (?,?,?,?,?)");
foreach ($clients as $c) $stmt->execute($c);
echo "Clients ajoutés.\n";

// Produits sénégalais (huile de palme, crevettes, etc.)
$produits = [
    ['Huile de palme rouge (1L)', 'Huile naturelle issue de palme, idéale pour cuisine ouest-africaine', 8.50, 15, 'assets/images/huile_palme.jpg'],
    ['Crevettes séchées (200g)', 'Crevettes séchées artisanales, parfumées', 12.90, 8, 'assets/images/crevettes_sechees.jpg'],
    ['Soupe kandia (Gombo)', 'Mélange d\'épices pour soupe kandia (gombo)', 5.50, 30, 'assets/images/kandia.jpg'],
    ['Thé à la menthe (Sénégal)', 'Thé vert importé, menthe fraîche', 6.00, 20, 'assets/images/the_menthe.jpg'],
    ['Beurre de karité pur', 'Beurre de karité bio, 100g', 9.00, 12, 'assets/images/beurre_karite.jpg'],
    ['Attiéké (500g)', 'Semoule de manioc, spécialité ivoirienne très prisée', 4.90, 25, 'assets/images/attieke.jpg'],
    ['Miel de Casamance (250g)', 'Miel sauvage, récolté en forêt', 11.50, 10, 'assets/images/miel_casamance.jpg']
];
$stmt = $pdo->prepare("INSERT INTO produits (nom, description, prix, stock, image) VALUES (?,?,?,?,?)");
foreach ($produits as $p) $stmt->execute($p);
echo "Produits ajoutés.\n";

// Vols Sénégal - France
$vols = [
    ['SN202', 'Dakar', 'Paris', '2026-05-10 08:00:00', '2026-05-10 14:30:00', 'planifie'],
    ['AF823', 'Dakar', 'Lyon', '2026-05-12 10:30:00', '2026-05-12 16:00:00', 'planifie'],
    ['SN405', 'Paris', 'Dakar', '2026-06-01 20:00:00', '2026-06-02 03:00:00', 'planifie'],
];
$stmt = $pdo->prepare("INSERT INTO vols (numero_vol, depart_ville, arrivee_ville, date_depart, date_arrivee_estimee, statut) VALUES (?,?,?,?,?,?)");
foreach ($vols as $v) $stmt->execute($v);
echo "Vols ajoutés.\n";

// Colis (les triggers génèrent les numeros_suivi automatiquement)
$colis = [
    [2, 3, 1, 'Ordinateur portable', 2.5, 'enregistre'],
    [2, 1, 2, 'Vêtements', 5.0, 'depart'],
    [4, 5, 1, 'Nourriture (conserves)', 10.0, 'transit'],
    [2, 3, 2, 'Tissus', 3.0, 'arrivee'],
    [4, 1, 1, 'Matériel électronique', 1.8, 'livre']
];
$stmt = $pdo->prepare("INSERT INTO colis (client_expediteur_id, client_destinataire_id, vol_id, description, poids_kg, statut) VALUES (?,?,?,?,?,?)");
foreach ($colis as $c) $stmt->execute($c);
echo "Colis ajoutés.\n";

// Récupérer les IDs des colis pour ajouter des historiques
$colis_ids = $pdo->query("SELECT id FROM colis")->fetchAll(PDO::FETCH_COLUMN);
foreach ($colis_ids as $cid) {
    $pdo->prepare("INSERT INTO statuts_suivi (colis_id, statut, localisation) VALUES (?, 'enregistre', 'Dakar Sénégal')")->execute([$cid]);
    // quelques mises à jour
    $pdo->prepare("UPDATE colis SET derniere_mise_a_jour = NOW() WHERE id = ?")->execute([$cid]);
}
echo "Historiques ajoutés.\n";
echo "Peuplement terminé !\n";
