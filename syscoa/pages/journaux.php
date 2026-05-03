<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-book me-2"></i>Gestion des Journaux</h5>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Nouvelle écriture
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="journal_type">Type de journal</label>
                            <select class="form-control" id="journal_type">
                                <option value="all">Tous les journaux</option>
                                <option value="achats">Achats</option>
                                <option value="ventes">Ventes</option>
                                <option value="banque">Banque</option>
                                <option value="caisse">Caisse</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_debut">Date début</label>
                            <input type="date" class="form-control" id="date_debut" value="<?php echo date('Y-m-01'); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_fin">Date fin</label>
                            <input type="date" class="form-control" id="date_fin" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Filtrer
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>N° Pièce</th>
                                <th>Journal</th>
                                <th>Compte</th>
                                <th>Libellé</th>
                                <th>Débit</th>
                                <th>Crédit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>03/12/2025</td>
                                <td>FA-2025-001</td>
                                <td><span class="badge bg-info">Ventes</span></td>
                                <td>411100 - Clients</td>
                                <td>Vente de marchandises</td>
                                <td class="text-success">500.000</td>
                                <td class="text-muted">0</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>03/12/2025</td>
                                <td>FA-2025-002</td>
                                <td><span class="badge bg-warning">Achats</span></td>
                                <td>601100 - Achats marchandises</td>
                                <td>Achat fournitures bureau</td>
                                <td class="text-muted">0</td>
                                <td class="text-danger">150.000</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>02/12/2025</td>
                                <td>BQ-2025-045</td>
                                <td><span class="badge bg-success">Banque</span></td>
                                <td>512100 - Banque</td>
                                <td>Virement reçu</td>
                                <td class="text-success">1.250.000</td>
                                <td class="text-muted">0</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td colspan="5" class="text-end"><strong>Totaux :</strong></td>
                                <td><strong>1.750.000</strong></td>
                                <td><strong>150.000</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Soldes par journal</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="journalChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Informations</h6>
                            </div>
                            <div class="card-body">
                                <p>Le module Journaux permet d'enregistrer toutes les écritures comptables selon les différents journaux :</p>
                                <ul>
                                    <li><strong>Journal des achats</strong> : Achats de biens et services</li>
                                    <li><strong>Journal des ventes</strong> : Ventes de biens et services</li>
                                    <li><strong>Journal de banque</strong> : Opérations bancaires</li>
                                    <li><strong>Journal de caisse</strong> : Opérations de caisse</li>
                                    <li><strong>Journal des opérations diverses</strong> : Autres opérations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour le graphique des journaux
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('journalChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Achats', 'Ventes', 'Banque', 'Caisse', 'OD'],
            datasets: [{
                label: 'Total Débit',
                data: [1200000, 500000, 1250000, 300000, 250000],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Total Crédit',
                data: [150000, 1800000, 50000, 100000, 150000],
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
