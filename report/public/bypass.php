<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['email'] = 'admin';
$_SESSION['role'] = 'admin';
header('Location: admin_dashboard.php');
exit();
