<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Grand Livre Général</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="compte_gl">Compte</label>
                            <select class="form-control" id="compte_gl">
                                <option value="">Tous les comptes</option>
                                <option value="1">1 - Capitaux</option>
                                <option value="2">2 - Immobilisations</option>
                                <option value="3">3 - Stocks</option>
                                <option value="4">4 - Tiers</option>
                                <option value="5">5 - Financier</option>
                                <option value="6">6 - Charges</option>
                                <option value="7">7 - Produits</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exercice_gl">Exercice</label>
                            <select class="form-control" id="exercice_gl">
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            <i class="fas fa-print me-1"></i>Imprimer le grand livre
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th colspan="8" class="text-center">GRAND LIVRE - Exercice 2025</th>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <th>Pièce</th>
                                <th>Journal</th>
                                <th>Compte</th>
                                <th>Libellé</th>
                                <th>Débit</th>
                                <th>Crédit</th>
                                <th>Solde</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Compte 411100 - Clients -->
                            <tr class="table-info">
                                <td colspan="8"><strong>Compte 411100 - Clients</strong></td>
                            </tr>
                            <tr>
                                <td>01/12/2025</td>
                                <td>FA-001</td>
                                <td>Ventes</td>
                                <td>411100</td>
                                <td>Facture Client A</td>
                                <td>500.000</td>
                                <td></td>
                                <td>500.000 D</td>
                            </tr>
                            <tr>
                                <td>15/12/2025</td>
                                <td>RE-045</td>
                                <td>Banque</td>
                                <td>411100</td>
                                <td>Règlement Client A</td>
                                <td></td>
                                <td>300.000</td>
                                <td>200.000 D</td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="5" class="text-end"><strong>Total 411100 :</strong></td>
                                <td><strong>500.000</strong></td>
                                <td><strong>300.000</strong></td>
                                <td><strong>200.000 D</strong></td>
                            </tr>
                            
                            <!-- Compte 512100 - Banque -->
                            <tr class="table-info">
                                <td colspan="8"><strong>Compte 512100 - Banque</strong></td>
                            </tr>
                            <tr>
                                <td>05/12/2025</td>
                                <td>VIR-123</td>
                                <td>Banque</td>
                                <td>512100</td>
                                <td>Virement entrant</td>
                                <td>1.000.000</td>
                                <td></td>
                                <td>1.000.000 D</td>
                            </tr>
                            <tr>
                                <td>10/12/2025</td>
                                <td>CHQ-456</td>
                                <td>Banque</td>
                                <td>512100</td>
                                <td>Paiement fournisseur</td>
                                <td></td>
                                <td>250.000</td>
                                <td>750.000 D</td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="5" class="text-end"><strong>Total 512100 :</strong></td>
                                <td><strong>1.000.000</strong></td>
                                <td><strong>250.000</strong></td>
                                <td><strong>750.000 D</strong></td>
                            </tr>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <td colspan="5" class="text-end"><strong>TOTAUX GÉNÉRAUX :</strong></td>
                                <td><strong>1.500.000</strong></td>
                                <td><strong>550.000</strong></td>
                                <td><strong>950.000 D</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Analyse par classe de comptes</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="classeComptesChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Informations sur le grand livre</h6>
                            </div>
                            <div class="card-body">
                                <p>Le grand livre est le registre principal qui reprend toutes les écritures comptables classées par compte.</p>
                                <p><strong>Structure OHADA :</strong></p>
                                <ul>
                                    <li>Classe 1 : Capitaux</li>
                                    <li>Classe 2 : Immobilisations</li>
                                    <li>Classe 3 : Stocks</li>
                                    <li>Classe 4 : Tiers</li>
                                    <li>Classe 5 : Financier</li>
                                    <li>Classe 6 : Charges</li>
                                    <li>Classe 7 : Produits</li>
                                </ul>
                                <p>Chaque mouvement affecte au moins deux comptes (débit/crédit).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour le graphique des classes de comptes
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('classeComptesChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Capitaux', 'Immobilisations', 'Stocks', 'Tiers', 'Financier', 'Charges', 'Produits'],
            datasets: [{
                data: [15, 25, 10, 20, 10, 35, 30],
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                    '#9966FF', '#FF9F40', '#8AC926'
                ]
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
});
</script>
