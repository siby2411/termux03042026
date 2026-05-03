<?php
session_start();
if (!isset($_SESSION['admin_logged'])) { die("Accès refusé"); }
require_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colis_id = $_POST['colis_id'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];
    $position = "$lat,$lng";
    $pdo->prepare("UPDATE colis SET position_gps = ? WHERE id = ?")->execute([$position, $colis_id]);
    $pdo->prepare("INSERT INTO statuts_suivi (colis_id, statut, localisation) VALUES (?, 'position', ?)")->execute([$colis_id, $position]);
    echo "Position mise à jour";
    exit;
}
?>
<form method="post">
    <select name="colis_id">
        <?php
        $cols = $pdo->query("SELECT id, numero_suivi FROM colis")->fetchAll();
        foreach ($cols as $c) echo "<option value='{$c['id']}'>{$c['numero_suivi']}</option>";
        ?>
    </select>
    Latitude : <input type="text" name="latitude" step="any" required><br>
    Longitude : <input type="text" name="longitude" step="any" required><br>
    <button type="submit">Mettre à jour position GPS</button>
</form>
