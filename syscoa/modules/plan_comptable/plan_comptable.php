<?php
// modules/plan_comptable/plan_comptable.php - Plan Comptable OHADA

// Démarrer le buffer de sortie pour éviter les erreurs d'en-têtes
ob_start();

// Vérification de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier l'authentification
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    echo "<script>window.location.href = \"../login.php\";</script>";
    exit();
}

// Inclure la configuration de la base de données
require_once dirname(__DIR__) . "/../config/database.php";

// Récupérer l'exercice courant
$id_exercice = isset($_SESSION["id_exercice"]) ? $_SESSION["id_exercice"] : 1;

// Requête pour récupérer le plan comptable
$sql = "SELECT * FROM plan_comptable WHERE id_exercice = :id_exercice ORDER BY numero_compte";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id_exercice" => $id_exercice]);
$comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1><i class="fas fa-book"></i> Plan Comptable OHADA</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Liste des comptes</h5>
        </div>
        <div class="card-body">
            <?php if (empty($comptes)): ?>
                <div class="alert alert-warning">
                    Aucun compte trouvé. Veuillez d'abord importer ou créer un plan comptable.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Intitulé</th>
                                <th>Classe</th>
                                <th>Type</th>
                                <th>Solde Initial</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comptes as $compte): ?>
                                <tr>
                                    <td><?= htmlspecialchars($compte["numero_compte"]) ?></td>
                                    <td><?= htmlspecialchars($compte["intitule"]) ?></td>
                                    <td><?= htmlspecialchars($compte["classe"]) ?></td>
                                    <td>
                                        <span class="badge <?= $compte["type"] == 'actif' ? 'bg-primary' : ($compte["type"] == 'passif' ? 'bg-success' : 'bg-secondary') ?>">
                                            <?= ucfirst($compte["type"]) ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><?= number_format($compte["solde_initial"], 2, ',', ' ') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
?>
