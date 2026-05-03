<?php
session_start();
require_once 'db_connect_ecole.php';
require_once 'header_ecole.php';

// Vérification du rôle
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'inscription' && $_SESSION['role'] !== 'admin')) {
    $_SESSION['message'] = "Accès non autorisé.";
    header("Location: login.php");
    exit();
}

$conn = db_connect_ecole();
$annee_actuelle = date('Y') . '-' . (date('Y') + 1);
$etudiant_code = ''; 
$etudiant_data = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // ACTION : RECHERCHER ÉTUDIANT
    if ($_POST['action'] === 'rechercher' && !empty($_POST['code_etudiant'])) {
        $etudiant_code = $conn->real_escape_string($_POST['code_etudiant']);
        
        $sql = "SELECT e.*, f.nom_filiere, c.nom_class, c.id AS id_classe
                FROM etudiants e
                JOIN classes c ON e.classe_id = c.id
                JOIN filieres f ON c.filiere_id = f.id
                WHERE e.code_etudiant = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $etudiant_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $etudiant_data = $result->fetch_assoc();

            // Récupérer les tarifs de la classe
            $stmt_t = $conn->prepare("SELECT montant_scolarite, droit_inscription FROM tarifs WHERE classe_id = ?");
            $stmt_t->bind_param("i", $etudiant_data['id_classe']);
            $stmt_t->execute();
            $etudiant_data['couts'] = $stmt_t->get_result()->fetch_assoc();

            // Récupérer paiement inscription
            $stmt_p = $conn->prepare("SELECT SUM(montant_paye) as total FROM paiements WHERE code_etudiant = ? AND type_paiement = 'Inscription'");
            $stmt_p->bind_param("s", $etudiant_code);
            $stmt_p->execute();
            $etudiant_data['montant_deja_paye'] = $stmt_p->get_result()->fetch_assoc()['total'] ?? 0;

            // RÉCUPÉRATION DES UV DE LA CLASSE
            $stmt_uv = $conn->prepare("SELECT uv.*, m.nom_matiere FROM unites_valeur uv JOIN matieres m ON uv.matiere_id = m.id WHERE uv.classe_id = ? ORDER BY uv.semestre ASC");
            $stmt_uv->bind_param("i", $etudiant_data['id_classe']);
            $stmt_uv->execute();
            $etudiant_data['uv_list'] = $stmt_uv->get_result()->fetch_all(MYSQLI_ASSOC);
            
        } else {
            $message = "Aucun étudiant trouvé.";
        }
    } 
    
    // ACTION : VALIDER PAIEMENT
    elseif ($_POST['action'] === 'valider_inscription') {
        $code = $_POST['code_etudiant_valider'];
        $montant = (float)$_POST['montant_inscription'];
        
        $stmt = $conn->prepare("INSERT INTO paiements (code_etudiant, annee_academique, type_paiement, montant_paye, date_paiement) VALUES (?, ?, 'Inscription', ?, CURDATE())");
        $stmt->bind_param("ssd", $code, $annee_actuelle, $montant);
        
        if ($stmt->execute()) {
            $message = "Paiement validé avec succès !";
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="text-success fw-bold mb-4"><i class="bi bi-person-check-fill"></i> Gestion des Inscriptions & UV</h1>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 p-4">
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="rechercher">
            <div class="col-md-9">
                <input type="text" name="code_etudiant" class="form-control form-control-lg" placeholder="Entrez le matricule de l'étudiant" value="<?= $etudiant_code ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success btn-lg w-100">Rechercher</button>
            </div>
        </form>
    </div>

    <?php if ($etudiant_data): ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow border-0 p-4 h-100">
                <h4 class="text-primary border-bottom pb-2">Détails Étudiant</h4>
                <p><strong>Nom :</strong> <?= $etudiant_data['nom'] ?> <?= $etudiant_data['prenom'] ?></p>
                <p><strong>Classe :</strong> <?= $etudiant_data['nom_class'] ?></p>
                <p><strong>Filière :</strong> <?= $etudiant_data['nom_filiere'] ?></p>
                
                <h4 class="text-warning border-bottom pb-2 mt-4">Statut Financier Inscription</h4>
                <?php 
                    $du = $etudiant_data['couts']['droit_inscription'] ?? 0;
                    $paye = $etudiant_data['montant_deja_paye'];
                    $reste = max(0, $du - $paye);
                ?>
                <div class="alert <?= ($reste <= 0) ? 'alert-success' : 'alert-danger' ?>">
                    Total Droit : <?= number_format($du, 0) ?> FCFA<br>
                    Payé : <?= number_format($paye, 0) ?> FCFA<br>
                    <strong>Reste : <?= number_format($reste, 0) ?> FCFA</strong>
                </div>

                <?php if ($reste > 0): ?>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="action" value="valider_inscription">
                    <input type="hidden" name="code_etudiant_valider" value="<?= $etudiant_data['code_etudiant'] ?>">
                    <div class="input-group">
                        <input type="number" name="montant_inscription" class="form-control" max="<?= $reste ?>" required>
                        <button class="btn btn-primary">Payer</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow border-0 p-4 h-100">
                <h4 class="text-dark border-bottom pb-2">Programme des UV (<?= $etudiant_data['nom_class'] ?>)</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Semestre</th>
                                <th>Unité de Valeur (UV)</th>
                                <th>Coef</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($etudiant_data['uv_list'] as $uv): ?>
                            <tr>
                                <td>S<?= $uv['semestre'] ?></td>
                                <td><strong><?= $uv['nom_uv'] ?></strong><br><small><?= $uv['nom_matiere'] ?></small></td>
                                <td><?= $uv['coefficient'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer_ecole.php'; ?>
