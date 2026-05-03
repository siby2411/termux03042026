<?php
require_once 'includes/db.php';

$pdo = getPDO();

// Insertion des examens
$examens = [
    ['nom' => 'IRM Cérébrale', 'categorie' => 'IRM_CEREBRALE', 'tarif' => 85000, 'duree' => 30, 'description' => 'IRM du cerveau sans injection', 'preparation' => 'Aucune'],
    ['nom' => 'Scanner Thorax', 'categorie' => 'SCANNER_THORAX', 'tarif' => 65000, 'duree' => 15, 'description' => 'Scanner du thorax avec ou sans contraste', 'preparation' => 'Jeûne 4h'],
    ['nom' => 'Radiographie Thorax', 'categorie' => 'RADIO_THORAX', 'tarif' => 15000, 'duree' => 5, 'description' => 'Radio pulmonaire face et profil', 'preparation' => 'Aucune'],
    ['nom' => 'Mammographie', 'categorie' => 'MAMMOGRAPHIE', 'tarif' => 25000, 'duree' => 10, 'description' => 'Mammographie bilatérale', 'preparation' => 'Éviter les déodorants'],
    ['nom' => 'Échographie Abdominale', 'categorie' => 'ECHO_ABDOMEN', 'tarif' => 30000, 'duree' => 20, 'description' => 'Échographie de l\'abdomen', 'preparation' => 'Jeûne 6h'],
];
foreach ($examens as $e) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO examens (nom, categorie, tarif, duree_estimee, description, preparation) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$e['nom'], $e['categorie'], $e['tarif'], $e['duree'], $e['description'], $e['preparation']]);
    echo "Examen ajouté : " . $e['nom'] . "\n";
}

// Insertion équipements
$equipements = [
    ['nom' => 'IRM 1.5T', 'type' => 'IRM', 'marque' => 'Siemens', 'modele' => 'Symphony', 'serie' => 'IRM001', 'acquisition' => '2020-01-01'],
    ['nom' => 'Scanner 128 coupes', 'type' => 'SCANNER', 'marque' => 'GE', 'modele' => 'Revolution', 'serie' => 'SCAN001', 'acquisition' => '2021-03-15'],
    ['nom' => 'Radiologie numérique', 'type' => 'RADIO', 'marque' => 'Philips', 'modele' => 'DigitalDiagnost', 'serie' => 'RAD001', 'acquisition' => '2019-06-20'],
];
foreach ($equipements as $e) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO equipements (nom, type, marque, modele, numero_serie, date_acquisition) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$e['nom'], $e['type'], $e['marque'], $e['modele'], $e['serie'], $e['acquisition']]);
    echo "Équipement ajouté : " . $e['nom'] . "\n";
}

// Admin
$admin_exists = $pdo->query("SELECT id FROM users WHERE username = 'admin'")->fetch();
if (!$admin_exists) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, role, is_active, is_staff, is_superuser) VALUES ('admin', ?, 'admin@cabinet.sn', 'Admin', 'System', 'admin', 1, 1, 1)")->execute([$password]);
    echo "Admin créé (admin/admin123)\n";
}
?>
