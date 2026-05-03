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
