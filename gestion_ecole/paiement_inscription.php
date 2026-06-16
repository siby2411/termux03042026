<?php include 'header_ecole.php'; require_once 'db_connect_ecole.php'; $conn = db_connect_ecole(); ?>

<div class="container mt-4">
    <div class="card shadow p-4 col-md-8 mx-auto">
        <h4 class="mb-4">Encaisser Frais d'Inscription</h4>
        <form method="POST" action="process_inscription.php"> <div class="mb-3">
                <label>Sélectionner Étudiant</label>
                <select name="code_etudiant" id="etudiant_code" class="form-control" onchange="loadMontant()" required>
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
                <label>Montant Inscription dû</label>
                <input type="number" id="montant_du" class="form-control" readonly placeholder="Sélectionnez un étudiant">
            </div>
            <div class="mb-3">
                <label>Montant à verser</label>
                <input type="number" name="montant" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enregistrer le paiement</button>
        </form>
    </div>
</div>

<script>
function loadMontant() {
    let code = document.getElementById('etudiant_code').value;
    if(!code) return;
    // On appelle le même script qui sert pour la scolarité
    fetch('get_etudiant_details.php?code=' + encodeURIComponent(code))
        .then(res => res.json())
        .then(data => {
            // Ici, on cible le montant_inscription
            document.getElementById('montant_du').value = data.montant_inscription || 0;
        });
}
</script>
<?php include 'footer_ecole.php'; ?>
