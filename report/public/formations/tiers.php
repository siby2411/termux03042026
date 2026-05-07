<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Formation - Gestion des Tiers";
$page_icon = "people";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-people"></i> Formation : Gestion des Tiers</h5>
                <small>Clients, Fournisseurs et partenaires commerciaux</small>
            </div>
            <div class="card-body">
                
                <!-- Objectifs -->
                <div class="alert alert-info">
                    <i class="bi bi-bullseye"></i>
                    <strong>OBJECTIFS PÉDAGOGIQUES :</strong>
                    <ul class="mt-2 mb-0">
                        <li>Savoir créer et gérer les fiches tiers (clients/fournisseurs)</li>
                        <li>Comprendre l'importance du code tiers unique</li>
                        <li>Maîtriser l'association des comptes comptables (401/411)</li>
                        <li>Gérer les informations fiscales (NIF, RCCM)</li>
                    </ul>
                </div>
                
                <!-- Vidéo explicative (simulation) -->
                <div class="card bg-light mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-play-circle"></i> Tutoriel vidéo
                    </div>
                    <div class="card-body text-center">
                        <div class="ratio ratio-16x9" style="max-width: 800px; margin: auto;">
                            <iframe src="https://www.youtube.com/embed/VIDEO_ID_PLACEHOLDER" title="Tutoriel Tiers" allowfullscreen></iframe>
                        </div>
                        <p class="mt-2 text-muted">* Remplacer par votre vidéo de formation</p>
                    </div>
                </div>
                
                <!-- Étapes clés -->
                <h5>📝 Étapes pour créer un tiers :</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">1</div>
                                <h6 class="mt-2">Accéder au module</h6>
                                <small>Menu → Gestion commerciale → Tiers</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">2</div>
                                <h6 class="mt-2">Cliquer "Nouveau"</h6>
                                <small>Bouton vert en haut à droite</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">3</div>
                                <h6 class="mt-2">Remplir le formulaire</h6>
                                <small>Code, raison sociale, contact, NIF</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">4</div>
                                <h6 class="mt-2">Enregistrer</h6>
                                <small>Valider la fiche tier</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cas pratique -->
                <div class="card bg-success text-white mb-4">
                    <div class="card-header">
                        <i class="bi bi-briefcase"></i> CAS PRATIQUE
                    </div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong> Vous devez créer un nouveau client "SOPRIM Sénégal" avec les informations suivantes :</p>
                        <ul>
                            <li>Code : CLI001</li>
                            <li>Adresse : Dakar Plateau, Immeuble SOPRIM</li>
                            <li>Tél : 33 123 45 67</li>
                            <li>NIF : 123456789A</li>
                        </ul>
                        <button class="btn btn-light" onclick="window.open('../tiers.php', '_blank')">📝 Mettre en pratique →</button>
                    </div>
                </div>
                
                <!-- QCM Évaluation -->
                <div class="card bg-warning">
                    <div class="card-header">
                        <i class="bi bi-question-circle"></i> Évaluation des connaissances
                    </div>
                    <div class="card-body">
                        <form id="quizForm">
                            <p><strong>1. Quel est le code comptable pour un compte client ?</strong></p>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="q1" value="401"> 401 - Fournisseurs
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="q1" value="411"> 411 - Clients (CORRECT)
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="q1" value="521"> 521 - Banque
                            </div>
                            
                            <p class="mt-3"><strong>2. À quoi sert le champ NIF ?</strong></p>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="q2" value="A"> Numéro de téléphone
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="q2" value="B"> Identifiant fiscal (CORRECT)
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="q2" value="C"> Code postal
                            </div>
                            
                            <button type="button" class="btn btn-primary mt-3" onclick="checkQuiz()">Vérifier mes réponses</button>
                            <span id="quizResult" class="ms-3"></span>
                        </form>
                    </div>
                </div>
                
                <!-- Liens utiles -->
                <div class="mt-4 text-center">
                    <a href="../tiers.php" class="btn btn-primary">Accéder au module Tiers</a>
                    <a href="../didactiel/" class="btn btn-outline-secondary">← Retour au didacticiel</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkQuiz() {
    let q1 = document.querySelector('input[name="q1"]:checked');
    let q2 = document.querySelector('input[name="q2"]:checked');
    let result = document.getElementById('quizResult');
    
    if(q1 && q1.value === '411' && q2 && q2.value === 'B') {
        result.innerHTML = '✅ Félicitations ! Vous avez tout juste.';
        result.className = 'text-success fw-bold';
    } else {
        result.innerHTML = '⚠️ Réexaminez les réponses. Le compte client est 411 et NIF = Identifiant Fiscal.';
        result.className = 'text-warning fw-bold';
    }
}
</script>

<?php include '../inc_footer.php'; ?>
