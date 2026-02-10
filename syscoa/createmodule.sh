#!/bin/bash
# create_module_pages.sh

echo "=== CRÉATION DES PAGES DE MODULES SYSCOHADA ==="

# Créer le dossier pages s'il n'existe pas
sudo mkdir -p /var/www/syscoa/pages

echo "1. Création de dashboard.php..."
sudo tee /var/www/syscoa/pages/dashboard.php << 'EOF'
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</h5>
            </div>
            <div class="card-body">
                <h4>Bienvenue dans SYSCOHADA !</h4>
                <p class="lead">Système Comptable OHADA Compliant</p>
                
                <div class="row mt-4">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body text-center">
                                <h1><i class="fas fa-book"></i></h1>
                                <h5>Journaux</h5>
                                <p>Gestion des écritures comptables</p>
                                <a href="index.php?module=journaux" class="btn btn-light btn-sm">Accéder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center">
                                <h1><i class="fas fa-file-invoice-dollar"></i></h1>
                                <h5>Grand Livre</h5>
                                <p>Consultation du grand livre général</p>
                                <a href="index.php?module=grand_livre" class="btn btn-light btn-sm">Accéder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center">
                                <h1><i class="fas fa-scale-balanced"></i></h1>
                                <h5>Balance</h5>
                                <p>Balance des comptes</p>
                                <a href="index.php?module=balance" class="btn btn-light btn-sm">Accéder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body text-center">
                                <h1><i class="fas fa-chart-bar"></i></h1>
                                <h5>Rapports</h5>
                                <p>États financiers et rapports</p>
                                <a href="index.php?module=rapports" class="btn btn-light btn-sm">Accéder</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Statistiques rapides</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Écritures du mois
                                        <span class="badge bg-primary rounded-pill">156</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Comptes actifs
                                        <span class="badge bg-success rounded-pill">89</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Tiers enregistrés
                                        <span class="badge bg-warning rounded-pill">42</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Exercice en cours
                                        <span class="badge bg-info rounded-pill">2025</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Dernières activités</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small>Il y a 2 heures</small>
                                        </div>
                                        <p class="mb-1">Nouvelle écriture dans le journal des achats</p>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small>Il y a 5 heures</small>
                                        </div>
                                        <p class="mb-1">Balance du mois générée</p>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small>Hier</small>
                                        </div>
                                        <p class="mb-1">Nouveau tiers ajouté</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

echo "2. Création de journaux.php..."
sudo tee /var/www/syscoa/pages/journaux.php << 'EOF'
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
EOF

echo "3. Création de grand_livre.php..."
sudo tee /var/www/syscoa/pages/grand_livre.php << 'EOF'
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
EOF

