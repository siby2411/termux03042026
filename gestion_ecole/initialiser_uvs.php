<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$all_uvs = [
    'Algo', 'Web', 'Base de Données', 'Python', 'Cloud',
    'Management', 'Droit', 'Comptabilité', 'Stratégie', 'Leadership',
    'Marketing', 'Communication', 'SEO', 'Data Marketing', 'Social Media',
    'Audit', 'Fiscalité', 'Finance', 'Comptabilité Approfondie', 'Gestion',
    'Réseaux', 'Sécurité Système', 'Linux', 'Cloud Computing', 'Cisco'
];

foreach ($all_uvs as $nom) {
    $conn->query("INSERT IGNORE INTO uvs (nom_uv) VALUES ('$nom')");
}
echo "Initialisation des UVs terminée.";
?>
