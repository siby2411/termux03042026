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
