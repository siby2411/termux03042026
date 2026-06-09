<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Guide d'utilisation - Modules avancés";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Guide d'utilisation - Modules avancés</h5>
                <small>Multi-journaux, Lettrage, Analytique, Modèles de saisie</small>
            </div>
            <div class="card-body">
                
                <!-- ==================== 1. MULTI-JOURNAUX ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">1. Multi-journaux (gestion_journaux.php)</div>
                    <div class="card-body">
                        <p><strong>📌 Objectif :</strong> Organiser les écritures par type d'opération pour faciliter l'analyse.</p>
                        <p><strong>📖 Comment utiliser :</strong></p>
                        <ol>
                            <li>Créez des journaux (ex: AC pour Achats, VE pour Ventes, BK pour Banque)</li>
                            <li>Lors de la saisie d'écriture, sélectionnez le journal correspondant</li>
                            <li>Consultez le Grand Livre filtré par journal pour analyser un type d'opération spécifique</li>
                        </ol>
                        <div class="alert alert-secondary">
                            <strong>💡 Exemple :</strong> Journal "AC" (Achats) → toutes les écritures d'achat sont regroupées
                        </div>
                        <p><strong>🔗 Synchronisation :</strong> Le champ <code>journal_id</code> dans ECRITURES_COMPTABLES lie</p>
                    </div>
                </div>

                <!-- ==================== 2. LETTRAGE COMPTABLE ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">2. Lettrage comptable (lettrage_comptable.php)</div>
                    <div class="card-body">
                        <p><strong>📌 Objectif :</strong> Rapprocher les factures clients des règlements pour solder les comptes.</p>
                        <p><strong>📖 Comment utiliser :</strong></p>
                        <ol>
                            <li>Sélectionnez un client/fournisseur et le type</li>
                            <li>Lancez le lettrage automatique</li>
                            <li>Cochez les factures et règlements à lettrer (total factures = total règlements)</li>
                            <li>Confirmez le lettrage</li>
                        </ol>
                        <div class="alert alert-success">
                            <strong>✅ Après lettrage :</strong> Les écritures sont marquées avec <code>lettrage_id</code> et n'apparaissent plus dans les soldes à lettrer
                        </div>
                        <p><strong>📊 Suivi :</strong> Le tableau "Soldes des tiers non lettrés" montre le montant restant</p>
                    </div>
                </div>

                <!-- ==================== 3. COMPTABILITÉ ANALYTIQUE ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">3. Comptabilité analytique (analytique.php)</div>
                    <div class="card-body">
                        <p><strong>📌 Objectif :</strong> Suivre la rentabilité par projet, département ou produit.</p>
                        <p><strong>📖 Comment utiliser :</strong></p>
                        <ol>
                            <li>Créez des sections analytiques (PROJ01, DEP01, PROD01...)</li>
                            <li>Lors de la saisie d'écriture, affectez la section correspondante</li>
                            <li>Consultez le tableau des résultats pour voir le CA, charges et résultat par section</li>
                        </ol>
                        <div class="alert alert-info">
                            <strong>📈 Interprétation :</strong><br>
                            - 💚 Vert = Rentable (Résultat positif)<br>
                            - ❤️ Rouge = Déficitaire (Résultat négatif)<br>
                            - 📊 Marge = (CA - Charges) / CA × 100
                        </div>
                        <p><strong>🔗 Synchronisation :</strong> <code>section_analytique_id</code> dans ECRITURES_COMPTABLES</p>
                    </div>
                </div>

                <!-- ==================== 4. MODÈLES DE SAISIE ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">4. Modèles de saisie (modeles_saisie.php)</div>
                    <div class="card-body">
                        <p><strong>📌 Objectif :</strong> Automatiser les écritures récurrentes (loyer, salaires, abonnements).</p>
                        <p><strong>📖 Comment utiliser :</strong></p>
                        <ol>
                            <li>Onglet "Nouveau modèle" → Créez un modèle (ex: LOYER)</li>
                            <li>Définissez les comptes débit/crédit et le montant par défaut</li>
                            <li>Onglet "Utiliser un modèle" → Sélectionnez le modèle et le montant</li>
                            <li>Cliquez "Générer" → L'écriture est automatiquement créée dans le Grand Livre</li>
                        </ol>
                        <div class="alert alert-light border">
                            <strong>📝 Exemple : Modèle "LOYER"</strong><br>
                            Compte débit: 613 (Locations)<br>
                            Compte crédit: 521 (Banque)<br>
                            Montant: 500 000 F<br>
                            Utilisation → Génère l'écriture mensuelle automatiquement
                        </div>
                    </div>
                </div>

                <!-- ==================== 5. BUSINESS INTELLIGENCE ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">5. Business Intelligence (analytique_avancee.php)</div>
                    <div class="card-body">
                        <p><strong>📌 Objectif :</strong> Analyser les performances globales et le taux d'absorption budgétaire.</p>
                        <p><strong>📖 Comprendre les indicateurs :</strong></p>
                        <ul>
                            <li><strong>Taux d'absorption budgétaire :</strong> (Coûts engagés / Ventes prévues) × 100<br>
                                → &lt; 100% = Maîtrise des coûts, &gt; 100% = Dépassement</li>
                            <li><strong>ROI (Return On Investment) :</strong> (Résultat / CA) × 100 → Rentabilité de l'activité</li>
                            <li><strong>CAF (Capacité d'Autofinancement) :</strong> Résultat + dotations aux amortissements</li>
                            <li><strong>Échéances à venir :</strong> Alertes sur les 7 prochains jours</li>
                        </ul>
                        <div class="alert alert-danger">
                            <strong>⚠️ Alerte SMS/WhatsApp :</strong> Pour les échéances critiques (J-2, J-5), cliquez sur "Alerte" pour envoyer une notification
                        </div>
                    </div>
                </div>

                <!-- ==================== RÉCAPITULATIF ==================== -->
                <div class="alert alert-success">
                    <h6>🔗 SCHÉMA DE SYNCHRONISATION AVEC LE GRAND LIVRE</h6>
                    <pre class="bg-dark text-white p-2 rounded">
ECRITURES_COMPTABLES
├── journal_id ────────→ JOURNAUX (Multi-journaux)
├── section_analytique_id → SECTIONS_ANALYTIQUES (Analytique)
├── modele_id ──────────→ MODELES_SAISIE (Modèles)
├── lettrage_id ────────→ LETTRAGES (Lettrage)
└── date_lettrage ──────→ Date de rapprochement
                    </pre>
                    <p class="mt-2">Tous ces champs sont visibles dans le Grand Livre (<code>grand_livre.php</code>) et la Balance.</p>
                </div>

                <div class="text-center">
                    <a href="verification_sync.php" class="btn btn-primary">🔍 Vérifier la synchronisation</a>
                    <a href="analytique_avancee.php" class="btn btn-success">📊 Voir les KPIs</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
