<?php include 'header_ecole.php'; require_once 'db_connect_ecole.php'; $conn = db_connect_ecole();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string($_POST['etudiant_code']);
    $montant = floatval($_POST['montant_verse']);
    $res = $conn->query("SELECT id FROM etudiants WHERE code_etudiant = '$code'");
    $etu = $res->fetch_assoc();
    $conn->query("INSERT INTO paiements (etudiant_id, montant, type_paiement, recu) VALUES ({$etu['id']}, $montant, 'Inscription', '{$_POST['recu']}')");
    echo "<div class='alert alert-success'>Inscription validée !</div>";
}
?>
