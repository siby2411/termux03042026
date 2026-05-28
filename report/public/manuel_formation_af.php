<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Manuel analyse financière";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="fas fa-graduation-cap"></i> Manuel de formation – Analyse financière</h2>
                </div>
                <div class="card-body">
                    <h3>1. Structure des coûts : Fixes vs Variables</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light"><div class="card-body"><h5>Entreprise A (structure légère)</h5>Coûts variables = 25% du CA<br>Coûts fixes = 750 k€<br>Fort levier opérationnel</div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light"><div class="card-body"><h5>Entreprise B (structure lourde)</h5>Coûts variables = 75% du CA<br>Coûts fixes = 250 k€<br>Seuil de rentabilité bas</div></div>
                        </div>
                    </div>
                    <h3 class="mt-4">2. Levier opérationnel</h3>
                    <p>Levier opérationnel = Marge sur CV / Résultat d'exploitation. Plus élevé, plus l'entreprise est sensible au CA.</p>
                    <h3>3. Seuil de rentabilité</h3>
                    <div class="formula bg-light p-2">SR = Coûts fixes / Taux de marge sur coût variable</div>
                    <h3 class="mt-4">4. Simulateur interactif (entreprise A vs B)</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card"><div class="card-header bg-primary text-white">Entreprise A</div><div class="card-body">
                                <label>CA (k€)</label><input type="number" id="caA" class="form-control" value="1400">
                                <label>Coûts fixes (k€)</label><input type="number" id="cfA" class="form-control" value="750">
                                <label>Coûts variables (k€)</label><input type="number" id="cvA" class="form-control" value="350" oninput="updateTM(1)">
                                <label>Taux marge CV (%)</label><input type="number" id="tmcvA" class="form-control" readonly>
                            </div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card"><div class="card-header bg-secondary text-white">Entreprise B</div><div class="card-body">
                                <label>CA (k€)</label><input type="number" id="caB" class="form-control" value="1600">
                                <label>Coûts fixes (k€)</label><input type="number" id="cfB" class="form-control" value="250">
                                <label>Coûts variables (k€)</label><input type="number" id="cvB" class="form-control" value="1200" oninput="updateTM(2)">
                                <label>Taux marge CV (%)</label><input type="number" id="tmcvB" class="form-control" readonly>
                            </div></div>
                        </div>
                    </div>
                    <div class="text-center mt-3"><button class="btn btn-success" onclick="simuler()">Comparer</button></div>
                    <div id="simuResult" class="mt-3"></div>
                    <script>
                        function updateTM(ent) {
                            let ca = document.getElementById('ca'+(ent==1?'A':'B')).value;
                            let cv = document.getElementById('cv'+(ent==1?'A':'B')).value;
                            let tm = ((ca-cv)/ca*100).toFixed(1);
                            document.getElementById('tmcv'+(ent==1?'A':'B')).value = tm;
                        }
                        function simuler() {
                            let caA=parseFloat(document.getElementById('caA').value), cfA=parseFloat(document.getElementById('cfA').value), tmcvA=parseFloat(document.getElementById('tmcvA').value);
                            let caB=parseFloat(document.getElementById('caB').value), cfB=parseFloat(document.getElementById('cfB').value), tmcvB=parseFloat(document.getElementById('tmcvB').value);
                            let srA = cfA/(tmcvA/100), resA = caA*(tmcvA/100)-cfA;
                            let srB = cfB/(tmcvB/100), resB = caB*(tmcvB/100)-cfB;
                            document.getElementById('simuResult').innerHTML = `<div class='row'><div class='col-md-6'><div class='alert alert-info'>Entreprise A : SR=${srA.toFixed(0)} k€, Résultat=${resA.toFixed(0)} k€</div></div><div class='col-md-6'><div class='alert alert-info'>Entreprise B : SR=${srB.toFixed(0)} k€, Résultat=${resB.toFixed(0)} k€</div></div></div>`;
                        }
                        updateTM(1); updateTM(2);
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
