<?php
include 'db_connect.php';

$clients = $conn->query("SELECT id, nom FROM clients");
$voitures = $conn->query("SELECT id, marque, modele FROM voitures WHERE statut='Disponible' AND type_usage='Location'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $c_id = $_POST['client_id'];
    $v_id = $_POST['voiture_id'];
    $debut = $_POST['date_debut'];
    $fin = $_POST['date_fin'];
    $total = $_POST['cout_total'];

    $sql = "INSERT INTO locations (client_id, voiture_id, date_debut, date_fin, cout_total, statut, statut_paiement) 
            VALUES ('$c_id', '$v_id', '$debut', '$fin', '$total', 'En cours', 'En attente')";
    
    if($conn->query($sql)) {
        $conn->query("UPDATE voitures SET statut='Loué' WHERE id='$v_id'");
        header("Location: liste_locations.php");
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Nouvelle Location</title></head>
<body style="font-family: sans-serif; padding: 50px; background: #f8fafc;">
    <div style="max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 12px;">
        <h2>🔑 Nouvelle Location</h2>
        <form method="POST">
            <label>Client :</label>
            <select name="client_id" style="width:100%; padding:10px; margin:10px 0;">
                <?php while($c = $clients->fetch_assoc()) echo "<option value='".$c['id']."'>".$c['nom']."</option>"; ?>
            </select>
            <label>Véhicule :</label>
            <select name="voiture_id" style="width:100%; padding:10px; margin:10px 0;">
                <?php while($v = $voitures->fetch_assoc()) echo "<option value='".$v['id']."'>".$v['marque']." ".$v['modele']."</option>"; ?>
            </select>
            <input type="date" name="date_debut" placeholder="Début" style="width:100%; padding:10px; margin:10px 0;" required>
            <input type="date" name="date_fin" placeholder="Fin" style="width:100%; padding:10px; margin:10px 0;" required>
            <input type="number" name="cout_total" placeholder="Montant Total FCFA" style="width:100%; padding:10px; margin:10px 0;" required>
            <button type="submit" style="width:100%; padding:15px; background: #2563eb; color:white; border:none; border-radius:8px;">Créer le contrat</button>
        </form>
    </div>
</body>
</html>
