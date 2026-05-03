<?php
session_start();
require_once 'includes/auth.php';

if (isset($_SESSION['user_token'])) {
    $token = getUserToken();
    if ($token) {
        unset($token['patient']);
        $_SESSION['user_token'] = base64_encode(json_encode($token));
    }
}
header('Location: voir_token_actuel.php');
exit();
