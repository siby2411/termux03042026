<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Guide d'utilisation - Moyens de paiement";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Guide d'utilisation - Moyens de paiement</h5>
                <small>Comment créer des échéances et les régler</small>
            </div>
            <div class="card-body">
                
                <!-- ==================== ÉTAPE 1 ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">1️⃣ Créer des échéances à payer</div>
                    <div class="card-body">
                        <p>Pour qu'une échéance apparaisse dans "Moyens de paiement", il faut d'abord la créer dans le module <strong>Trésorerie & Échéanciers</strong>.</p>
                        
                        <div class="alert alert-info">
                            <strong>🔗 Accès :</strong> 
                            <a href="tresorerie_complete.php" class="btn btn-sm btn-primary">📊 Trésorerie & Échéanciers</a>
                        </div>
                        
                        <p><strong>Procédure :</strong></p>
                        <ol>
                            <li>Allez dans <strong>Trésorerie & Échéanciers</strong></li>
                            <li>Onglet <strong>"Nouveau"</strong></li>
                            <li>Remplissez le formulaire :
                                <ul>
                                    <li>Type : <strong>FOURNISSEUR</strong> (pour les paiements à effectuer)</li>
                                    <li>Tiers : Sélectionnez le fournisseur</li>
                                    <li>Mode paiement : <strong>VIREMENT</strong> (ou CHÈQUE, ESPÈCES)</li>
                                    <li>Libellé : "Facture n°XXX"</li>
                                    <li>Montant : 500 000 F</li>
                                    <li>Date échéance : Date butoir de paiement</li>
                                </ul>
                            </li>
                            <li>Cliquez <strong>"Créer échéance"</strong></li>
                        </ol>
                        
                        <div class="alert alert-warning">
                            <strong>⚠️ Important :</strong> L'échéance apparaîtra dans la liste des échéances à payer dès sa création.
                        </div>
                    </div>
                </div>

                <!-- ==================== ÉTAPE 2 ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">2️⃣ Visualiser les échéances à payer</div>
                    <div class="card-body">
                        <p>Une fois créées, les échéances apparaissent dans deux endroits :</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">📍 Dans Trésorerie & Échéanciers</div>
                                    <div class="card-body">
                                        <ul>
                                            <li>Onglet <strong>"Échéanciers"</strong></li>
                                            <li>Colonne <strong>"Statut"</strong> = EN_ATTENTE</li>
                                            <li>Les échéances proches apparaissent en <span class="text-warning">orange</span></li>
                                            <li>Les échéances dépassées apparaissent en <span class="text-danger">rouge</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">📍 Dans Moyens de paiement</div>
                                    <div class="card-body">
                                        <ul>
                                            <li>Onglet <strong>"Échéances à payer"</strong></li>
                                            <li>Liste des fournisseurs à payer</li>
                                            <li>Case à cocher pour sélection multiple</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== ÉTAPE 3 ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">3️⃣ Paiement des échéances</div>
                    <div class="card-body">
                        <p><strong>Méthode 1 - Paiement rapide (une seule échéance) :</strong></p>
                        <ol>
                            <li>Dans <strong>Trésorerie & Échéanciers</strong>, onglet "Échéanciers"</li>
                            <li>Cliquez sur le bouton <strong class="text-success">"Payer"</strong> en face de l'échéance</li>
                            <li>Confirmez → L'écriture comptable est automatiquement générée</li>
                        </ol>
                        
                        <p><strong>Méthode 2 - Paiement groupé (multiple échéances) :</strong></p>
                        <ol>
                            <li>Dans <strong>Moyens de paiement</strong></li>
                            <li>Cochez plusieurs échéances</li>
                            <li>Cliquez sur <strong>"Émettre le virement"</strong></li>
                            <li>Un fichier de virement est généré</li>
                        </ol>
                    </div>
                </div>

                <!-- ==================== CAS PRATIQUE ==================== -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">📋 CAS PRATIQUE - Paiement fournisseur</div>
                    <div class="card-body">
                        <p><strong>Scénario :</strong> Vous devez payer une facture fournisseur de 250 000 F avec échéance dans 5 jours.</p>
                        
                        <p><strong>Solution :</strong></p>
                        <ol>
                            <li>Aller dans <strong>Trésorerie & Échéanciers</strong> → "Nouveau"</li>
                            <li>Saisir : Type=FOURNISSEUR, Montant=250000, Échéance=date_J+5</li>
                            <li>Cliquer "Créer échéance"</li>
                            <li>Retourner dans "Échéanciers" → Voir la ligne créée</li>
                            <li>Cliquer "Payer" → L'écriture comptable est générée</li>
                        </ol>
                        
                        <div class="alert alert-success">
                            <strong>✅ Résultat :</strong> Le Grand Livre enregistre automatiquement l'écriture de paiement.
                        </div>
                    </div>
                </div>

                <!-- ==================== SYNCHRONISATION ==================== -->
                <div class="alert alert-info">
                    <h6>🔗 SYNCHRONISATION AVEC LE GRAND LIVRE</h6>
                    <p>Chaque paiement enregistré génère automatiquement une écriture comptable :</p>
                    <ul>
                        <li><strong>Paiement fournisseur :</strong> Débit 401 (Fournisseur) / Crédit 521 (Banque)</li>
                        <li><strong>Encaissement client :</strong> Débit 521 (Banque) / Crédit 411 (Client)</li>
                    </ul>
                    <p class="mb-0">Ces écritures sont immédiatement visibles dans le Grand Livre (<code>grand_livre.php</code>).</p>
                </div>

                <div class="text-center mt-3">
                    <a href="tresorerie_complete.php" class="btn btn-primary">📊 Créer des échéances</a>
                    <a href="moyens_paiement.php" class="btn btn-success">💰 Payer les échéances</a>
                    <a href="grand_livre.php" class="btn btn-info">📖 Voir le Grand Livre</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
