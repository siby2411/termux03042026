<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Didacticiel - Inventaire SYSCOHADA";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-book"></i> Didacticiel : L'inventaire comptable (SYSCOHADA)</h5>
                <small>Comprendre et maîtriser la variation des stocks</small>
            </div>
            <div class="card-body">
                
                <!-- SOMMAIRE -->
                <div class="alert alert-info">
                    <strong>📚 SOMMAIRE</strong>
                    <ol class="mb-0 mt-2">
                        <li>Qu'est-ce que l'inventaire ?</li>
                        <li>Le compte 603 - Variation des stocks</li>
                        <li>Les écritures d'inventaire</li>
                        <li>Cas pratique 1 : Stockage (SF > SI)</li>
                        <li>Cas pratique 2 : Déstockage (SF < SI)</li>
                        <li>Impact sur le compte de résultat</li>
                        <li>Impact sur le bilan</li>
                        <li>Fonction PHP d'automatisation</li>
                    </ol>
                </div>

                <!-- CHAPITRE 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 1. Qu'est-ce que l'inventaire ?</div>
                    <div class="card-body">
                        <p>L'inventaire est une opération qui consiste à <strong>compter physiquement</strong> les marchandises en stock à une date donnée (généralement en fin d'exercice).</p>
                        <div class="alert alert-success">
                            <strong>Objectifs :</strong>
                            <ul class="mb-0">
                                <li>Respecter le principe d'indépendance des exercices</li>
                                <li>Refléter une image fidèle du patrimoine</li>
                                <li>Détecter les vols, pertes ou erreurs de gestion</li>
                                <li>Déterminer le résultat fiscal</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📊 2. Le compte 603 - Variation des stocks</div>
                    <div class="card-body">
                        <p>Le compte <strong>603 - Variation des stocks</strong> est un compte de <strong>contrepartie</strong> qui permet d'ajuster le coût d'achat des marchandises vendues.</p>
                        <div class="alert alert-primary">
                            <strong>Formule :</strong><br>
                            Achats de marchandises (60) ± Variation des stocks (603) = Achats consommés
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card text-center bg-success text-white">
                                    <div class="card-body">
                                        <h5>Stock Final > Stock Initial</h5>
                                        <h3>603 = CRÉDIT</h3>
                                        <small>Produit → Bénéfice</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card text-center bg-danger text-white">
                                    <div class="card-body">
                                        <h5>Stock Final < Stock Initial</h5>
                                        <h3>603 = DÉBIT</h3>
                                        <small>Charge → Perte</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 3 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">✍️ 3. Les écritures d'inventaire</div>
                    <div class="card-body">
                        <p>Pour constater la variation de stock, on procède en <strong>deux étapes</strong> :</p>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Étape 1 : Annulation du stock initial</h6>
                                <pre class="bg-dark text-white p-2 rounded">
603 (Variation)     DÉBIT    SI
    31 (Stock)               CRÉDIT   SI
                                </pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Étape 2 : Constatation du stock final</h6>
                                <pre class="bg-dark text-white p-2 rounded">
31 (Stock)          DÉBIT    SF
    603 (Variation)          CRÉDIT   SF
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 4 - Cas pratique 1 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📋 4. Cas pratique 1 : Stockage (SF > SI)</div>
                    <div class="card-body">
                        <p><strong>Données :</strong> Stock Initial = 5 000 F, Stock Final = 7 000 F</p>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Écriture 1 - Annulation SI</h6>
                                <pre class="bg-dark text-white p-2 rounded">
603      DÉBIT    5 000
    31            CRÉDIT   5 000
                                </pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Écriture 2 - Constatation SF</h6>
                                <pre class="bg-dark text-white p-2 rounded">
31       DÉBIT    7 000
    603           CRÉDIT   7 000
                                </pre>
                            </div>
                        </div>
                        <div class="alert alert-success text-center">
                            <strong>Solde 603 = 2 000 F (Créditeur) → PRODUIT → Le stock a augmenté</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 5 - Cas pratique 2 -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📋 5. Cas pratique 2 : Déstockage (SF < SI)</div>
                    <div class="card-body">
                        <p><strong>Données :</strong> Stock Initial = 10 000 F, Stock Final = 6 000 F</p>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Écriture 1 - Annulation SI</h6>
                                <pre class="bg-dark text-white p-2 rounded">
603      DÉBIT   10 000
    31            CRÉDIT  10 000
                                </pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Écriture 2 - Constatation SF</h6>
                                <pre class="bg-dark text-white p-2 rounded">
31       DÉBIT    6 000
    603           CRÉDIT   6 000
                                </pre>
                            </div>
                        </div>
                        <div class="alert alert-danger text-center">
                            <strong>Solde 603 = 4 000 F (Débiteur) → CHARGE → Le stock a diminué</strong>
                        </div>
                    </div>
                </div>

                <!-- CHAPITRE 6 - Impact compte de résultat -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📈 6. Impact sur le compte de résultat</div>
                    <div class="card-body">
                        <pre class="bg-dark text-white p-2 rounded">
