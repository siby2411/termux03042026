<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// 1. Définition des maquettes par code filière (Standard LMD)
// Assurez-vous que les noms d'UVs correspondent à ceux présents dans votre table 'uvs'
$maquettes = [
    'GL' => ['Algo', 'Web', 'Base de Données', 'Python', 'Cloud'],
    'MG' => ['Management', 'Droit', 'Comptabilité', 'Stratégie', 'Leadership'],
    'MK' => ['Marketing', 'Communication', 'SEO', 'Data Marketing', 'Social Media'],
    'FI' => ['Audit', 'Fiscalité', 'Finance', 'Comptabilité Approfondie', 'Gestion'],
    'RS' => ['Réseaux', 'Sécurité Système', 'Linux', 'Cloud Computing', 'Cisco']
];

// 2. Récupérer toutes les classes (ciblant les nouvelles avec ID > 10)
$sql = "SELECT id, nom_class FROM classes WHERE id > 10";
$classes = $conn->query($sql);

if (!$classes) {
    die("Erreur de requête : " . $conn->error);
}

echo "Début de l'affectation automatique...\n";

while ($c = $classes->fetch_assoc()) {
    // Extraction du code filière (ex: L1-GL -> GL)
    $parts = explode('-', $c['nom_class']);
    $code_filiere = $parts[1] ?? '';

    if (isset($maquettes[$code_filiere])) {
        foreach ($maquettes[$code_filiere] as $nom_uv) {
            // Recherche de l'ID de l'UV
            $res = $conn->query("SELECT id FROM uvs WHERE nom_uv = '$nom_uv'");
            $uv = $res->fetch_assoc();
            
            if ($uv) {
                $uv_id = $uv['id'];
                $classe_id = $c['id'];
                
                // Insertion dans la table programme_classe
                $insert = $conn->prepare("INSERT IGNORE INTO programme_classe (classe_id, uv_id) VALUES (?, ?)");
                $insert->bind_param("ii", $classe_id, $uv_id);
                $insert->execute();
                
                echo "Affecté : UV $nom_uv à la classe " . $c['nom_class'] . "\n";
            } else {
                echo "Attention : UV '$nom_uv' non trouvée en base pour la classe " . $c['nom_class'] . "\n";
            }
        }
    }
}
echo "Traitement terminé avec succès.";
?>
