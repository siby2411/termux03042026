<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Immobilisations";
$page_icon = "building";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-building"></i> Module : Les Immobilisations (Classe 2)</h5>
                <small>Classification et comptabilisation des actifs durables</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 DÉFINITION :</strong>
                    <p>Une immobilisation est un bien durable détenu par l'entreprise pour une utilisation durable (plus d'un exercice).</p>
                </div>
                
                <!-- Classification -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-primary text-white">
                                <h6>🏗️ Types d'immobilisations</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>21</strong> - Immobilisations incorporelles (brevets, licences)</li>
                                    <li><strong>22-23</strong> - Terrains et constructions</li>
                                    <li><strong>24-25</strong> - Matériel, outillage, mobilier</li>
                                    <li><strong>26-27</strong> - Immobilisations en cours</li>
                                    <li><strong>28</strong> - Amortissements correspondants</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">
                                <h6>📝 Écritures d'acquisition</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-danger">
                                    <strong>DÉBIT :</strong> Compte d'immobilisation (21-27)
                                </div>
                                <div class="alert alert-success">
                                    <strong>CRÉDIT :</strong> Banque (521) ou Fournisseur (401)
                                </div>
                                <div class="mt-2">
                                    <strong>Cas pratique :</strong> Achat camion 15.000.000 F
                                    <pre class="bg-dark text-white p-2 mt-2 rounded">
Débit : 253 - Véhicules utilitaires ..... 15.000.000
Crédit : 521 - Banque ................... 15.000.000</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cession d'immobilisation -->
                <h5 class="mt-4">🔄 Cession d'immobilisation</h5>
                <div class="alert alert-warning">
                    <strong>Écritures de cession :</strong><br>
                    1. Constater la sortie du bien (valeur brute)<br>
                    2. Constater les amortissements cumulés<br>
                    3. Constater la plus ou moins-value
                </div>
                
                <div class="text-center mt-4">
                    <a href="../immobilisations.php" class="btn btn-primary">Gérer vos immobilisations →</a>
                    <a href="amortissements.php" class="btn btn-warning">Module suivant : Amortissements →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
