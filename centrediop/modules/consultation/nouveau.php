<?php
require_once '../../includes/db.php';
// Récupérer la liste des patients pour le menu déroulant
$patients = $conn->query("SELECT id, nom, prenom FROM patients");
?>
<form action="save_consultation.php" method="POST" class="p-4 bg-white shadow-sm">
    <div class="mb-3">
        <label>Sélectionner le patient :</label>
        <select name="patient_id" class="form-control" required>
            <?php while($p = $patients->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>"><?php echo $p['nom'] . ' ' . $p['prenom']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label>Motif de la consultation :</label>
        <textarea name="motif" class="form-control" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Enregistrer la consultation</button>
</form>
