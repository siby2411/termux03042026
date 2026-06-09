<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Manuel - Opérations sur capital et titres";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Manuel de formation : Opérations sur capital et titres</h5>
                <small>Conforme SYSCOHADA révisé / IFRS</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Libération d'actions par compensation de créances</li>
                        <li>Augmentation de capital et DPS</li>
                        <li>Obligations convertibles (OCA / OBSA)</li>
                        <li>Actionnariat salarié (Stock-options)</li>
                        <li>Certificats d'investissement et actions de préférence</li>
                        <li>Dividendes (acomptes et paiement en actions)</li>
                        <li>Opérations de transformation</li>
                        <li>Calcul du DPS (formule et cas pratique)</li>
                        <li>Fiscalité des stock-options</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">1. Libération d'actions par compensation de créances</div>
                    <div class="card-body">
                        <p><strong>Définition :</strong> Éteindre une dette de l'entreprise envers un créancier en lui attribuant des actions.</p>
                        <div class="alert alert-primary">
                            <strong>✍️ Écriture type :</strong><br>
                            <code>401 (Fournisseur) DÉBIT / 101 (Capital social) CRÉDIT</code>
                        </div>
                        <p><strong>Cas pratique :</strong> Une entreprise doit 10 000 € à un fournisseur. Elle émet pour 10 000 € d'actions.</p>
                        <pre class="bg-dark text-white p-2 rounded">
401 (Fournisseur)    DÉBIT    10 000 €
    101 (Capital)              CRÉDIT   10 000 €
                        </pre>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">2. Augmentation de capital et DPS</div>
                    <div class="card-body">
                        <p><strong>Définition :</strong> Le DPS protège les anciens actionnaires contre la dilution.</p>
                        <div class="alert alert-success">
                            <strong>📐 Formule du DPS :</strong><br>
                            V_dps = (C_a - P_e) / (n + 1)<br>
                            <small>Où C_a = cours avant, P_e = prix d'émission, n = nombre d'actions anciennes pour 1 nouvelle</small>
                        </div>
                        <div class="alert alert-warning">
                            <strong>🔑 Points de vigilance :</strong>
                            <ul class="mb-0">
                                <li>Vérifier le Procès-Verbal (PV) de l'AG</li>
                                <li>Respecter les statuts et les majorités requises</li>
                                <li>Attention aux conséquences fiscales</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">3. Obligations convertibles (OCA / OBSA)</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>OCA (Obligations Convertibles en Actions)</h6>
                                <p>Titre de créance transformable en action.</p>
                                <pre class="bg-dark text-white p-2 rounded">
16 (Dette obligataire)    DÉBIT
    101 (Capital)                 CRÉDIT
    104 (Prime d'émission)        CRÉDIT
                                </pre>
                            </div>
                            <div class="col-md-6">
                                <h6>OBSA (Obligations avec Bons de Souscription)</h6>
                                <p>Obligation + bon d'achat d'actions à prix fixé.</p>
                                <pre class="bg-dark text-white p-2 rounded">
16 (Dette)                DÉBIT
    101 (Capital)                 CRÉDIT
    104 (Prime)                   CRÉDIT
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">4. Stock-options</div>
                    <div class="card-body">
                        <p><strong>Définition :</strong> Droit d'acheter des actions à prix préférentiel.</p>
                        <div class="alert alert-info">
                            <strong>💰 Gain d'acquisition :</strong> (Valeur action - Prix exercice) × nb options<br>
                            <strong>📈 Gain de cession :</strong> (Prix vente - Valeur à levée) × nb options
                        </div>
                        <div class="alert alert-secondary">
                            <strong>⚙️ Impact comptable :</strong> La différence constitue une charge de personnel à étaler.
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">5. Certificats d'investissement (CI)</div>
                    <div class="card-body">
                        <p>Titre représentatif d'une fraction du capital, <strong>sans droit de vote</strong>.</p>
                        <p><strong>Utilité :</strong> Augmenter les fonds propres sans diluer le pouvoir de décision.</p>
                    </div>
                </div>

                <!-- CHAPITRE 6 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">6. Dividendes</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Acomptes sur dividendes</h6>
                                <p>Distribution avant l'approbation des comptes.</p>
                                <pre class="bg-dark text-white p-2 rounded">
119 (Report) ou 12 (Résultat)    DÉBIT
    457 (Associés)                      CRÉDIT
                                </pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Dividendes en actions</h6>
                                <p>Paiement en actions nouvelles plutôt qu'en numéraire.</p>
                                <pre class="bg-dark text-white p-2 rounded">
457 (Associés)    DÉBIT
    101 (Capital)         CRÉDIT
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 7 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">7. Opérations de transformation</div>
                    <div class="card-body">
                        <p>Changement de forme juridique (ex: SARL → SA).</p>
                        <div class="alert alert-warning">
                            <strong>⚠️ Points clés :</strong>
                            <ul class="mb-0">
                                <li>Pas de création d'une nouvelle personne morale</li>
                                <li>Les capitaux propres sont maintenus</li>
                                <li>Ajustement du capital aux seuils légaux</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 8 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">8. Calcul du DPS - Cas pratique détaillé</div>
                    <div class="card-body">
                        <p><strong>Données :</strong></p>
                        <ul>
                            <li>Cours avant augmentation : 150 €</li>
                            <li>Prix d'émission : 100 €</li>
                            <li>Parité : 4 actions anciennes pour 1 action nouvelle</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>Calcul :</strong> V_dps = (150 - 100) / (4 + 1) = 50 / 5 = <strong>10 €</strong>
                        </div>
                        <p><strong>Analyse :</strong> L'actionnaire possédant 4 actions perd 10 € par action, soit 40 € au total.</p>
                    </div>
                </div>

                <!-- CHAPITRE 9 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">9. Fiscalité des stock-options</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Type de gain</th><th>Imposition</th><th>Taux</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Gain d'acquisition</td><td>Traitements et salaires</td><td>Barème IR (0-45%)</td></tr>
                                    <tr><td>Gain de cession</td><td>Plus-value mobilière</td><td>Flat tax 30% ou PFU</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AU MODULE -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AUX MODULES :</strong><br>
                    <a href="gestion_capital_titres.php" class="btn btn-sm btn-primary">💰 Gestion capital et titres</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
