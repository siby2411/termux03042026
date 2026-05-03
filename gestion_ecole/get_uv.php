<?php
header('Content-Type: application/json');
$conn = new mysqli("127.0.0.1", "root", "", "ecole");
if ($conn->connect_error) die(json_encode([]));

$matiere_id = isset($_GET['matiere_id']) ? intval($_GET['matiere_id']) : 0;
$data = [];

if ($matiere_id) {
    $stmt = $conn->prepare("SELECT id, nom_uv FROM unites_valeur WHERE matiere_id=? ORDER BY nom_uv");
    $stmt->bind_param("i", $matiere_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
}

$conn->close();
echo json_encode($data);
?>

