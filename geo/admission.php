// Connexion PDO simplifiée
$pdo = new PDO('mysql:host=127.0.0.1;dbname=hopital_db', 'user', 'password');

// Récupération des services pour le menu déroulant
$services = $pdo->query("SELECT * FROM services")->fetchAll();
$medecins = $pdo->query("SELECT * FROM personnel WHERE role='Medecin'")->fetchAll();
?>

<form action="enregistrer_patient.php" method="POST">
    <h3>Admission Patient</h3>
    <input type="text" name="ipp" placeholder="N° IPP" required>
    <input type="text" name="nom" placeholder="Nom" required>
    
    <select name="service_id">
        <?php foreach($services as $s): ?>
            <option value="<?= $s['id'] ?>"><?= $s['nom_service'] ?></option>
        <?php endforeach; ?>
    </select>

    <select name="medecin_id">
        <?php foreach($medecins as $m): ?>
            <option value="<?= $m['id'] ?>">Dr. <?= $m['nom'] ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="chambre" placeholder="N° Chambre">
    <button type="submit">Valider l'Admission</button>
</form>
