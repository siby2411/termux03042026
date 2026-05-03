<?php require_once '../../includes/header.php'; ?>
<div class="container bg-white p-5 rounded shadow">
    <h2 class="text-primary border-bottom pb-3"><i class="fas fa-code me-2"></i>Documentation API OMEGA TECH</h2>
    <div class="mt-4">
        <h4>1. Endpoint: Suivi Véhicule</h4>
        <code class="bg-light p-2 d-block">GET /api/vehicule/statut.php?immat=DK-XXXX-AS</code>
        <p class="mt-2 text-muted">Retourne le statut actuel, le kilométrage et les pannes détectées au format JSON.</p>
        
        <h4 class="mt-4">2. Endpoint: Facturation</h4>
        <code class="bg-light p-2 d-block">POST /api/factures/creer.php</code>
        <p class="mt-2 text-muted">Permet de générer une facture à distance depuis une application tierce.</p>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
