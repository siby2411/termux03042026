<?php
require_once '../../includes/db.php';
// On compte simplement les patients en attente
$res = $conn->query("SELECT COUNT(*) as total FROM file_attente WHERE statut = 'attente'");
$data = $res->fetch_assoc();
echo $data['total']; 
