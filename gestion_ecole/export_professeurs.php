<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// On récupère les mêmes filtres que la recherche
$search_name = $_GET['nom'] ?? '';
$search_classe = $_GET['classe_id'] ?? '';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Rapport_Professeurs_'.date('Y-m-d').'.csv');

$output = fopen('php://output', 'w');
// Entête du fichier Excel (CSV)
fputcsv($output, array('Nom', 'Prenom', 'Code Prof', 'Specialite', 'Classe', 'Matiere', 'VH Estime (h)'));

$sql = "SELECT p.nom, p.prenom, p.id_prof_code, p.specialite, c.nom_classe, m.nom_matiere, (uv.coefficient * 10) as vh
        FROM professeurs p
        LEFT JOIN affectation_matieres am ON p.id_prof = am.id_prof
        LEFT JOIN classes c ON am.id_classe = c.id_classe
        LEFT JOIN matieres m ON am.id_matiere = m.id_matiere
        LEFT JOIN unites_valeur uv ON (m.id_matiere = uv.matiere_id AND c.id_classe = uv.classe_id)
        WHERE (p.nom LIKE ? OR p.id_prof_code LIKE ?)";

if (!empty($search_classe)) { $sql .= " AND c.id_classe = " . intval($search_classe); }

$stmt = $conn->prepare($sql);
$term = "%$search_name%";
$stmt->bind_param("ss", $term, $term);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>
