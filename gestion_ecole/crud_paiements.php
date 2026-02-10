<?php
// Fichier : crud_paiements.php - Gestion des paiements de Scolarité
// RÔLE D'ACCÈS : ADMINISTRATEUR UNIQUEMENT

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php'; 

// Vérification de la session Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Accès non autorisé.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php"); 
    exit();
}

$conn = db_connect_ecole();
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Initialisation des variables de formulaire
$code_etudiant = '';
$montant_paye = '';
$type_paiement = 'Scolarite';
$date_paiement = date('Y-m-d');
$mode_paiement = 'Espèces';
$code_transaction = '';
$annee_academique_en_cours = date('Y') . '-' . (date('Y') + 1); // Exemple: 2025-2026

$message_tarif = '';
$montant_du_total = 0;
$montant_deja_paye = 0;
$montant_restant_du = 0;

// --- GESTION DU FORMULAIRE ET INITIALISATION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_paiement'])) {
    
    // 1. Récupération des données POST
    $code_etudiant = trim($_POST['code_etudiant']);
    $montant_paye = floatval($_POST['montant_paye']);
    $type_paiement = $_POST['type_paiement'];
    $date_paiement = $_POST['date_paiement'];
    $mode_paiement = $_POST['mode_paiement'];
    // Utilisez NULL si le champ est vide pour permettre à la DB d'appliquer l'UNIQUE KEY correctement
    $code_transaction = !empty(trim($_POST['code_transaction'])) ? trim($_POST['code_transaction']) : NULL;
    $annee_paiement = $_POST['annee_paiement'];

    // 2. Validation basique
    if (empty($code_etudiant) || $montant_paye <= 0 || empty($date_paiement) || empty($type_paiement)) {
        $_SESSION['message'] = "Veuillez remplir tous les champs obligatoires (Code Étudiant, Montant, Type, Date).";
        $_SESSION['msg_type'] = "danger";
        header("Location: crud_paiements.php"); exit();
    }

    // 3. Insertion du Paiement en transaction
    $conn->begin_transaction();
    try {
        // Préparer l'insertion
        $stmt_insert = $conn->prepare("INSERT INTO paiements (code_etudiant, annee_academique, type_paiement, montant_paye, date_paiement, mode_paiement, code_transaction) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssdsss", $code_etudiant, $annee_paiement, $type_paiement, $montant_paye, $date_paiement, $mode_paiement, $code_transaction);
        
        if (!$stmt_insert->execute()) {
             throw new Exception("Erreur lors de l'enregistrement du paiement : " . $stmt_insert->error);
        }
        $stmt_insert->close();

        $conn->commit();
        $_SESSION['message'] = "Paiement de **{$montant_paye} €** (Type: {$type_paiement}) pour l'étudiant **{$code_etudiant}** enregistré avec succès.";
        $_SESSION['msg_type'] = "success";

    } catch (Exception $e) {
        $conn->rollback();
        // Gérer spécifiquement les erreurs de doublon de code_transaction
        if (strpos($e->getMessage(), 'Duplicate entry') !== false && $code_transaction != NULL) {
            $_SESSION['message'] = "Erreur: Ce code de transaction ({$code_transaction}) a déjà été utilisé.";
        } else {
            $_SESSION['message'] = "Erreur fatale lors de l'enregistrement: " . $e->getMessage();
        }
        $_SESSION['msg_type'] = "danger";
    }

    // Rediriger avec le code étudiant pour réafficher le solde
    header("Location: crud_paiements.php?code_etudiant=" . urlencode($code_etudiant));
    exit();
}