┌─────────────────────────────────────────────────────────────┐
│                     COMPTE DE RÉSULTAT                       │
├─────────────────────────────────────────────────────────────┤
│ CHARGES                        │ PRODUITS                   │
├────────────────────────────────┼────────────────────────────┤
│ Achats de marchandises 1 000 000│ Ventes                     │
│ Variation de stocks     + 200 000│                            │
│ = Achats consommés     1 200 000│                            │
└────────────────────────────────┴────────────────────────────┘
                        </pre>
                        <p class="mt-2">Le compte 603 ajuste les achats pour ne faire apparaître que ce qui a été <strong>réellement vendu</strong>.</p>
                    </div>
                </div>

                <!-- CHAPITRE 7 - Impact bilan -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📊 7. Impact sur le bilan</div>
                    <div class="card-body">
                        <pre class="bg-dark text-white p-2 rounded">
┌─────────────────────────────────────────────────────────────┐
│                         BILAN                               │
├─────────────────────────────────────────────────────────────┤
│ ACTIF                               │ PASSIF                │
├─────────────────────────────────────┼───────────────────────┤
│ ACTIF CIRCULANT                      │                       │
│ Stocks (31) = 7 000 F               │                       │
└─────────────────────────────────────┴───────────────────────┘
                        </pre>
                        <p class="mt-2">Le bilan montre le <strong>stock final</strong> (position), pas la variation (mouvement).</p>
                    </div>
                </div>

                <!-- CHAPITRE 8 - Code PHP -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">💻 8. Fonction PHP d'automatisation</div>
                    <div class="card-body">
                        <pre class="bg-dark text-white p-2 rounded">
function genererEcritureClotureStock($stock_initial, $stock_final, $exercice) {
    // 1. Annulation du stock initial
    if ($stock_initial > 0) {
        $sql = "INSERT INTO ECRITURES_COMPTABLES 
                (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant) 
                VALUES (CURDATE(), 'Annulation stock initial N', 603, 31, ?)";
        $pdo->prepare($sql)->execute([$stock_initial]);
    }
    
    // 2. Constatation du stock final
    if ($stock_final > 0) {
        $sql = "INSERT INTO ECRITURES_COMPTABLES 
                (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant) 
                VALUES (CURDATE(), 'Constatation stock final N', 31, 603, ?)";
        $pdo->prepare($sql)->execute([$stock_final]);
    }
    
    // 3. Contrepassation au 01/01/N+1
    $sql = "INSERT INTO ECRITURES_COMPTABLES 
            (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant) 
            VALUES (DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Contrepassation', 603, 31, ?)";
    $pdo->prepare($sql)->execute([$stock_final]);
}
                        </pre>
                        <button class="btn btn-primary mt-2" onclick="copierCode()">📋 Copier le code</button>
                    </div>
                </div>

                <!-- GLOSSAIRE -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">📖 Glossaire</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>SI</strong> : Stock Initial</p>
                                <p><strong>SF</strong> : Stock Final</p>
                                <p><strong>603</strong> : Variation des stocks</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>31</strong> : Stocks de marchandises</p>
                                <p><strong>VNC</strong> : Valeur Nette Comptable</p>
                                <p><strong>CEG</strong> : Charges d'Exploitation Générales</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Débit</strong> : Colonne gauche</p>
                                <p><strong>Crédit</strong> : Colonne droite</p>
                                <p><strong>Partie double</strong> : Total Débit = Total Crédit</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCÈS AU MODULE -->
                <div class="alert alert-info mt-3">
                    <strong>🌐 ACCÈS AU MODULE D'INVENTAIRE :</strong><br>
                    <a href="inventaire_complet.php" class="btn btn-sm btn-primary">📦 Lancer un inventaire</a>
                    <a href="gestion_stocks_complet.php" class="btn btn-sm btn-success">📊 Gestion des stocks</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copierCode() {
    let code = `function genererEcritureClotureStock($stock_initial, $stock_final, $exercice) {
    // 1. Annulation du stock initial
    if ($stock_initial > 0) {
        $sql = "INSERT INTO ECRITURES_COMPTABLES 
                (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant) 
                VALUES (CURDATE(), 'Annulation stock initial N', 603, 31, ?)";
        $pdo->prepare($sql)->execute([$stock_initial]);
    }
    
    // 2. Constatation du stock final
    if ($stock_final > 0) {
        $sql = "INSERT INTO ECRITURES_COMPTABLES 
                (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant) 
                VALUES (CURDATE(), 'Constatation stock final N', 31, 603, ?)";
        $pdo->prepare($sql)->execute([$stock_final]);
    }
    
    // 3. Contrepassation au 01/01/N+1
    $sql = "INSERT INTO ECRITURES_COMPTABLES 
            (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant) 
            VALUES (DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Contrepassation', 603, 31, ?)";
    $pdo->prepare($sql)->execute([$stock_final]);
}`;
    
    navigator.clipboard.writeText(code);
    alert("✅ Code copié dans le presse-papier");
}
</script>

<?php include 'inc_footer.php'; ?>