echo "4. Création de balance.php..."
sudo tee /var/www/syscoa/pages/balance.php << 'EOF'
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-scale-balanced me-2"></i>Balance des Comptes</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="balance_date">Date de balance</label>
                            <input type="date" class="form-control" id="balance_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="balance_type">Type de balance</label>
                            <select class="form-control" id="balance_type">
                                <option value="mensuelle">Balance mensuelle</option>
                                <option value="trimestrielle">Balance trimestrielle</option>
                                <option value="annuelle">Balance annuelle</option>
                                <option value="cumulee">Balance cumulée</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="balance_format">Format</label>
                            <select class="form-control" id="balance_format">
                                <option value="complet">Balance complète</option>
                                <option value="agee">Balance âgée</option>
                                <option value="classe">Par classe</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button class="btn btn-primary">
                                <i class="fas fa-calculator me-1"></i>Calculer
                            </button>
                            <button class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th rowspan="2">Compte</th>
                                <th rowspan="2">Intitulé</th>
                                <th colspan="2" class="text-center">Solde initial</th>
                                <th colspan="2" class="text-center">Mouvements</th>
                                <th colspan="2" class="text-center">Solde final</th>
                            </tr>
                            <tr>
                                <th>Débit</th>
                                <th>Crédit</th>
                                <th>Débit</th>
                                <th>Crédit</th>
                                <th>Débit</th>
                                <th>Crédit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Classe 1 -->
                            <tr class="table-info">
                                <td colspan="8"><strong>CLASSE 1 - CAPITAUX</strong></td>
                            </tr>
                            <tr>
                                <td>101100</td>
                                <td>Capital social</td>
                                <td></td>
                                <td>5.000.000</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>5.000.000</td>
                            </tr>
                            <tr>
                                <td>106100</td>
                                <td>Réserves</td>
                                <td></td>
                                <td>1.250.000</td>
                                <td></td>
                                <td>250.000</td>
                                <td></td>
                                <td>1.500.000</td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="2" class="text-end"><strong>Total Classe 1 :</strong></td>
                                <td></td>
                                <td><strong>5.000.000</strong></td>
                                <td></td>
                                <td><strong>250.000</strong></td>
                                <td></td>
                                <td><strong>6.500.000</strong></td>
                            </tr>
                            
                            <!-- Classe 2 -->
                            <tr class="table-info">
                                <td colspan="8"><strong>CLASSE 2 - IMMOBILISATIONS</strong></td>
                            </tr>
                            <tr>
                                <td>211100</td>
                                <td>Terrain</td>
                                <td>2.000.000</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>2.000.000</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>218100</td>
                                <td>Matériel informatique</td>
                                <td>500.000</td>
                                <td></td>
                                <td>250.000</td>
                                <td></td>
                                <td>750.000</td>
                                <td></td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="2" class="text-end"><strong>Total Classe 2 :</strong></td>
                                <td><strong>2.500.000</strong></td>
                                <td></td>
                                <td><strong>250.000</strong></td>
                                <td></td>
                                <td><strong>2.750.000</strong></td>
                                <td></td>
                            </tr>
                            
                            <!-- Classe 6 -->
                            <tr class="table-info">
                                <td colspan="8"><strong>CLASSE 6 - CHARGES</strong></td>
                            </tr>
                            <tr>
                                <td>601100</td>
                                <td>Achats marchandises</td>
                                <td></td>
                                <td></td>
                                <td>1.500.000</td>
                                <td></td>
                                <td>1.500.000</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>622100</td>
                                <td>Frais de transport</td>
                                <td></td>
                                <td></td>
                                <td>250.000</td>
                                <td></td>
                                <td>250.000</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>631100</td>
                                <td>Salaires et appointements</td>
                                <td></td>
                                <td></td>
                                <td>2.500.000</td>
                                <td></td>
                                <td>2.500.000</td>
                                <td></td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="2" class="text-end"><strong>Total Classe 6 :</strong></td>
                                <td></td>
                                <td></td>
                                <td><strong>4.250.000</strong></td>
                                <td></td>
                                <td><strong>4.250.000</strong></td>
                                <td></td>
                            </tr>
                            
                            <!-- Classe 7 -->
                            <tr class="table-info">
                                <td colspan="8"><strong>CLASSE 7 - PRODUITS</strong></td>
                            </tr>
                            <tr>
                                <td>701100</td>
                                <td>Ventes de marchandises</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>6.000.000</td>
                                <td></td>
                                <td>6.000.000</td>
                            </tr>
                            <tr>
                                <td>706100</td>
                                <td>Produits accessoires</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>500.000</td>
                                <td></td>
                                <td>500.000</td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="2" class="text-end"><strong>Total Classe 7 :</strong></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><strong>6.500.000</strong></td>
                                <td></td>
                                <td><strong>6.500.000</strong></td>
                            </tr>
                            
                            <!-- TOTAUX GÉNÉRAUX -->
                            <tr class="table-dark">
                                <td colspan="2" class="text-end"><strong>TOTAUX GÉNÉRAUX :</strong></td>
                                <td><strong>2.500.000</strong></td>
                                <td><strong>5.000.000</strong></td>
                                <td><strong>4.500.000</strong></td>
                                <td><strong>6.750.000</strong></td>
                                <td><strong>7.500.000</strong></td>
                                <td><strong>13.000.000</strong></td>
                            </tr>
                            <tr class="table-warning">
                                <td colspan="2" class="text-end"><strong>SOLDE :</strong></td>
                                <td colspan="2" class="text-center"><strong>2.500.000 C</strong></td>
                                <td colspan="2" class="text-center"><strong>2.250.000 C</strong></td>
                                <td colspan="2" class="text-center"><strong>5.500.000 C</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>À propos de la balance</h6>
                            <p>La balance vérifie l'équilibre comptable : <strong>Total Débit = Total Crédit</strong>.</p>
                            <p>En OHADA, la balance doit être équilibrée à chaque période de reporting.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

