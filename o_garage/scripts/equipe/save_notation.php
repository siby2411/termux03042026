<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_personnel'];
    $moyenne = ($_POST['n_tech'] + $_POST['n_assi']) / 2;
    $db->prepare("UPDATE personnel SET note_performance = ?, assiduite = ? WHERE id_personnel = ?")
       ->execute([$moyenne, $_POST['n_assi'], $id]);
    $db->prepare("INSERT INTO notes_performance (id_personnel, mois_annee, note_technique, note_assiduite) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE note_technique=?, note_assiduite=?")
       ->execute([$id, $_POST['periode'], $_POST['n_tech'], $_POST['n_assi'], $_POST['n_tech'], $_POST['n_assi']]);
    header('Location: ../../index.php?noted=true');
}