// 4. LOGIQUE DE CALCUL (Affichage des soldes)
if (isset($_GET['code_etudiant']) && !empty($_GET['code_etudiant'])) {
    $code_etudiant_check = trim($_GET['code_etudiant']);
    
    // a. Récupérer les informations de l'étudiant et de sa filière/cycle
    $stmt_info = $conn->prepare("
        SELECT 
            e.id_classe, c.id_filiere, c.cycle, c.annee_academique, f.nom_filiere
        FROM 
            etudiants e
        JOIN classes c ON e.id_classe = c.id_classe
        JOIN filieres f ON c.id_filiere = f.id_filiere
        WHERE e.code_etudiant = ?
    ");
    $stmt_info->bind_param("s", $code_etudiant_check);
    $stmt_info->execute();
    $info = $stmt_info->get_result()->fetch_assoc();
    $stmt_info->close();

    if ($info) {
        $id_filiere = $info['id_filiere'];
        $cycle = $info['cycle'];
        $annee_etudiant = $info['annee_academique'];
        $nom_filiere = $info['nom_filiere'];

        // b. Récupérer le tarif pour cette filière/cycle/année
        $stmt_tarif = $conn->prepare("
            SELECT montant_inscription, montant_scolarite_annuel
            FROM tarifs
            WHERE id_filiere = ? AND cycle = ? AND annee_academique = ?
        ");
        $stmt_tarif->bind_param("iss", $id_filiere, $cycle, $annee_etudiant);
        $stmt_tarif->execute();
        $tarif = $stmt_tarif->get_result()->fetch_assoc();
        $stmt_tarif->close();

        if ($tarif) {
            $montant_du_inscription = $tarif['montant_inscription'];
            $montant_du_scolarite = $tarif['montant_scolarite_annuel'];
            $montant_du_total = $montant_du_inscription + $montant_du_scolarite;

            // c. Calculer les paiements déjà effectués pour cette année
            $stmt_paye = $conn->prepare("
                SELECT SUM(montant_paye) as total_paye
                FROM paiements
                WHERE code_etudiant = ? AND annee_academique = ?
            ");
            $stmt_paye->bind_param("ss", $code_etudiant_check, $annee_etudiant);
            $stmt_paye->execute();
            $montant_deja_paye = $stmt_paye->get_result()->fetch_assoc()['total_paye'] ?? 0;
            $stmt_paye->close();

            // d. Calculer le solde
            $montant_restant_du = $montant_du_total - $montant_deja_paye;
            
            $message_tarif = "Étudiant : **{$code_etudiant_check}** (Classe: {$info['id_classe']}, Année {$annee_etudiant})<br>" .
                             "Filière/Cycle : **{$nom_filiere} / {$cycle}**<hr>" .
                             "- Droits d'Inscription: **" . number_format($montant_du_inscription, 2) . " €**<br>" .
                             "- Scolarité Annuelle: **" . number_format($montant_du_scolarite, 2) . " €**<br>" .
                             "- **TOTAL DÛ:** <span class='text-primary fw-bold'>" . number_format($montant_du_total, 2) . " €</span><br>" .
                             "- **Déjà Payé:** <span class='text-success fw-bold'>" . number_format($montant_deja_paye, 2) . " €</span><br>" .
                             "- **SOLDE RESTANT:** <span class='text-" . ($montant_restant_du > 0 ? 'danger' : 'success') . " fw-bold'>" . number_format($montant_restant_du, 2) . " €</span>";
        
        } else {
             $message_tarif = "<span class='text-danger'>ATTENTION: Aucun tarif défini pour la filière **{$nom_filiere}** et le cycle **{$cycle}** pour l'année **{$annee_etudiant}**.</span>";
             $montant_restant_du = 0;
        }
        $annee_paiement_form = $annee_etudiant;

    } else {
        $message_tarif = "<span class='text-danger'>Étudiant non trouvé avec le code **{$code_etudiant_check}**.</span>";
        $montant_restant_du = 0;
        $annee_paiement_form = $annee_academique_en_cours;
    }

    $code_etudiant = $code_etudiant_check;
    // Pré-remplir le montant par le solde restant si positif
    $montant_paye = $montant_restant_du > 0 ? round($montant_restant_du, 2) : ''; 
} else {
    $annee_paiement_form = $annee_academique_en_cours;
}

// 5. Récupérer l'historique des paiements pour l'affichage
$paiements_result = $conn->query("SELECT p.*, e.nom, e.prenom FROM paiements p JOIN etudiants e ON p.code_etudiant = e.code_etudiant ORDER BY p.date_paiement DESC, p.id_paiement DESC LIMIT 10");

$conn->close();

// 6. Affichage HTML
include 'header_ecole.php'; 
?>

<h1 class="mb-4">💰 Gestion des Paiements Scolaires</h1>

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo $_SESSION['msg_type']; ?> mt-3">
    <?php echo $_SESSION['message']; ?>
</div>
<?php 
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
endif; 
?>

<div class="row">
    <!-- Formulaire d'Enregistrement de Paiement -->
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Enregistrer une Transaction</h5>
            </div>
            <div class="card-body">
                
                <!-- Formulaire de Vérification du Code Étudiant -->
                <form action="crud_paiements.php" method="GET" class="mb-4 border p-3 rounded bg-light">
                    <div class="form-group">
                        <label for="code_etudiant_check" class="fw-bold">1. Rechercher par Code Étudiant</label>
                        <input type="text" name="code_etudiant" id="code_etudiant_check" class="form-control" value="<?php echo htmlspecialchars($code_etudiant); ?>" placeholder="Ex: L1INFO2025001" required>
                    </div>
                    <button type="submit" class="btn btn-info btn-block mt-2">Vérifier Solde Actuel</button>
                </form>

                <?php if ($message_tarif): ?>
                    <div class="alert alert-light border border-secondary mb-4 p-3">
                        <h6>Infos Solde & Tarifs :</h6>
                        <?php echo $message_tarif; ?>
                    </div>
                <?php endif; ?>

                <form action="crud_paiements.php" method="POST">
                    <!-- Champ caché pour l'année académique de l'étudiant -->
                    <input type="hidden" name="annee_paiement" value="<?php echo htmlspecialchars($annee_paiement_form); ?>">
                    
                    <div class="form-group mb-3">
                        <label for="code_etudiant" class="fw-bold">2. Code Étudiant (Confirmation)</label>
                        <input type="text" name="code_etudiant" id="code_etudiant" class="form-control" value="<?php echo htmlspecialchars($code_etudiant); ?>" required readonly style="background-color: #e9ecef;">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="type_paiement">Type de Frais</label>
                        <select name="type_paiement" id="type_paiement" class="form-control" required>
                            <option value="Scolarite" <?php echo ($type_paiement == 'Scolarite') ? 'selected' : ''; ?>>Scolarité</option>
                            <option value="Inscription" <?php echo ($type_paiement == 'Inscription') ? 'selected' : ''; ?>>Droits d'Inscription</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="montant_paye">Montant Payé (€)</label>
                        <input type="number" step="0.01" name="montant_paye" id="montant_paye" class="form-control" value="<?php echo htmlspecialchars($montant_paye); ?>" required min="0.01">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="date_paiement">Date du Paiement</label>
                        <input type="date" name="date_paiement" id="date_paiement" class="form-control" value="<?php echo htmlspecialchars($date_paiement); ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="mode_paiement">Mode de Paiement</label>
                        <select name="mode_paiement" id="mode_paiement" class="form-control" required>
                            <option value="Espèces">Espèces</option>
                            <option value="Virement">Virement Bancaire</option>
                            <option value="Chèque">Chèque</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Carte Bancaire">Carte Bancaire</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="code_transaction">Code Transaction/Référence (Optionnel, Unique)</label>
                        <input type="text" name="code_transaction" id="code_transaction" class="form-control" value="<?php echo htmlspecialchars($code_transaction); ?>" placeholder="Référence externe (ex: Mobile Money ID)">
                    </div>

                    <button type="submit" name="save_paiement" class="btn btn-success btn-block mt-3" <?php echo empty($code_etudiant) ? 'disabled' : ''; ?>>
                        3. Enregistrer la Transaction
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Liste des derniers paiements (READ) -->
    <div class="col-md-6">
        <h2 class="mt-4 mt-md-0">Historique des 10 Derniers Paiements</h2>
        <?php 
        if ($paiements_result && $paiements_result->num_rows > 0):
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>Code</th>
                        <th>Nom & Prénom</th>
                        <th>Montant</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $paiements_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['code_etudiant']); ?></td>
                        <td><?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?></td>
                        <td>**<?php echo number_format($row['montant_paye'], 2); ?> €**</td>
                        <td><span class="badge bg-<?php echo ($row['type_paiement'] == 'Inscription') ? 'warning' : 'info'; ?>"><?php echo $row['type_paiement']; ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($row['date_paiement'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">Aucun paiement enregistré pour l'instant.</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