echo "5. Création des autres modules..."
# Créer les autres fichiers rapidement
sudo tee /var/www/syscoa/pages/comptes.php << 'EOF'
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>Plan Comptable OHADA</h5>
            </div>
            <div class="card-body">
                <p>Module Plan Comptable - En construction</p>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Structure du plan comptable OHADA</h6>
                    <p>Le système comptable OHADA organise les comptes en 10 classes :</p>
                    <ol>
                        <li>Comptes de capitaux</li>
                        <li>Comptes d'immobilisations</li>
                        <li>Comptes de stocks</li>
                        <li>Comptes de tiers</li>
                        <li>Comptes financiers</li>
                        <li>Comptes de charges</li>
                        <li>Comptes de produits</li>
                        <li>Comptes de résultat</li>
                        <li>Comptes spéciaux</li>
                        <li>Comptes d'engagements</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

sudo tee /var/www/syscoa/pages/tiers.php << 'EOF'
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Gestion des Tiers</h5>
            </div>
            <div class="card-body">
                <p>Module Tiers - En construction</p>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-users me-2"></i>Types de tiers</h6>
                    <ul>
                        <li><strong>Clients</strong> : Comptes 411xxx</li>
                        <li><strong>Fournisseurs</strong> : Comptes 401xxx</li>
                        <li><strong>Personnel</strong> : Comptes 421xxx</li>
                        <li><strong>État et organismes sociaux</strong> : Comptes 43xxx, 44xxx</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

sudo tee /var/www/syscoa/pages/rapports.php << 'EOF'
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Rapports et Analyses</h5>
            </div>
            <div class="card-body">
                <p>Module Rapports - En construction</p>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-file-invoice fa-3x text-primary mb-3"></i>
                                <h5>Bilan</h5>
                                <p>État de la situation financière</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                                <h5>Compte de résultat</h5>
                                <p>Résultats de l'exercice</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-cash-register fa-3x text-warning mb-3"></i>
                                <h5>Tableau de flux</h5>
                                <p>Flux de trésorerie</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

sudo tee /var/www/syscoa/pages/etats.php << 'EOF'
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-file-contract me-2"></i>États Financiers</h5>
            </div>
            <div class="card-body">
                <p>Module États Financiers - En construction</p>
                <div class="alert alert-success">
                    <h6><i class="fas fa-file-alt me-2"></i>États financiers obligatoires OHADA</h6>
                    <ul>
                        <li><strong>Bilan</strong> : Actif / Passif</li>
                        <li><strong>Compte de résultat</strong> : Charges / Produits</li>
                        <li><strong>Tableau de flux de trésorerie</strong></li>
                        <li><strong>Annexes</strong> : Informations complémentaires</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

