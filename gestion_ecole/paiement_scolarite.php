<?php 
include 'header_ecole.php'; 
require_once 'db_connect_ecole.php'; 
$conn = db_connect_ecole();
?>

<div class="container mt-4">
    <div class="card shadow p-4">
        <h4><i class="bi bi-cash-stack"></i> Encaisser une Scolarité</h4>
        <form method="POST" action="process_paiement.php">
            <div class="mb-3">
                <label>Sélectionner Étudiant (Code)</label>
                <select name="etudiant_code" id="etudiant_code" class="form-select" onchange="loadMontant()" required>
                    <option value="">Choisir un étudiant...</option>
                    <?php 
                    $res = $conn->query("SELECT code_etudiant, nom, prenom FROM etudiants");
                    while($e = $res->fetch_assoc()) echo "<option value='{$e['code_etudiant']}'>{$e['code_etudiant']} - {$e['nom']} {$e['prenom']}</option>";
                    ?>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Montant Scolarité dû (FCFA)</label>
                    <input type="number" id="montant_du" class="form-control" readonly placeholder="Sélectionnez un étudiant">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Montant à verser (FCFA)</label>
                    <input type="number" name="montant_verse" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Mois concerné</label>
                    <select name="mois" class="form-select" required>
                        <option value="Octobre">Octobre</option><option value="Novembre">Novembre</option>
                        <option value="Decembre">Décembre</option><option value="Janvier">Janvier</option>
                        <option value="Fevrier">Février</option><option value="Mars">Mars</option>
                        <option value="Avril">Avril</option><option value="Mai">Mai</option>
                        <option value="Juin">Juin</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Mode Paiement</label>
                    <select name="mode_paiement" class="form-select">
                        <option value="Espèces">Espèces</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Chèque">Chèque</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>N° Reçu</label>
                    <input type="text" name="recu_numero" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Valider l'encaissement</button>
        </form>
    </div>
</div>

<script>
function loadMontant() {
    let code = document.getElementById('etudiant_code').value;
    if(!code) return;
    fetch('get_etudiant_details.php?code=' + encodeURIComponent(code))
        .then(res => res.json())
        .then(data => {
            document.getElementById('montant_du').value = data.montant_scolarite || 0;
        });
}
</script>
<?php include 'footer_ecole.php'; ?>
