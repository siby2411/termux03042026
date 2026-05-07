<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Compte de résultat";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

// Calcul automatique
$total_produits = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$total_charges = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$resultat = $total_produits - $total_charges;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-calculator"></i> Module : Le Compte de résultat</h5>
                <small>Mesurer la performance de l'entreprise</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 DÉFINITION :</strong>
                    <p>Le compte de résultat mesure la performance de l'entreprise sur un exercice en comparant les produits et les charges.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">
                                <h6>📈 PRODUITS (Classe 7)</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>70</strong> - Ventes de marchandises</li>
                                    <li><strong>71</strong> - Production stockée</li>
                                    <li><strong>72</strong> - Production immobilisée</li>
                                    <li><strong>75</strong> - Produits financiers</li>
                                    <li><strong>77</strong> - Produits exceptionnels</li>
                                </ul>
                                <h4 class="text-success text-end"><?= number_format($total_produits, 0, ',', ' ') ?> F</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-danger text-white">
                                <h6>📉 CHARGES (Classe 6)</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>60</strong> - Achats consommés</li>
                                    <li><strong>61-66</strong> - Services et personnel</li>
                                    <li><strong>67</strong> - Charges financières</li>
                                    <li><strong>68</strong> - Dotations amortissements</li>
                                    <li><strong>69</strong> - Impôts</li>
                                </ul>
                                <h4 class="text-danger text-end"><?= number_format($total_charges, 0, ',', ' ') ?> F</h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert <?= $resultat >= 0 ? 'alert-success' : 'alert-danger' ?> text-center mt-3">
                    <h4>RÉSULTAT NET = <?= number_format(abs($resultat), 0, ',', ' ') ?> FCFA</h4>
                    <p><?= $resultat >= 0 ? '✅ BÉNÉFICE' : '⚠️ PERTE' ?></p>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../compte_resultat.php" class="btn btn-success">Voir votre compte de résultat →</a>
                    <a href="sig.php" class="btn btn-info">Module suivant : SIG →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
