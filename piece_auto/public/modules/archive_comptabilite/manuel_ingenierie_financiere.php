<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Manuel ingénierie financière";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-building"></i> Manuel de formation : Ingénierie financière</h5>
                    <small>Évaluation d’entreprise, fusions-acquisitions, LBO, montages financiers</small>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>📚 SOMMAIRE</strong>
                        <ol class="mb-0 mt-2">
                            <li>Méthodes d’évaluation d’entreprise (DCF, comparables, actif net)</li>
                            <li>Coût du capital (WACC) et taux d’actualisation</li>
                            <li>Fusions – acquisitions (traitement comptable, goodwill)</li>
                            <li>LBO (Leveraged Buy-Out) – montage et effet de levier</li>
                            <li>Capital risque et capital développement</li>
                            <li>Calcul de la VAN, du TRI et du délai de récupération</li>
                            <li>Étude de cas : acquisition d’une société cible</li>
                        </ol>
                    </div>

                    <!-- 1. Évaluation -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">1. Méthodes d’évaluation d’entreprise</div><div class="card-body">
                        <ul><li><strong>Actualisation des flux de trésorerie (DCF)</strong> : Valeur = Σ FCF / (1+WACC)^n</li>
                        <li><strong>Comparables boursiers</strong> : multiple PER, EV/EBITDA</li>
                        <li><strong>Actif net comptable corrigé</strong> : ANCC = Actif réel – Dette réelle</li>
                        <li><strong>Rentabilité (Goodwill)</strong> : Valeur = ANCC + Superbénéfice / Taux de capitalisation</li></ul>
                    </div></div>

                    <!-- 2. WACC -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">2. Coût du capital (WACC) et taux d’actualisation</div><div class="card-body">
                        <div class="alert alert-primary">WACC = (Capitaux propres / V) × Ke + (Dettes / V) × Kd × (1 – IS)</div>
                        <p>Ke (coût des fonds propres) = taux sans risque + β × prime de risque marché.</p>
                    </div></div>

                    <!-- 3. Fusions -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">3. Fusions – acquisitions</div><div class="card-body">
                        <p>Écritures types (fusion absorption) :</p>
                        <pre class="bg-dark text-white p-2 rounded">
Actif de la société absorbée  DÉBIT
    Dettes de la société absorbée          CRÉDIT
    Capital (part de l’augmentation)       CRÉDIT
    Prime de fusion (différence)           CRÉDIT
Goodwill (écart d’acquisition)             DÉBIT si >0
                        </pre>
                    </div></div>

                    <!-- 4. LBO -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">4. LBO (Leveraged Buy-Out)</div><div class="card-body">
                        <p>Un LBO consiste à acquérir une entreprise en utilisant principalement de la dette (effet de levier). Le remboursement de la dette est assuré par les flux futurs de la cible.</p>
                        <ul><li>Holding d’acquisition (SPV)</li>
                        <li>Dette senior, mezzanine, fonds propres (sponsor)</li>
                        <li>Calcul du TRI du sponsor (effet de levier positif)</li></ul>
                    </div></div>

                    <!-- 5. Capital risque -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">5. Capital risque et capital développement</div><div class="card-body">
                        <p>Étapes : amorçage, start-up, développement, transmission. L’investisseur prend une participation minoritaire et sort par introduction en bourse ou revente stratégique.</p>
                    </div></div>

                    <!-- 6. VAN/TRI -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">6. VAN, TRI et délai de récupération</div><div class="card-body">
                        <div class="alert alert-success">VAN = Σ (FCF_t / (1+i)^t) – I0<br>TRI = taux i qui annule la VAN</div>
                        <p>Un projet est acceptable si VAN > 0 ou TRI > coût du capital.</p>
                    </div></div>

                    <!-- 7. Cas pratique -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">7. Cas pratique : acquisition d’une cible</div><div class="card-body">
                        <p>Données : flux FCF prévisionnels (k€) : 100, 120, 150, 160 ; valeur terminale = 2000 ; WACC = 10% ; investissement initial = 1500</p>
                        <div class="alert alert-warning">
                            VAN = -1500 + 100/1.1 + 120/1.1² + 150/1.1³ + 160/1.1⁴ + 2000/1.1⁴ ≈ 130 k€ → projet rentable.
                        </div>
                    </div></div>

                    <div class="alert alert-info mt-3">
                        <strong>🌐 MODULES ASSOCIÉS :</strong><br>
                        <a href="evaluation_entreprise.php" class="btn btn-sm btn-primary">Évaluation entreprise</a>
                        <a href="cout_capital.php" class="btn btn-sm btn-primary">Coût du capital (WACC)</a>
                        <a href="calculs_van_tri.php" class="btn btn-sm btn-primary">VAN / TRI</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
