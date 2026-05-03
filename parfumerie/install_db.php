<?php
$db = new PDO("mysql:host=localhost", 'root', '');
$db->exec("DROP DATABASE IF EXISTS cosmetique_db");
$db->exec("CREATE DATABASE cosmetique_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->exec("USE cosmetique_db");

$sql = file_get_contents(__DIR__ . '/database/schema.sql');
$db->exec($sql);

echo "✅ Base de données installée avec succès!\n";
echo "📝 Identifiants: admin / Admin@2026\n";
?>
