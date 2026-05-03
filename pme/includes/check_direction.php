<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'direction') {
    header("Location: index.php?error=accès_refusé_marges_confidentielles");
    exit();
}
?>
