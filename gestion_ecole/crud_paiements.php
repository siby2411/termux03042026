<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$message_solde = "";
$code_etu = $_GET['code_etudiant'] ?? '';

// Logique de calcul de solde simplifiée pour le design Gold
if (!empty($code_etu)) {
    $res = $conn->query("SELECT e.nom, e.prenom, c.nom_class FROM etudiants e JOIN classes c ON e.classe_id = c.id WHERE e.code_etudiant = '$code_etu'");
    if($info = $res->fetch_assoc()) {
        $message_solde = "Caisse : <b>".$info['nom']." ".$info['prenom']."</b> (".$info['nom_class'].")";
    }
}

include 'header_ecole.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="bi bi-cash-coin me-2"></i>Encaisser un Paiement
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <label class="small fw-bold">1. Rechercher Étudiant (Code)</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="code_etudiant" class="form-control" value="<?= $code_etu ?>" placeholder="ex: ETU-2026-001">
                            <button class="btn btn-dark">Vérifier</button>
                        </div>
                    </form>

                    <?php if($message_solde): ?>
                        <div class="alert alert-info small py-2"><?= $message_solde ?></div>
                        <form method="POST" action="save_paiement.php">
                            <input type="hidden" name="code_etudiant" value="<?= $code_etu ?>">
                            <div class="mb-2">
                                <label class="small">Type de Frais</label>
                                <select name="type" class="form-select form-select-sm">
                                    <option value="Scolarite">Scolarité</option>
                                    <option value="Inscription">Inscription</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="small">Montant (€ / FCFA)</label>
                                <input type="number" name="montant" class="form-control form-control-sm" required>
                            </div>
                            <button class="btn btn-success btn-sm w-100 fw-bold mt-2">Valider l'Encaissement</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold text-uppercase small">Dernières Transactions</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Date</th>
                                <th>Étudiant</th>
                                <th>Type</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php 
                            $hist = $conn->query("SELECT p.*, e.nom FROM paiements p JOIN etudiants e ON p.code_etudiant = e.code_etudiant ORDER BY p.id_paiement DESC LIMIT 10");
                            while($p = $hist->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d/m/y', strtotime($p['date_paiement'])) ?></td>
                                <td><?= $p['nom'] ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $p['type_paiement'] ?></span></td>
                                <td class="text-end fw-bold"><?= number_format($p['montant_paye'], 0) ?> €</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
