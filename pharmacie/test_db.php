<?php
require_once 'core/Database.php';
try {
    $res = Database::query("SELECT DATABASE()");
    echo "✅ Connexion réussie à la base : " . $res[0]['DATABASE()'];
} catch (Exception $e) {
    echo "❌ Échec : " . $e->getMessage();
}
