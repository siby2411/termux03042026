<?php
session_start();
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if (isset($_POST['valider_caisse'])) {
    $date_caisse = $_POST['date_caisse'];
    $admin = "Administrateur Principal"; // Peut être dynamique via $_SESSION

    $stmt = $conn->prepare("INSERT IGNORE INTO clotures_validees (date_cloture, signe_par) VALUES (?, ?)");
    $stmt->bind_param("ss", $date_caisse, $admin);
    
    if ($stmt->execute()) {
        header("Location: cloture_caisse.php?locked=1");
    }
}
?>
