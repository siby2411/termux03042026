<?php
require_once __DIR__ . '/../includes/db.php';
$page_title = "Compte de Résultat - OMEGA";
include "layout.php";

// Produits (Classe 7)
$produits = $pdo->query("SELECT SUM(montant) FROM ECRITURES_COMPTABLES WHERE compte_credite_id LIKE '7%'")->fetchColumn() ?: 0;
// Charges (Classe 6)
$charges = $pdo->query("SELECT SUM(montant) FROM ECRITURES_COMPTABLES WHERE compte_debite_id LIKE '6%'")->fetchColumn() ?: 0;
$resultat = $produits - $charges;
?>

<div class="form-centered">
    <div class="card omega-card overflow-hidden">
        <div class="card-header bg-white py-4 text-center border-0">
            <h2 class="fw-bold text-dark">COMPTE DE RÉSULTAT (SYSCOHADA)</h2>
            <div class="badge bg-gold text-dark">Performance Annuelle 2026</div>
        </div>
        <div class="card-body p-5">
            <div class="row g-5">
                <div class="col-md-6">
                    <div class="p-4 rounded-4 bg-light border-start border-5 border-success">
                        <h4 class="text-success fw-bold">PRODUITS (7)</h4>
                        <p class="display-6 fw-bold"><?= number_format($produits, 0, ',', ' ') ?> F</p>
                        <small class="text-muted">Ventes de marchandises et services</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4 rounded-4 bg-light border-start border-5 border-danger">
                        <h4 class="text-danger fw-bold">CHARGES (6)</h4>
                        <p class="display-6 fw-bold"><?= number_format($charges, 0, ',', ' ') ?> F</p>
                        <small class="text-muted">Achats, salaires, impôts et loyers</small>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-center p-5 rounded-5 <?= $resultat >= 0 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                <h1 class="fw-bold">RÉSULTAT NET : <?= number_format($resultat, 0, ',', ' ') ?> F CFA</h1>
                <h3><?= $resultat >= 0 ? '🏆 BÉNÉFICE D\'EXPLOITATION' : '⚠️ PERTE D\'EXPLOITATION' ?></h3>
            </div>
        </div>
    </div>
</div>
