<?php
function db_connect_ecole() {
    $host = null; 
    $user = 'root';
    $pass = '';
    $db   = 'ecole';
    $socket = '/var/run/mysqld/mysqld.sock';
    
    $conn = new mysqli($host, $user, $pass, $db, null, $socket);
    if ($conn->connect_error) {
        $conn = new mysqli('127.0.0.1', $user, $pass, $db);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
