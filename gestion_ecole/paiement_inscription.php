<?php 
include 'header_ecole.php'; 
require_once 'db_connect_ecole.php'; 
$conn = db_connect_ecole();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer le code_etudiant depuis la sélection (on passe le code au lieu de l'ID)
    $code_etudiant = $_POST['code_etudiant']; 
    $montant = $_POST['montant'];
    
    // Insertion adaptée à la structure réelle de votre table
    $sql = "INSERT INTO paiements_inscription (code_etudiant, montant_verse, date_paiement) 
            VALUES ('$code_etudiant', $montant, NOW())";
    
    if ($conn->query($sql)) {
        echo "<div class='container mt-3 alert alert-success'>Paiement enregistré avec succès !</div>";
    } else {
        echo "<div class='container mt-3 alert alert-danger'>Erreur : " . $conn->error . "</div>";
    }
}
?>

<div class="container mt-4">
    <div class="card shadow p-4 col-md-8 mx-auto">
        <h4 class="mb-4">Encaisser Frais d'Inscription</h4>
        <form method="POST">
            <div class="mb-3">
                <label>Sélectionner Étudiant</label>
                <select name="code_etudiant" class="form-control" required>
                    <option value="">Choisir un étudiant...</option>
                    <?php
                    $res = $conn->query("SELECT code_etudiant, nom, prenom FROM etudiants");
                    while ($row = $res->fetch_assoc()) {
                        echo "<option value='{$row['code_etudiant']}'>{$row['code_etudiant']} - {$row['nom']} {$row['prenom']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Montant à verser</label>
                <input type="number" name="montant" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enregistrer le paiement</button>
        </form>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
