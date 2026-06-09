<?php
$mdp = 'admin123';
$hash = password_hash($mdp, PASSWORD_DEFAULT);
echo "Copiez ce hash : " . $hash;
?>
