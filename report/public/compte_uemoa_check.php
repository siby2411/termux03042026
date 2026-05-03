<?php
require __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$intitule = trim($_POST['intitule_compte'] ?? '');
$response = ['exists' => false];

if($intitule !== ''){
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM PLAN_COMPTABLE_UEMOA WHERE intitule_compte = ?");
    $stmt->execute([$intitule]);
    if($stmt->fetchColumn() > 0){
        $response['exists'] = true;
    }
}
echo json_encode($response);

