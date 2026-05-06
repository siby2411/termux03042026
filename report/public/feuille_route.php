<?php
$page_title = "Feuille de route - Évolutions";
require_once 'inc_navbar.php';
?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5><i class="bi bi-roadmap"></i> Feuille de route - Évolutions à venir</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-info">
                    <strong>📈 Version 2.0 - Court terme</strong>
                    <ul class="mt-2">
                        <li>Export PDF des états financiers</li>
                        <li>Import CSV des relevés bancaires</li>
                        <li>Génération automatique des factures</li>
                        <li>Dashboard avec graphiques dynamiques</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success">
                    <strong>🚀 Version 3.0 - Moyen terme</strong>
                    <ul class="mt-2">
                        <li>Multi-utilisateurs avec permissions</li>
                        <li>Gestion de la TVA (EDI)</li>
                        <li>Déclaration fiscale automatique</li>
                        <li>API de connexion avec banques</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