sudo tee /var/www/syscoa/pages/parametres.php << 'EOF'
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-cog me-2"></i>Paramètres du Système</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Paramètres généraux</h6>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">Nom de l'entreprise</label>
                                        <input type="text" class="form-control" value="Ma Société SARL">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Devise</label>
                                        <select class="form-control">
                                            <option>FCFA</option>
                                            <option>EUR</option>
                                            <option>USD</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Format de date</label>
                                        <select class="form-control">
                                            <option>JJ/MM/AAAA</option>
                                            <option>AAAA-MM-JJ</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Exercice comptable</h6>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">Exercice en cours</label>
                                        <select class="form-control">
                                            <option>2025</option>
                                            <option>2024</option>
                                            <option>2023</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date début</label>
                                        <input type="date" class="form-control" value="2025-01-01">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date fin</label>
                                        <input type="date" class="form-control" value="2025-12-31">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="exercice_ouvert" checked>
                                            <label class="form-check-label" for="exercice_ouvert">
                                                Exercice ouvert
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6>Informations système</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td width="30%"><strong>Version SYSCOHADA</strong></td>
                                        <td>2.0</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Base de données</strong></td>
                                        <td>MySQL/MariaDB</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Serveur web</strong></td>
                                        <td>Apache 2.4</td>
                                    </tr>
                                    <tr>
                                        <td><strong>PHP</strong></td>
                                        <td>8.1+</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dernière mise à jour</strong></td>
                                        <td>03/12/2025</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

echo "6. Vérification de l'existence des fichiers..."
ls -la /var/www/syscoa/pages/*.php

echo "7. Mise à jour d'index.php pour inclure les pages..."
# Vérifier si index.php inclut correctement les pages
if ! grep -q "include 'pages/" /var/www/syscoa/index.php; then
    echo "   → Mise à jour d'index.php..."
    sudo tee /var/www/syscoa/index.php << 'EOF'
<?php
// Page d'accueil SYSCOHADA
require_once 'config.php';
check_login();

$module = $_GET['module'] ?? 'dashboard';
$submodule = $_GET['submodule'] ?? '';

// Inclure le header
include 'includes/header.php';

// Contenu principal
$page_file = "pages/$module.php";
if (file_exists($page_file)) {
    include $page_file;
} else {
    echo '<div class="alert alert-danger">';
    echo '<h4><i class="fas fa-exclamation-triangle"></i> Module non disponible</h4>';
    echo '<p>Le module <strong>' . htmlspecialchars($module) . '</strong> n\'est pas encore implémenté.</p>';
    echo '<a href="?module=dashboard" class="btn btn-primary">Retour au tableau de bord</a>';
    echo '</div>';
}

// Inclure le footer
include 'includes/footer.php';
?>
EOF
fi

echo "8. Ajout de Chart.js pour les graphiques..."
# Ajouter Chart.js CDN dans header.php si pas déjà présent
if ! grep -q "chart.js" /var/www/syscoa/includes/header.php; then
    sudo sed -i '/<\/head>/i\    <!-- Chart.js -->\n    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>' /var/www/syscoa/includes/header.php
fi

echo "9. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CRÉATION TERMINÉE ==="
echo ""
echo "🎯 PAGES CRÉÉES :"
echo "1. Tableau de bord (/pages/dashboard.php)"
echo "2. Journaux (/pages/journaux.php)"
echo "3. Grand Livre (/pages/grand_livre.php)"
echo "4. Balance (/pages/balance.php)"
echo "5. Plan Comptable (/pages/comptes.php)"
echo "6. Tiers (/pages/tiers.php)"
echo "7. Rapports (/pages/rapports.php)"
echo "8. États Financiers (/pages/etats.php)"
echo "9. Paramètres (/pages/parametres.php)"
echo ""
echo "✅ TESTEZ MAINTENANT :"
echo "http://192.168.1.33:8080/syscoa/index.php?module=dashboard"
echo "http://192.168.1.33:8080/syscoa/index.php?module=journaux"
echo "http://192.168.1.33:8080/syscoa/index.php?module=grand_livre"
echo ""
echo "🔧 SI PROBLÈME :"
echo "   sudo tail -f /var/log/apache2/error.log"
