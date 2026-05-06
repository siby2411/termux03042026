<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_expert.php");
} else {
    header("Location: test_login.php");
}
exit();
?>
