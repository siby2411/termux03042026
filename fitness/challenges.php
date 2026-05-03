<?php
require_once 'config/database.php';
include 'header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-trophy"></i> Défis & Challenges Oméga Fitness</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-running fa-3x" style="color: #FF4B2B"></i>
                            <h5 class="mt-3">Challenge Assiduité</h5>
                            <p>30 séances en 30 jours</p>
                            <div class="progress mb-2"><div class="progress-bar" style="width: 65%">65%</div></div>
                            <small>12 participants | 8 ont réussi</small>
                            <button class="btn btn-sm btn-primary mt-2">Participer</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-fist-raised fa-3x" style="color: #FF4B2B"></i>
                            <h5 class="mt-3">Défis Techniques</h5>
                            <p>Maîtrisez 5 techniques avancées</p>
                            <div class="progress mb-2"><div class="progress-bar" style="width: 40%">40%</div></div>
                            <small>8 participants | 3 ont réussi</small>
                            <button class="btn btn-sm btn-primary mt-2">Participer</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-weight-hanging fa-3x" style="color: #FF4B2B"></i>
                            <h5 class="mt-3">Transformation</h5>
                            <p>Objectif perte de poids -5kg</p>
                            <div class="progress mb-2"><div class="progress-bar" style="width: 25%">25%</div></div>
                            <small>15 participants | 4 ont réussi</small>
                            <button class="btn btn-sm btn-primary mt-2">Participer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
