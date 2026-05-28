<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Manuel évaluation d'entreprise";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-coin"></i> Manuel de formation : Évaluation d’entreprise</h5>
                    <small>Méthodes patrimoniales, comparables, DCF, Goodwill</small>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>📚 SOMMAIRE</strong>
                        <ol class="mb-0 mt-2">
                            <li>Approche patrimoniale (ANR, ANCC, valeur liquidative)</li>
                            <li>Approche par les flux (DCF, modèle de Gordon-Shapiro)</li>
                            <li>Approche par les multiples (PER, EV/EBITDA, EV/CA)</li>
                            <li>Méthode des praticiens (moyenne de plusieurs méthodes)</li>
                            <li>Goodwill et rente du bon vouloir (méthode des Anglo-Saxons)</li>
                            <li>Prime de contrôle et décote de minorité</li>
                            <li>Cas pratique : valorisation d’une PME</li>
                        </ol>
                    </div>

                    <!-- 1. Patrimoniale -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">1. Approche patrimoniale</div><div class="card-body">
                        <p><strong>ANR (Actif Net Réel)</strong> = Actif réel – Dettes réelles (après correction des immobilisations et créances).<br>
                        <strong>ANCC (Actif Net Comptable Corrigé)</strong> = ANR + Plus‑values latentes – Moins‑values latentes.<br>
                        <strong>Valeur liquidative</strong> = valeur de revente des actifs en cas de cessation.</p>
                    </div></div>

                    <!-- 2. DCF -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">2. Approche par les flux de trésorerie (DCF)</div><div class="card-body">
                        <div class="alert alert-primary">Valeur = Σ FCF_t / (1+i)^t + Valeur terminale / (1+i)^n</div>
                        <p>Le taux d’actualisation (i) = WACC (coût moyen pondéré du capital). La valeur terminale peut être calculée par le modèle de Gordon‑Shapiro : FCF_n+1 / (i – g).</p>
                    </div></div>

                    <!-- 3. Multiples -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">3. Approche par les multiples de marché</div><div class="card-body">
                        <ul><li><strong>PER</strong> = Prix / Bénéfice net → Valeur = PER moyen sectoriel × Bénéfice de la cible</li>
                        <li><strong>EV/EBITDA</strong> (Enterprise Value / EBITDA) → Valeur entreprise = Multiple × EBITDA</li>
                        <li><strong>EV/CA</strong> utilisé pour les entreprises à forte croissance mais pas encore rentables</li></ul>
                    </div></div>

                    <!-- 4. Méthode des praticiens -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">4. Méthode des praticiens (moyenne)</div><div class="card-body">
                        <p>Elle consiste à calculer la moyenne pondérée de la valeur patrimoniale (ANCC) et de la valeur de rendement (capacité bénéficiaire / taux de capitalisation).</p>
                        <div class="alert alert-success">Valeur = (ANCC × 2 + Valeur de rendement) / 3</div>
                    </div></div>

                    <!-- 5. Goodwill -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">5. Goodwill et rente du bon vouloir</div><div class="card-body">
                        <p>Le Goodwill représente la différence entre la valeur d’entreprise et l’ANCC. Il incorpore la clientèle, la marque, la rentabilité future.</p>
                        <p>Méthode des Anglo‑Saxons : Goodwill = (Bénéfice net – ANCC × taux d’intérêt sans risque) / taux de capitalisation.</p>
                    </div></div>

                    <!-- 6. Primes / décotes -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">6. Prime de contrôle et décote de minorité</div><div class="card-body">
                        <p>Un bloc majoritaire bénéficie d’une prime (souvent 20‑30%). Une participation minoritaire subit une décote (10‑25%) car elle ne permet pas de piloter la stratégie.</p>
                    </div></div>

                    <!-- 7. Cas pratique -->
                    <div class="card mb-3"><div class="card-header bg-secondary text-white">7. Cas pratique : PME de 10 M€ de CA</div><div class="card-body">
                        <p>Données : ANCC = 8 M€, Bénéfice net = 1,2 M€, taux sans risque = 5%, prime de risque = 7%, croissance long terme = 2%.</p>
                        <p>WACC = 12% ; Valeur de rendement = 1,2 / (12% – 2%) = 12 M€. Valeur mixte = (8 + 12) / 2 = 10 M€.<br>
                        Après prime de contrôle (20%) → 12 M€.</p>
                    </div></div>

                    <div class="alert alert-info mt-3">
                        <strong>🌐 MODULES ASSOCIÉS :</strong><br>
                        <a href="evaluation_entreprise.php" class="btn btn-sm btn-primary">Simulateur DCF</a>
                        <a href="gordon_shapiro.php" class="btn btn-sm btn-primary">Modèle Gordon-Shapiro</a>
                        <a href="cout_capital.php" class="btn btn-sm btn-primary">Calcul du WACC</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
