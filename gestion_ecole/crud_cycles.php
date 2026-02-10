<?php
// Fichier : crud_cycles.php - Gestion des Cycles (L1, M2, etc.)

// Démarrer la session pour les messages de succès/erreur
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect_ecole.php';

// Redirection si non connecté (Protection de base)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = db_connect_ecole();

// Initialisation des variables pour l'édition
$is_editing = false;
$id_cycle = 0;
$nom_cycle = '';
$annee_etude = '';
$duree_semestres = 2; 

// --- Fonction pour Afficher les Messages de Session ---
function display_session_message() {
    if (isset($_SESSION['message']) && isset($_SESSION['msg_type'])) {
        $alert_class = 'alert-' . $_SESSION['msg_type'];
        $icon = ($_SESSION['msg_type'] === 'success') ? 'check-circle' : 'exclamation-triangle';
        echo "<div class='alert {$alert_class} alert-dismissible fade show' role='alert'>
                <i class='bi bi-{$icon} me-2'></i>" . $_SESSION['message'] . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
    }
}
// -------------------------------------------------------------------
// 1. GESTION DES REQUÊTES (INSERT, UPDATE, DELETE)
// -------------------------------------------------------------------

// Traitement de l'ajout/modification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    
    // Utilisation des filtres modernes pour la sécurité et le nettoyage
    $id_cycle = filter_input(INPUT_POST, 'id_cycle', FILTER_VALIDATE_INT);
    $nom_cycle = filter_input(INPUT_POST, 'nom_cycle', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $annee_etude = filter_input(INPUT_POST, 'annee_etude', FILTER_VALIDATE_INT);
    $duree_semestres = filter_input(INPUT_POST, 'duree_semestres', FILTER_VALIDATE_INT);
    
    // Validation minimale
    if ($nom_cycle && $annee_etude > 0 && $duree_semestres > 0) {
        
        if ($id_cycle > 0) {
            // MISE À JOUR (UPDATE)
            $stmt = $conn->prepare("UPDATE cycles SET nom_cycle=?, annee_etude=?, duree_semestres=? WHERE id_cycle=?");
            $stmt->bind_param("siii", $nom_cycle, $annee_etude, $duree_semestres, $id_cycle);
            $action_text = "modifié";
        } else {
            // AJOUT (INSERT)
            $stmt = $conn->prepare("INSERT INTO cycles (nom_cycle, annee_etude, duree_semestres) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $nom_cycle, $annee_etude, $duree_semestres);
            $action_text = "ajouté";
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Le cycle **{$nom_cycle}** a été {$action_text} avec succès.";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "❌ Erreur lors de l'exécution de l'opération : " . $stmt->error;
            $_SESSION['msg_type'] = "danger";
        }
        $stmt->close();
        
    } else {
        $_SESSION['message'] = "❌ Données d'entrée invalides ou manquantes pour le cycle.";
        $_SESSION['msg_type'] = "danger";
    }

    // Redirection pour éviter la soumission multiple et effacer les variables POST
    header("Location: crud_cycles.php");
    exit();
}

// Traitement de l'édition (Chargement des données dans le formulaire)
if (isset($_GET['edit'])) {
    $id_cycle = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    
    if ($id_cycle > 0) {
        $stmt = $conn->prepare("SELECT * FROM cycles WHERE id_cycle=?");
        $stmt->bind_param("i", $id_cycle);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $is_editing = true;
            $nom_cycle = htmlspecialchars($row['nom_cycle']);
            $annee_etude = htmlspecialchars($row['annee_etude']);
            $duree_semestres = htmlspecialchars($row['duree_semestres']);
        }
        $stmt->close();
    }
}

// Traitement de la suppression (DELETE)
if (isset($_GET['delete'])) {
    $id_cycle = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    
    if ($id_cycle > 0) {
        $stmt = $conn->prepare("DELETE FROM cycles WHERE id_cycle = ?");
        $stmt->bind_param("i", $id_cycle);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                 $_SESSION['message'] = "🗑️ Le cycle (ID: {$id_cycle}) a été supprimé avec succès.";
                 $_SESSION['msg_type'] = "warning";
            } else {
                 $_SESSION['message'] = "❓ Le cycle (ID: {$id_cycle}) n'existe pas.";
                 $_SESSION['msg_type'] = "info";
            }
        } else {
            // Gestion de l'erreur de contrainte de clé étrangère (FK)
            if ($conn->errno == 1451) {
                $_SESSION['message'] = "❌ Impossible de supprimer ce cycle. Il est lié à des classes ou des étudiants existants.";
                $_SESSION['msg_type'] = "danger";
            } else {
                $_SESSION['message'] = "❌ Erreur de suppression : " . $conn->error;
                $_SESSION['msg_type'] = "danger";
            }
        }
        $stmt->close();
    }
    
    header("Location: crud_cycles.php");
    exit();
}

