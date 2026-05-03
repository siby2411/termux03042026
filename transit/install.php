<?php
// Fichier d'installation initiale
require_once 'config.php';

echo "<h1>Installation d'OMEGA TRANSIT</h1>";

// Créer utilisateur admin par défaut
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO utilisateurs (nom, email, password, role, telephone) 
        VALUES ('Administrateur', 'admin@omegatransit.sn', ?, 'admin', '+221777777777')
        ON DUPLICATE KEY UPDATE id=id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$admin_password]);

echo "<p>✓ Utilisateur admin créé (email: admin@omegatransit.sn, mot de passe: admin123)</p>";

// Ajouter quelques tarifs par défaut
$tarifs = [
    ['DEC-001', 'Déclaration en douane - Maritime', 'Déclaration', 50000],
    ['DEC-002', 'Déclaration en douane - Aérien', 'Déclaration', 45000],
    ['TRANS-001', 'Transit interne', 'Opération', 25000],
    ['STOCK-001', 'Stockage par jour', 'Jour', 5000],
];

foreach($tarifs as $tarif) {
    $sql = "INSERT INTO tarifs_prestations (code_prestation, libelle, unite, prix_unitaire) 
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE libelle=libelle";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($tarif);
}

echo "<p>✓ Tarifs de base ajoutés</p>";

echo "<h3>Installation terminée !</h3>";
echo "<a href='index.php' class='btn btn-primary'>Accéder à l'application</a>";
