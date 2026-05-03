<?php
// modules/paiements/ajax_get_by_code.php
require_once '../../config/database.php';
require_once '../../includes/codes_manager.php';

$database = new Database();
$db = $database->getConnection();
$code = $_GET['code'] ?? '';

$type = validateCode($code);
$response = ['success' => false];

if($type === 'parent') {
    $parent = findParentByCode($code, $db);
    if($parent) {
        $stmt = $db->prepare("SELECT id_eleve, nom_eleve, prenom_eleve, code_eleve FROM eleves WHERE id_parent = ?");
        $stmt->execute([$parent['id_parent']]);
        $eleves = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true,
            'type' => 'parent',
            'id_parent' => $parent['id_parent'],
            'nom_parent' => $parent['prenom'] . ' ' . $parent['nom'],
            'telephone' => $parent['telephone'],
            'eleves' => $eleves
        ];
    }
} elseif($type === 'eleve') {
    $eleve = findEleveByCode($code, $db);
    if($eleve) {
        $response = [
            'success' => true,
            'type' => 'eleve',
            'id_eleve' => $eleve['id_eleve'],
            'nom_eleve' => $eleve['prenom_eleve'] . ' ' . $eleve['nom_eleve'],
            'telephone_parent' => $eleve['telephone'],
            'code_parent' => $eleve['code_parent']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
