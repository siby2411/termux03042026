<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$programmes_pro = [
    'GL' => ['Architecture Logicielle', 'Développement Mobile', 'DevOps & CI/CD', 'IA & Machine Learning'],
    'MG' => ['Gestion des Ressources Humaines', 'Entrepreneuriat & Innovation', 'Soft Skills'],
    'MK' => ['Marketing d\'Influence', 'UX/UI Design', 'Stratégie Content Marketing'],
    'FI' => ['Analyse Financière Avancée', 'Contrôle de Gestion', 'Audit Interne & Conformité'],
    'RS' => ['Cloud Security', 'Administration Serveurs', 'Virtualisation & Docker']
];

foreach ($programmes_pro as $code => $uvs) {
    foreach ($uvs as $uv_nom) {
        // Utilisation de prepare pour l'UV
        $stmt = $conn->prepare("INSERT IGNORE INTO uvs (nom_uv) VALUES (?)");
        $stmt->bind_param("s", $uv_nom);
        $stmt->execute();
        
        $stmt = $conn->prepare("SELECT id FROM uvs WHERE nom_uv = ?");
        $stmt->bind_param("s", $uv_nom);
        $stmt->execute();
        $uv = $stmt->get_result()->fetch_assoc();
        
        if ($uv) {
            $uv_id = $uv['id'];
            $stmt = $conn->prepare("SELECT id FROM classes WHERE nom_class LIKE ?");
            $like_code = "%-$code%";
            $stmt->bind_param("s", $like_code);
            $stmt->execute();
            $classes = $stmt->get_result();
            
            while ($c = $classes->fetch_assoc()) {
                $cid = $c['id'];
                
                // Insertion sécurisée avec prepare pour éviter les erreurs d'apostrophes
                $insert = $conn->prepare("INSERT IGNORE INTO programme_classe (classe_id, uv_id, semestre) VALUES (?, ?, 'S1')");
                $insert->bind_param("ii", $cid, $uv_id);
                $insert->execute();
            }
        }
    }
}
echo "Traitement finalisé avec succès sans erreurs de syntaxe.\n";
?>
