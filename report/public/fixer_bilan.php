<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$page_title = "Fixer le bilan - Correction des écarts";
$page_icon = "wrench";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exercice = $_POST['exercice'] ?? date('Y');
    
    try {
        $pdo->beginTransaction();
        
        // 1. Calcul du résultat net
        $total_produits = $pdo->prepare("
            SELECT COALESCE(SUM(montant), 0) 
            FROM ECRITURES_COMPTABLES 
            WHERE YEAR(date_ecriture) = ? 
            AND compte_credite_id BETWEEN 700 AND 799
        ");
        $total_produits->execute([$exercice]);
        $produits = $total_produits->fetchColumn();
        
        $total_charges = $pdo->prepare("
            SELECT COALESCE(SUM(montant), 0) 
            FROM ECRITURES_COMPTABLES 
            WHERE YEAR(date_ecriture) = ? 
            AND compte_debite_id BETWEEN 600 AND 699
        ");
        $total_charges->execute([$exercice]);
        $charges = $total_charges->fetchColumn();
        
        $resultat = $produits - $charges;
        
        // 2. Création de l'écriture de clôture si besoin
        if ($resultat != 0) {
            $stmt = $pdo->prepare("
                INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture)
                VALUES (CURDATE(), 'Clôture résultat N', 120, 112, ?, 'CLOTURE-N', 'CLOTURE')
            ");
            $stmt->execute([$resultat]);
            $message = "✅ Écriture de clôture ajoutée. Résultat : " . number_format($resultat, 0, ',', ' ') . " FCFA";
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur : " . $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-wrench"></i> Fixer le bilan - Correction des écarts</h5>
                <small>Ajoute automatiquement les écritures manquantes pour équilibrer le bilan</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">Correction automatique</div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label>Exercice à corriger</label>
                                        <select name="exercice" class="form-select">
                                            <option value="2026">2026</option>
                                            <option value="2025">2025</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-omega w-100">Corriger le bilan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-header">📖 Actions recommandées</div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>1. Vérifier les écritures non lettrées</li>
                                    <li>2. Passer les amortissements manquants</li>
                                    <li>3. Clôturer les comptes de gestion</li>
                                    <li>4. Recalculer le bilan après correction</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
