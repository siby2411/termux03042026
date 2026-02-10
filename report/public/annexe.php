<?php
// public/annexes.php
$page_title = "Annexes & Fonctionnalités - SynthesePro";
require_once 'layout.php';
require_once __DIR__ . '/../config/database.php';

// --- normaliser $pdo (compatible avec différents database.php) ---
if (function_exists('getConnection')) {
    $pdo = getConnection();
} elseif (isset($conn) && $conn instanceof PDO) {
    $pdo = $conn;
} elseif (isset($db) && $db instanceof PDO) {
    $pdo = $db;
} else {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=synthesepro_db;charset=utf8mb4', 'root', '123', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die("Erreur DB fallback : " . $e->getMessage());
    }
}
?>
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col">
      <h1 class="h3 mb-1">Annexes — Fonctionnalités & Outils d’analyse</h1>
      <p class="text-muted">Présentation synthétique des modules, des outils d’analyse financière intégrés, des avantages et de la logique d’implémentation. Intégré au layout SynthesePro.</p>
    </div>
  </div>

  <!-- Top cards : modules principaux -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-journal-text me-2"></i> Comptabilité</h5>
          <p class="card-text small text-muted">Saisie et gestion des écritures, plan comptable, balance, grand livre, bilan et compte de résultat.</p>
          <ul class="list-unstyled small mb-0">
            <li><a href="ecriture.php">Saisie d'écriture</a></li>
            <li><a href="list_ecriture.php">Liste des écritures</a></li>
            <li><a href="comptes.php">Plan comptable</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-gear-wide-connected me-2"></i> Analyse & SIG</h5>
          <p class="card-text small text-muted">Soldes Intermédiaires de Gestion (SIG), génération de bilans, résultat, SIG snapshots et exports.</p>
          <ul class="list-unstyled small mb-0">
            <li><a href="sig.php">SIG (Soldes intermédiaires)</a></li>
            <li><a href="bilan.php">Bilan</a></li>
            <li><a href="resultat.php">Compte de résultat</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-boxes me-2"></i> Stocks & Immobilisations</h5>
          <p class="card-text small text-muted">Gestion des mouvements de stock, inventaire, immobilisations et amortissements.</p>
          <ul class="list-unstyled small mb-0">
            <li><a href="list_stock.php">Mouvements de stock</a></li>
            <li><a href="immobilisations.php">Immobilisations</a></li>
            <li><a href="amortissements.php">Amortissements</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Tools & Analysis -->
  <div class="row g-3 mb-4">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Outils d’analyse financière intégrés</h5>
          <div class="row">
            <div class="col-md-6">
              <h6 class="small text-muted">SIG & KPI</h6>
              <ul>
                <li>Chiffre d’affaires, Valeur ajoutée, EBE, Résultat d’exploitation, Résultat net</li>
                <li>KPI : trésorerie, marge nette, marge brute, rotation des stocks</li>
                <li>Snapshots SIG (sauvegarde & export CSV)</li>
              </ul>
            </div>
            <div class="col-md-6">
              <h6 class="small text-muted">Ratios & Diagnostics</h6>
              <ul>
                <li>Liquidité générale / réduite / immédiate</li>
                <li>Autonomie financière, taux d’endettement</li>
                <li>Rentabilité économique et financière</li>
              </ul>
            </div>
          </div>

          <hr>

          <h6 class="small text-muted">Visualisation</h6>
          <p class="small mb-0">Graphiques interactifs (Chart.js) sur le dashboard pour tendances, comparaisons année sur année et analyses sectorielles.</p>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Contrôles & Gouvernance</h5>
          <ul>
            <li>Rapprochement bancaire (rapprochement.php)</li>
            <li>Contrôle budgétaire (controle_budget.php) : budgets vs réalisés, alertes</li>
            <li>Contrôle de gestion (controle_gestion.php) : indicateurs, seuils, alertes</li>
            <li>Gestion des régularisations (reg_passif.php)</li>
          </ul>
          <hr>
          <h6 class="small text-muted">Export & Reporting</h6>
          <p class="small mb-0">Exports CSV / XLSX (via PHPSpreadsheet) et PDF (Dompdf) pour bilans, SIG et états financiers.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Advantages -->
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Avantages clés</h5>
          <ul>
            <li><strong>Conformité SYSCOHADA / UEMOA</strong> : plan comptable et flux conçus pour la région.</li>
            <li><strong>Automatisation</strong> : calculs de SIG, ratios et génération d’états.</li>
            <li><strong>Traçabilité</strong> : audit log, snapshots, et historique des écritures.</li>
            <li><strong>Extensible</strong> : modules futurs (rapprochement, contrôle budgetaire, contrôle gestion).</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Sécurité & Architecture</h5>
          <ul>
            <li>Connexion PDO centralisée (même logique que <code>bilan.php</code>).</li>
            <li>Pages protégées via <code>includes/auth_check.php</code>.</li>
            <li>Layout unique (sidebar + topbar) pour une UX cohérente.</li>
            <li>Possibilité d’ajouter rôle/permissions utilisateurs (module USERS existant).</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Implementation logic -->
  <div class="row g-3 mb-5">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Logique d’implémentation (architecture & flux)</h5>

          <ol>
            <li><strong>DB centralisée</strong> — tables maîtresses : <code>PLAN_COMPTABLE_UEMOA</code>, <code>ECRITURES_COMPTABLES</code>, <code>SYNTHESES_BALANCE</code>, <code>ECRITURES_STOCK</code>, <code>AMORTISSEMENTS</code>.</li>
            <li><strong>Connexion</strong> — normalisation PDO utilisée partout (voir bloc copié depuis <code>bilan.php</code>) ; garantit compatibilité avec plusieurs fichiers <code>database.php</code>.</li>
            <li><strong>Modules</strong> — routes/pages frontales : saisie, listes, SIG, bilans, résultat, rapprochement, contrôles. Chaque page inclut <code>layout.php</code> pour cohérence UI.</li>
            <li><strong>Analytique</strong> — endpoints JSON (ex : <code>api/chart_ca.php?year=2025</code>) pour Chart.js ; traitement côté PHP, visualisation côté client.</li>
            <li><strong>Exports & snapshots</strong> — SIG snapshots, exports CSV/XLSX/PDF, sauvegarde des états pour audits.</li>
            <li><strong>Roadmap</strong> — ajouter alertes temps réel, génération automatique d’états (PDF/XLSX), droits avancés par rôle et API REST pour intégration ERP.</li>
          </ol>

          <hr>

          <p class="small text-muted mb-0">Remarque technique : conservez le même modèle de connexion (le code utilisé dans <code>bilan.php</code>) — toutes les pages futures doivent le réutiliser pour assurer compatibilité et suppression des erreurs <code>getConnection()</code> / <code>query() on string</code>.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick links footer -->
  <div class="row mb-5">
    <div class="col">
      <div class="card p-3 shadow-sm">
        <h6 class="mb-2">Liens rapides</h6>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-outline-primary btn-sm" href="admin_dashboard.php">Dashboard</a>
          <a class="btn btn-outline-primary btn-sm" href="bilan.php">Bilan</a>
          <a class="btn btn-outline-primary btn-sm" href="resultat.php">Résultat</a>
          <a class="btn btn-outline-primary btn-sm" href="sig.php">SIG</a>
          <a class="btn btn-outline-primary btn-sm" href="list_ecriture.php">Écritures</a>
          <a class="btn btn-outline-primary btn-sm" href="list_stock.php">Stocks</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- fin du contenu page : coller la fermeture attendue par layout.php -->
    </section>
  </main>
</div>

<!-- JS: Bootstrap bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Petite animation / interaction
  document.querySelectorAll('.card').forEach(c => {
      c.addEventListener('mouseenter', () => c.classList.add('shadow-lg'));
      c.addEventListener('mouseleave', () => c.classList.remove('shadow-lg'));
  });
</script>

</body>
</html>

