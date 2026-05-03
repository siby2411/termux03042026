<?php
require_once 'core/Auth.php';
Auth::check();
// Redirection immédiate vers le vrai Dashboard structuré
header('Location: /modules/dashboard/index.php');
exit();