// -------------------------------------------------------------------
// 2. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE
// -------------------------------------------------------------------

$result_cycles = $conn->query("SELECT * FROM cycles ORDER BY annee_etude ASC, nom_cycle ASC");
$conn->close();

include 'header_ecole.php'; 
?>

<h1 class="mb-4 text-primary"><i class="bi bi-calendar3 me-2"></i> Gestion des Cycles d'Étude</h1>

<?php display_session_message(); ?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> <?php echo $is_editing ? 'Modifier le Cycle' : 'Ajouter un Nouveau Cycle'; ?></h5>
            </div>
            <div class="card-body">
                <form action="crud_cycles.php" method="POST">
                    <input type="hidden" name="id_cycle" value="<?php echo $id_cycle; ?>">
                    
                    <div class="mb-3">
                        <label for="nom_cycle" class="form-label">Nom du Cycle (Ex: L1, M2)</label>
                        <input type="text" name="nom_cycle" id="nom_cycle" class="form-control" 
                               value="<?php echo $nom_cycle; ?>" required 
                               placeholder="Ex: Licence 1, Master 2">
                    </div>

                    <div class="mb-3">
                        <label for="annee_etude" class="form-label">Année d'Étude (Numéro)</label>
                        <input type="number" name="annee_etude" id="annee_etude" class="form-control" 
                               value="<?php echo $annee_etude; ?>" required min="1" max="10"
                               placeholder="Ex: 1 (pour L1)">
                    </div>

                    <div class="mb-4">
                        <label for="duree_semestres" class="form-label">Nombre de Semestres/Année</label>
                        <input type="number" name="duree_semestres" id="duree_semestres" class="form-control" 
                               value="<?php echo $duree_semestres; ?>" required min="1" max="4"
                               placeholder="Généralement 2">
                    </div>
                    
                    <button type="submit" name="save" class="btn btn-<?php echo $is_editing ? 'success' : 'primary'; ?> w-100">
                        <i class="bi bi-floppy-fill me-1"></i> <?php echo $is_editing ? 'Enregistrer les Modifications' : 'Ajouter le Cycle'; ?>
                    </button>
                    
                    <?php if ($is_editing): ?>
                        <a href="crud_cycles.php" class="btn btn-outline-secondary w-100 mt-2">Annuler l'Édition</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <h2 class="mb-3 text-secondary"><i class="bi bi-list-columns-reverse me-2"></i> Liste des Cycles Existants</h2>
        
        <?php if ($result_cycles->num_rows > 0): ?>
            <div class="table-responsive shadow-sm">
                <table class="table table-striped table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 5%;">ID</th>
                            <th style="width: 30%;">Cycle</th>
                            <th class="text-center" style="width: 15%;">Année</th>
                            <th class="text-center" style="width: 20%;">Semestres/An</th>
                            <th class="text-center" style="width: 30%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_cycles->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center fw-bold"><?php echo $row['id_cycle']; ?></td>
                            <td><?php echo htmlspecialchars($row['nom_cycle']); ?></td>
                            <td class="text-center"><?php echo $row['annee_etude']; ?></td>
                            <td class="text-center fw-bold"><?php echo $row['duree_semestres']; ?></td>
                            <td class="text-center">
                                <a href="crud_cycles.php?edit=<?php echo $row['id_cycle']; ?>" 
                                   class="btn btn-sm btn-info text-white me-1">
                                   <i class="bi bi-pencil"></i> Modifier
                                </a>
                                <a href="crud_cycles.php?delete=<?php echo $row['id_cycle']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cycle ? ATTENTION: Cela peut échouer si des classes y sont liées.');">
                                   <i class="bi bi-trash"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                Aucun cycle d'étude n'a été trouvé. Veuillez en ajouter un.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
