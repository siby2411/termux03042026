<?php require_once '../../includes/header.php'; ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white">Notation & Performance</div>
    <div class="card-body">
        <form action="save_notation.php" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Collaborateur</label>
                    <select name="id_personnel" id="id_personnel" class="form-select" onchange="calculerPrime()">
                        <?php 
                        $db = (new Database())->getConnection();
                        $mecs = $db->query("SELECT id_personnel, nom_complet, salaire_base FROM personnel");
                        while($m = $mecs->fetch()) echo "<option value='{$m['id_personnel']}' data-base='{$m['salaire_base']}'>{$m['nom_complet']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-6"><label>Mois</label><input type="month" name="periode" class="form-control" value="<?=date('Y-m')?>"></div>
                <div class="col-md-6"><label>Note Technique /100</label><input type="number" name="n_tech" id="n_tech" class="form-control" value="80" oninput="calculerPrime()"></div>
                <div class="col-md-6"><label>Assiduité /100</label><input type="number" name="n_assi" id="n_assi" class="form-control" value="100" oninput="calculerPrime()"></div>
                <div class="col-12 mt-3"><h4 class="text-success">Prime estimée : <span id="prime_result">0</span> F</h4></div>
                <button type="submit" class="btn btn-primary">Valider la notation</button>
            </div>
        </form>
    </div>
</div>
<script>
function calculerPrime() {
    const tech = document.getElementById('n_tech').value;
    const assi = document.getElementById('n_assi').value;
    const select = document.getElementById('id_personnel');
    const base = select.options[select.selectedIndex].getAttribute('data-base') || 0;
    let prime = ((parseInt(tech) + parseInt(assi)) / 200) * (base * 0.15);
    document.getElementById('prime_result').innerText = Math.round(prime).toLocaleString();
}
</script>
<?php require_once '../../includes/footer.php'; ?>
