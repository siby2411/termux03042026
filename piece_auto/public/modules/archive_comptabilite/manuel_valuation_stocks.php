<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel - Valorisation des stocks et approvisionnements";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel de valorisation des stocks et approvisionnements</h5>
                <small>Méthodes CUMP, FIFO, LIFO - Guide de choix</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Présentation des méthodes de valorisation</li>
                        <li>Quand utiliser CUMP, FIFO ou LIFO ?</li>
                        <li>Impact sur le bilan et le compte de résultat</li>
                        <li>Produits de la classe 7 et comptabilité analytique</li>
                        <li>Gestion des approvisionnements (méthode de Wilson)</li>
                        <li>Cas pratiques</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">1. Présentation des méthodes</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center border-primary">
                                    <div class="card-header bg-primary text-white">CUMP</div>
                                    <div class="card-body">
                                        <p><strong>Coût Unitaire Moyen Pondéré</strong></p>
                                        <p>Moyenne du coût d'achat des marchandises en stock.</p>
                                        <code>CUMP = (Valeur stock initial + Entrées) / (Quantité initiale + Entrées)</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center border-success">
                                    <div class="card-header bg-success text-white">FIFO</div>
                                    <div class="card-body">
                                        <p><strong>First In, First Out</strong></p>
                                        <p>Premiers biens entrés = premiers biens sortis.</p>
                                        <p>Utilisé pour produits périssables.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center border-danger">
                                    <div class="card-header bg-danger text-white">LIFO</div>
                                    <div class="card-body">
                                        <p><strong>Last In, First Out</strong></p>
                                        <p>Derniers biens entrés = premiers biens sortis.</p>
                                        <p>Non recommandé SYSCOHADA (interdit dans certains pays).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">2. Quel méthode choisir ?</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Situation</th><th>Méthode recommandée</th><th>Justification</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Produits périssables (aliments, fleurs)</td><td class="fw-bold text-success">FIFO</td><td>Évite la péremption</td></tr>
                                    <tr><td>Matières premières sans date limite</td><td class="fw-bold text-primary">CUMP</td><td>Lisse les variations de prix</td></tr>
                                    <tr><td>Inflation forte</td><td class="fw-bold text-primary">CUMP ou FIFO</td><td>LIFO interdit en France/SYSCOHADA</td></tr>
                                    <tr><td>Produits de luxe / valeur croissante</td><td class="fw-bold text-primary">CUMP</td><td>Évaluation prudente</td></tr>
                                    <tr><td>Stock de sécurité important</td><td class="fw-bold text-primary">FIFO</td><td>Renouvellement permanent</td></tr>
                                </tbody>
                            <tr>
                        </div>
                        <div class="alert alert-warning mt-2">
                            <strong>⚠️ Recommandation SYSCOHADA :</strong> La méthode LIFO n'est pas recommandée. Privilégiez CUMP ou FIFO.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">3. Impact sur les états financiers</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Sur le BILAN</h6>
                                        <p class="small">Le stock final apparaît à l'actif circulant. La méthode influence sa valorisation.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Sur le COMPTE DE RÉSULTAT</h6>
                                        <p class="small">Le coût des marchandises vendues (CMV) impacte directement le résultat.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>En ANALYTIQUE</h6>
                                        <p class="small">Les coûts des matières premières affectent les coûts de revient des produits.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">4. Produits de la classe 7 et comptabilité analytique</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>✅ OUI, les produits de la classe 7 (ventes) doivent être intégrés en comptabilité analytique</strong>
                        </div>
                        <p>Les produits (ventes) permettent de calculer :</p>
                        <ul>
                            <li>La marge brute par produit</li>
                            <li>Le seuil de rentabilité</li>
                            <li>La rentabilité par gamme</li>
                            <li>Les écarts sur ventes (prix, volume)</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>📊 Exemple :</strong> Si vous fabriquez 3 produits, vous devez affecter chaque vente (compte 701, 703) à la section analytique correspondante.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">5. Cas pratique – Choix de la méthode</div>
                    <div class="card-body">
                        <p><strong>📋 Société OMEGA SARL (fabrication de meubles) :</strong></p>
                        <ul>
                            <li>Matières premières : bois, vernis, quincaillerie (non périssables)</li>
                            <li>Prix en hausse de 5% par an</li>
                            <li>Stock de sécurité important (3 mois)</li>
                            <li>Rotation des stocks : 4 fois par an</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>✅ Recommandation : CUMP (Coût Unitaire Moyen Pondéré)</strong><br>
                            Justification : Lisse les variations de prix, adapté aux matières premières non périssables, conforme SYSCOHADA.
                        </div>
                        <p><strong>💡 Application dans OMEGA ERP :</strong> Utilisez <code>gestion_stocks_avancee.php</code> pour calculer la valorisation CUMP.</p>
                    </div>
                </div>

                <!-- CHAPITRE 6 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">6. Gestion des approvisionnements</div>
                    <div class="card-body">
                        <p><strong>📊 Formules clés :</strong></p>
                        <ul>
                            <li><strong>Quantité économique (Wilson) :</strong> QEC = √(2 × D × Cl / Cs)</li>
                            <li><strong>Stock d'alerte :</strong> Sa = (Dm × Délai) + Ss</li>
                            <li><strong>Coût total :</strong> CT = (D/Q) × Cl + (Q/2) × Cs</li>
                        </ul>
                        <p>Ces calculs sont automatisés dans le module <code>gestion_approvisionnements.php</code>.</p>
                        <div class="alert alert-info">
                            <strong>🔗 Module associé :</strong> <a href="gestion_approvisionnements.php">Gestion des approvisionnements</a>
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AUX MODULES -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AUX MODULES :</strong><br>
                    <a href="gestion_stocks_avancee.php" class="btn btn-sm btn-primary">📊 Valorisation stocks (CUMP/FIFO/LIFO)</a>
                    <a href="gestion_approvisionnements.php" class="btn btn-sm btn-primary">📦 Gestion approvisionnements</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
