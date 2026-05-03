<?php
$conn = new mysqli("127.0.0.1", "root", "", "ecole");
$filiere_id = intval($_GET['filiere_id']);

$result = $conn->query("SELECT id, nom_matiere, code_matiere FROM matieres WHERE filiere_id = $filiere_id ORDER BY nom_matiere");
echo '<option value="">-- Sélectionner une matière --</option>';
while($row = $result->fetch_assoc()){
    echo "<option value='{$row['id']}'>{$row['nom_matiere']} ({$row['code_matiere']})</option>";
}
?>

