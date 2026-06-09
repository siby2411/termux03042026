<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Inventaire complet - Gestion des stocks SYSCOHADA";
$page_icon = "clipboard-data";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération des articles
$articles = $pdo->query("SELECT * FROM ARTICLES_STOCK ORDER BY code_article")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_inventaire = $_POST['date_inventaire'];
    
    foreach ($articles as $a) {
        $quantite_reelle = (int)$_POST["qte_reelle_{$a['id']}"];
        $stock_theorique = $a['stock_actuel'];
        $ecart = $quantite_reelle - $stock_theorique;
        $valeur_unitaire = $a['prix_unitaire'];
        $valeur_ecart = $ecart * $valeur_unitaire;
        
        // Enregistrement dans INVENTAIRE_PHYSIQUE
        $stmt = $pdo->prepare("INSERT INTO INVENTAIRE_PHYSIQUE (article_id, date_inventaire, quantite_theorique, quantite_reelle, valeur_ecart) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$a['id'], $date_inventaire, $stock_theorique, $quantite_reelle, $valeur_ecart]);
        
        // Mise à jour du stock
        $stmt = $pdo->prepare("UPDATE ARTICLES_STOCK SET stock_actuel = ? WHERE id = ?");
        $stmt->execute([$quantite_reelle, $a['id']]);
        
        // Génération de l'écriture comptable
        if ($ecart != 0) {
            if ($ecart > 0) {
                // Gain de stock → Débit 31, Crédit 603
                $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) 
                        VALUES (?, 'Inventaire - Gain stock', 31, 603, ?, ?, 'INVENTAIRE')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$date_inventaire, abs($valeur_ecart), "INV-{$a['code_article']}"]);
            } else {
                // Perte de stock → Débit 603, Crédit 31
                $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) 
                        VALUES (?, 'Inventaire - Perte stock', 603, 31, ?, ?, 'INVENTAIRE')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$date_inventaire, abs($valeur_ecart), "INV-{$a['code_article']}"]);
            }
        }
    }
    
    $message = "✅ Inventaire enregistré avec succès - Écritures comptables générées";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-clipboard-data"></i> Inventaire physique - SYSCOHADA</h5>
                <small>Constatation du stock final et variation de stock (compte 603)</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <strong>📖 Principe SYSCOHADA :</strong><br>
                    • Stock final > Stock initial → Compte 603 créditeur (produit) → Bénéfice<br>
                    • Stock final < Stock initial → Compte 603 débiteur (charge) → Perte
                </div>

                <form method="POST">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label>Date d'inventaire</label>
                            <input type="date" name="date_inventaire" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Article</th>
                                    <th>Stock théorique</th>
                                    <th>Stock réel</th>
                                    <th>Écart</th>
                                    <th>Valeur unitaire</th>
                                    <th class="text-end">Variation (F)</th>
                                 </tr>
                            </thead>
                            <tbody>
                                <?php foreach($articles as $a): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($a['code_article']) ?> </td>
                                    <td><?= htmlspecialchars($a['libelle']) ?> </td>
                                    <td class="text-center"><?= $a['stock_actuel'] ?> <?= $a['unite'] ?> </td>
                                    <td class="text-center">
                                        <input type="number" name="qte_reelle_<?= $a['id'] ?>" class="form-control form-control-sm" value="<?= $a['stock_actuel'] ?>" required>
                                    </td>
                                    <td id="ecart_<?= $a['id'] ?>" class="text-center">0</td>
                                    <td class="text-center"><?= number_format($a['prix_unitaire'], 0, ',', ' ') ?> F</td>
                                    <td id="variation_<?= $a['id'] ?>" class="text-end">0 F</td>
                                 </tr>
                                <?php endforeach; ?>
                            </tbody>
                         </table>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn-omega">Valider l'inventaire</button>
                    </div>
                </form>

                <!-- Schéma comptable -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">📊 Schéma comptable SYSCOHADA</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Cas 1 : Stock Final > Stock Initial (Stockage)</h6>
                                <pre class="bg-dark text-white p-2 rounded">
Compte 603 (Variation)    Compte 31 (Stock)
Débit: SI (5 000)         Crédit: SI (5 000)

Compte 31 (Stock)         Compte 603 (Variation)
Débit: SF (7 000)         Crédit: SF (7 000)

Solde 603 = 2 000 (Créditeur) → PRODUIT → Bénéfice
                                </pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Cas 2 : Stock Final < Stock Initial (Déstockage)</h6>
                                <pre class="bg-dark text-white p-2 rounded">
Compte 603 (Variation)    Compte 31 (Stock)
Débit: SI (10 000)        Crédit: SI (10 000)

Compte 31 (Stock)         Compte 603 (Variation)
Débit: SF (6 000)         Crédit: SF (6 000)

Solde 603 = 4 000 (Débiteur) → CHARGE → Perte
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcul automatique de l'écart et de la variation
<?php foreach($articles as $a): ?>
document.querySelector('input[name="qte_reelle_<?= $a['id'] ?>"]').addEventListener('input', function() {
    let theorique = <?= $a['stock_actuel'] ?>;
    let reel = parseInt(this.value) || 0;
    let ecart = reel - theorique;
    let prix = <?= $a['prix_unitaire'] ?>;
    let variation = ecart * prix;
    
    document.getElementById('ecart_<?= $a['id'] ?>').innerText = ecart;
    document.getElementById('variation_<?= $a['id'] ?>').innerHTML = (ecart >= 0 ? '+' : '') + variation.toLocaleString() + ' F';
});
<?php endforeach; ?>
</script>

<?php include 'inc_footer.php'; ?>
