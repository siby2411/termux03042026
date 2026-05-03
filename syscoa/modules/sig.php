<?php
// modules/sig.php - Soldes Intermédiaires de Gestion
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">
            <i class="fas fa-chart-bar me-2"></i>Soldes Intermédiaires de Gestion (SIG)
        </h2>
        <div>
            <button class="btn btn-primary" onclick="calculateSIG()">
                <i class="fas fa-calculator me-1"></i>Calculer
            </button>
            <button class="btn btn-outline-secondary" onclick="printSIG()">
                <i class="fas fa-print me-1"></i>Imprimer
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Calcul des SIG</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>SOLDES INTERMÉDIAIRES DE GESTION</th>
                            <th class="text-end">Montant (FCFA)</th>
                            <th class="text-end">% du CA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-primary">
                            <td><strong>1. MARGE COMMERCIALE</strong></td>
                            <td class="text-end fw-bold" id="marge-commerciale">0</td>
                            <td class="text-end" id="marge-pourcentage">0%</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Chiffre d'affaires net</td>
                            <td class="text-end"><input type="number" class="form-control form-control-sm text-end" id="ca-net" value="0" onchange="updateSIG()"></td>
                            <td class="text-end">100%</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 30px;">Achats de marchandises</td>
                            <td class="text-end"><input type="number" class="form-control form-control-sm text-end" id="achats" value="0" onchange="updateSIG()"></td>
                            <td class="text-end" id="achats-pourcentage">0%</td>
                        </tr>

                        <tr class="table-primary">
                            <td><strong>2. PRODUCTION DE L'EXERCICE</strong></td>
                            <td class="text-end fw-bold" id="production">0</td>
                            <td class="text-end" id="production-pourcentage">0%</td>
                        </tr>

                        <tr class="table-primary">
                            <td><strong>3. VALEUR AJOUTÉE</strong></td>
                            <td class="text-end fw-bold" id="valeur-ajoutee">0</td>
                            <td class="text-end" id="valeur-ajoutee-pourcentage">0%</td>
                        </tr>

                        <tr class="table-success">
                            <td><strong>4. EXCÉDENT BRUT D'EXPLOITATION (EBE)</strong></td>
                            <td class="text-end fw-bold" id="ebe">0</td>
                            <td class="text-end" id="ebe-pourcentage">0%</td>
                        </tr>

                        <tr class="table-success">
                            <td><strong>5. RÉSULTAT D'EXPLOITATION</strong></td>
                            <td class="text-end fw-bold" id="resultat-exploitation">0</td>
                            <td class="text-end" id="resultat-exploitation-pourcentage">0%</td>
                        </tr>

                        <tr class="table-info">
                            <td><strong>6. RÉSULTAT COURANT AVANT IMPÔTS</strong></td>
                            <td class="text-end fw-bold" id="resultat-courant">0</td>
                            <td class="text-end" id="resultat-courant-pourcentage">0%</td>
                        </tr>

                        <tr class="table-warning">
                            <td><strong>7. RÉSULTAT NET</strong></td>
                            <td class="text-end fw-bold" id="resultat-net">0</td>
                            <td class="text-end" id="resultat-net-pourcentage">0%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <h6><i class="fas fa-chart-pie me-2"></i>Visualisation des SIG</h6>
                <canvas id="sigChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function updateSIG() {
    // Récupérer les valeurs
    const caNet = parseFloat(document.getElementById('ca-net').value) || 0;
    const achats = parseFloat(document.getElementById('achats').value) || 0;
    
    // Calculer les SIG
    const margeCommerciale = caNet - achats;
    const pourcentageMarge = caNet > 0 ? (margeCommerciale / caNet * 100).toFixed(1) : 0;
    
    // Mettre à jour l'affichage
    document.getElementById('marge-commerciale').textContent = 
        margeCommerciale.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('marge-pourcentage').textContent = pourcentageMarge + '%';
    document.getElementById('achats-pourcentage').textContent = 
        caNet > 0 ? ((achats / caNet * 100).toFixed(1) + '%') : '0%';
    
    // Mettre à jour le graphique
    updateChart();
}

function calculateSIG() {
    // Simuler des calculs automatiques
    document.getElementById('ca-net').value = 62400000;
    document.getElementById('achats').value = 46550000;
    updateSIG();
    
    // Remplir les autres valeurs
    document.getElementById('production').textContent = '8 750 000 FCFA';
    document.getElementById('production-pourcentage').textContent = '14.0%';
    document.getElementById('valeur-ajoutee').textContent = '24 600 000 FCFA';
    document.getElementById('valeur-ajoutee-pourcentage').textContent = '39.4%';
    document.getElementById('ebe').textContent = '18 250 000 FCFA';
    document.getElementById('ebe-pourcentage').textContent = '29.2%';
    document.getElementById('resultat-exploitation').textContent = '15 800 000 FCFA';
    document.getElementById('resultat-exploitation-pourcentage').textContent = '25.3%';
    document.getElementById('resultat-courant').textContent = '14 200 000 FCFA';
    document.getElementById('resultat-courant-pourcentage').textContent = '22.8%';
    document.getElementById('resultat-net').textContent = '9 850 000 FCFA';
    document.getElementById('resultat-net-pourcentage').textContent = '15.8%';
    
    alert('SIG calculés automatiquement avec des données de démonstration!');
}

function printSIG() {
    window.print();
}

let sigChart;
function updateChart() {
    const ctx = document.getElementById('sigChart').getContext('2d');
    
    if (sigChart) {
        sigChart.destroy();
    }
    
    const caNet = parseFloat(document.getElementById('ca-net').value) || 0;
    const achats = parseFloat(document.getElementById('achats').value) || 0;
    const margeCommerciale = caNet - achats;
    
    sigChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Marge commerciale', 'Achats'],
            datasets: [{
                data: [margeCommerciale, achats],
                backgroundColor: ['#4e73df', '#e74a3b']
     }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Initialiser le graphique
document.addEventListener('DOMContentLoaded', function() {
    updateChart();
});
</script>
