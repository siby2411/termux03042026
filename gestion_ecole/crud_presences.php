<?php
// Fichier : crud_presences.php - Gestion des Présences/Absences des Étudiants
// RÔLE D'ACCÈS : ADMINISTRATEUR et PROFESSEUR

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php'; 

// Vérification de la session
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Professeur')) {
    $_SESSION['message'] = "Accès non autorisé. Vous devez être Administrateur ou Professeur.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php"); 
    exit();
}

$conn = db_connect_ecole();
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$current_user_id = $_SESSION['user_id'] ?? 0;
$current_role = $_SESSION['role'] ?? 'Invité';

// Variables de filtre
$id_classe_filter = $_POST['id_classe_filter'] ?? $_GET['id_classe_filter'] ?? '';
$id_matiere_filter = $_POST['id_matiere_filter'] ?? $_GET['id_matiere_filter'] ?? '';
$date_session_filter = $_POST['date_session_filter'] ?? $_GET['date_session_filter'] ?? date('Y-m-d');
$heure_debut_filter = $_POST['heure_debut_filter'] ?? $_GET['heure_debut_filter'] ?? '08:00:00';

// --- Récupération des données pour les listes déroulantes ---

// Récupérer les classes que le professeur enseigne (si professeur) ou toutes (si admin)
$where_prof_classes = "";
if ($current_role === 'Professeur') {
    // Jointure complexe pour trouver les classes où ce professeur enseigne une matière
    $where_prof_classes = "JOIN classe_matiere cm ON c.id_classe = cm.id_classe AND cm.id_professeur = $current_user_id";
}

$classes_query = "SELECT DISTINCT c.id_classe, c.nom_classe FROM classes c $where_prof_classes ORDER BY c.nom_classe";
$classes_result = $conn->query($classes_query);

// Récupérer les matières que le professeur enseigne (si professeur) ou toutes (si admin)
$where_prof_matieres = "";
if ($current_role === 'Professeur') {
    $where_prof_matieres = "WHERE cm.id_professeur = $current_user_id";
}
$matieres_query = "
    SELECT DISTINCT m.id_matiere, m.nom_matiere 
    FROM matieres m 
    LEFT JOIN classe_matiere cm ON m.id_matiere = cm.id_matiere
    $where_prof_matieres
    ORDER BY m.nom_matiere
";
$matieres_result = $conn->query($matieres_query);


// --- Fonction d'enregistrement/mise à jour en lot ---
if (isset($_POST['save_presence'])) {
    $id_classe = $_POST['id_classe_filter'];
    $id_matiere = $_POST['id_matiere_filter'];
    $date_session = $_POST['date_session_filter'];
    $heure_debut = $_POST['heure_debut_filter'];
    $prof_id_log = $current_role === 'Professeur' ? $current_user_id : $_POST['id_prof_log'] ; // Professeur logueur ou celui choisi

    $etudiants_statut = $_POST['statut'] ?? [];
    $etudiants_justifie = $_POST['justifie'] ?? [];
    $etudiants_commentaire = $_POST['commentaire'] ?? [];

    $success_count = 0;
    $error_count = 0;

    foreach ($etudiants_statut as $id_etudiant => $statut) {
        $justifie = isset($etudiants_justifie[$id_etudiant]) ? 1 : 0;
        $commentaire = $etudiants_commentaire[$id_etudiant] ?? '';
        
        // 1. Essayer de mettre à jour si l'enregistrement existe
        $stmt_update = $conn->prepare("
            UPDATE presences 
            SET statut=?, justifie=?, commentaire=?, id_professeur=? 
            WHERE id_etudiant=? AND id_matiere=? AND date_session=? AND heure_debut_prevue=?
        ");
        $stmt_update->bind_param("sisissi", $statut, $justifie, $commentaire, $prof_id_log, $id_etudiant, $id_matiere, $date_session, $heure_debut);
        $stmt_update->execute();
        
        if ($conn->affected_rows > 0) {
            $success_count++;
        } else {
            // 2. Sinon, insérer un nouvel enregistrement
            $stmt_insert = $conn->prepare("
                INSERT INTO presences (id_etudiant, id_matiere, date_session, heure_debut_prevue, statut, justifie, commentaire, id_professeur) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_insert->bind_param("iissisii", $id_etudiant, $id_matiere, $date_session, $heure_debut, $statut, $justifie, $commentaire, $prof_id_log);
            if ($stmt_insert->execute()) {
                $success_count++;
            } else {
                // Gestion de l'erreur (ex: conflit de clé unique, mais normalement géré par l'UPDATE)
                $error_count++;
                // echo "Erreur pour étudiant $id_etudiant: " . $conn->error;
            }
            $stmt_insert->close();
        }
        $stmt_update->close();
    }

    if ($success_count > 0) {
        $_SESSION['message'] = "Présence mise à jour pour $success_count étudiant(s) avec succès!";
        $_SESSION['msg_type'] = "success";
    } elseif ($error_count > 0) {
        $_SESSION['message'] = "Erreur lors de la mise à jour de la présence pour certains étudiants.";
        $_SESSION['msg_type'] = "danger";
    } else {
         $_SESSION['message'] = "Aucun changement détecté ou aucun étudiant à enregistrer.";
         $_SESSION['msg_type'] = "info";
    }
    
    // Redirection vers la même page avec les filtres pour rester sur la vue
    $redirect_url = "crud_presences.php?id_classe_filter=$id_classe&id_matiere_filter=$id_matiere&date_session_filter=$date_session&heure_debut_filter=$heure_debut";
    header("Location: $redirect_url");
    exit();
}

include 'header_ecole.php'; 
?>

<h1 class="mb-4"><i class="bi bi-check2-square me-2"></i> Gestion des Présences et Absences</h1>

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo $_SESSION['msg_type']; ?> mt-3">
    <?php echo $_SESSION['message']; ?>
</div>
<?php 
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
endif; 
?>

<!-- Formulaire de Sélection de Session -->
<div class="card shadow mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Choisir la Session de Cours</h5>
    </div>
    <div class="card-body">
        <form action="crud_presences.php" method="POST" class="row g-3 align-items-end">
            
            <div class="col-md-3">
                <label for="id_classe_filter" class="form-label">Classe *</label>
                <select name="id_classe_filter" id="id_classe_filter" class="form-select" required>
                    <option value="">Sélectionner la Classe</option>
                    <?php 
                        $classes_result->data_seek(0);
                        while ($row = $classes_result->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $row['id_classe']; ?>" <?php echo $id_classe_filter == $row['id_classe'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['nom_classe']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="id_matiere_filter" class="form-label">Matière *</label>
                <select name="id_matiere_filter" id="id_matiere_filter" class="form-select" required>
                    <option value="">Sélectionner la Matière</option>
                    <?php 
                        $matieres_result->data_seek(0);
                        while ($row = $matieres_result->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $row['id_matiere']; ?>" <?php echo $id_matiere_filter == $row['id_matiere'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['nom_matiere']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="date_session_filter" class="form-label">Date *</label>
                <input type="date" name="date_session_filter" id="date_session_filter" class="form-control" value="<?php echo htmlspecialchars($date_session_filter); ?>" required>
            </div>
            
            <div class="col-md-2">
                <label for="heure_debut_filter" class="form-label">Heure Début (Prévue) *</label>
                <input type="time" name="heure_debut_filter" id="heure_debut_filter" class="form-control" value="<?php echo htmlspecialchars($heure_debut_filter); ?>" required>
            </div>

            <div class="col-md-1">
                <button type="submit" name="load_session" class="btn btn-info w-100">Charger</button>
            </div>
        </form>
    </div>
</div>


<!-- Tableau de prise de Présence -->
<?php if ($id_classe_filter && $id_matiere_filter): ?>
    
    <?php
    // Récupérer les étudiants de la classe
    $etudiants_query = $conn->query("
        SELECT id_etudiant, nom, prenom, matricule 
        FROM etudiants 
        WHERE id_classe = '$id_classe_filter' 
        ORDER BY nom, prenom
    ");

    // Récupérer tous les enregistrements de présence existants pour cette session
    $presences_existantes = [];
    $stmt_presence = $conn->prepare("
        SELECT id_etudiant, statut, justifie, commentaire 
        FROM presences 
        WHERE id_matiere=? AND date_session=? AND heure_debut_prevue=?
    ");
    $stmt_presence->bind_param("iss", $id_matiere_filter, $date_session_filter, $heure_debut_filter);
    $stmt_presence->execute();
    $result_presence = $stmt_presence->get_result();
    while ($row = $result_presence->fetch_assoc()) {
        $presences_existantes[$row['id_etudiant']] = $row;
    }
    $stmt_presence->close();
    
    // Récupérer la liste des professeurs si l'utilisateur est Admin
    $professeurs_all = [];
    if ($current_role === 'Administrateur') {
        $profs_res = $conn->query("SELECT id_professeur, nom, prenom FROM professeurs ORDER BY nom");
        while($p_row = $profs_res->fetch_assoc()) {
            $professeurs_all[] = $p_row;
        }
    }
    
    // Infos Matière/Classe pour l'affichage
    $matiere_nom = $conn->query("SELECT nom_matiere FROM matieres WHERE id_matiere = '$id_matiere_filter'")->fetch_assoc()['nom_matiere'] ?? 'Inconnue';
    $classe_nom = $conn->query("SELECT nom_classe FROM classes WHERE id_classe = '$id_classe_filter'")->fetch_assoc()['nom_classe'] ?? 'Inconnue';
    ?>

    <div class="alert alert-primary mt-3">
        Saisie de présence pour : **<?php echo htmlspecialchars($matiere_nom); ?>** (Classe : **<?php echo htmlspecialchars($classe_nom); ?>**)
        <br>
        Date: **<?php echo date('d/m/Y', strtotime($date_session_filter)); ?>** à **<?php echo substr($heure_debut_filter, 0, 5); ?>**
    </div>

    <div class="card shadow p-3">
        <form action="crud_presences.php" method="POST">
            <!-- Champs cachés pour le retour du formulaire -->
            <input type="hidden" name="id_classe_filter" value="<?php echo htmlspecialchars($id_classe_filter); ?>">
            <input type="hidden" name="id_matiere_filter" value="<?php echo htmlspecialchars($id_matiere_filter); ?>">
            <input type="hidden" name="date_session_filter" value="<?php echo htmlspecialchars($date_session_filter); ?>">
            <input type="hidden" name="heure_debut_filter" value="<?php echo htmlspecialchars($heure_debut_filter); ?>">
            
            <?php if ($current_role === 'Administrateur'): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="id_prof_log" class="form-label">Professeur responsable de la session (pour le log):</label>
                        <select name="id_prof_log" id="id_prof_log" class="form-select" required>
                            <option value="">Sélectionner Professeur</option>
                            <?php foreach($professeurs_all as $prof): ?>
                                <option value="<?php echo $prof['id_professeur']; ?>">
                                    <?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['prenom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php else: ?>
                <!-- Si Professeur, utilise son ID comme ID du logueur -->
                <input type="hidden" name="id_prof_log" value="<?php echo $current_user_id; ?>">
            <?php endif; ?>


            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">N°</th>
                            <th>Matricule</th>
                            <th>Nom & Prénom</th>
                            <th class="text-center" style="width: 20%;">Statut</th>
                            <th class="text-center" style="width: 10%;">Justifié</th>
                            <th>Commentaire (Raison absence/retard)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        if ($etudiants_query->num_rows > 0):
                            while ($etudiant = $etudiants_query->fetch_assoc()):
                                $id_etudiant = $etudiant['id_etudiant'];
                                $existing = $presences_existantes[$id_etudiant] ?? ['statut' => 'P', 'justifie' => 0, 'commentaire' => ''];
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($etudiant['matricule']); ?></td>
                            <td>**<?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?>**</td>
                            
                            <!-- Statut (P/A/R) -->
                            <td>
                                <select name="statut[<?php echo $id_etudiant; ?>]" class="form-select form-select-sm">
                                    <option value="P" <?php echo $existing['statut'] == 'P' ? 'selected' : ''; ?>>Présent (P)</option>
                                    <option value="A" <?php echo $existing['statut'] == 'A' ? 'selected' : ''; ?>>Absent (A)</option>
                                    <option value="R" <?php echo $existing['statut'] == 'R' ? 'selected' : ''; ?>>Retard (R)</option>
                                </select>
                            </td>
                            
                            <!-- Justifié -->
                            <td class="text-center">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="justifie[<?php echo $id_etudiant; ?>]" value="1" id="justifie_<?php echo $id_etudiant; ?>" <?php echo $existing['justifie'] ? 'checked' : ''; ?>>
                                </div>
                            </td>
                            
                            <!-- Commentaire -->
                            <td>
                                <input type="text" name="commentaire[<?php echo $id_etudiant; ?>]" class="form-control form-control-sm" placeholder="Raison de l'absence..." value="<?php echo htmlspecialchars($existing['commentaire']); ?>">
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                <i class="bi bi-info-circle me-1"></i> Aucun étudiant trouvé dans cette classe.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($etudiants_query->num_rows > 0): ?>
                <button type="submit" name="save_presence" class="btn btn-danger mt-3 w-100">
                    <i class="bi bi-save me-2"></i> Enregistrer la Présence / Mettre à Jour
                </button>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<h2 id="rapports" class="mt-5 mb-4">Rapports d'Absences (Quantification)</h2>
<div class="card shadow p-4 mb-5">
    
    <p class="text-muted">
        Cette section permet de visualiser les absences cumulées par étudiant depuis le début de l'année.
    </p>

    <?php
    // --- Requête de Rapport d'Absences ---
    $rapport_absences_query = $conn->query("
        SELECT 
            e.matricule, e.nom, e.prenom, c.nom_classe,
            SUM(CASE WHEN p.statut = 'A' THEN 1 ELSE 0 END) AS total_absences,
            SUM(CASE WHEN p.statut = 'A' AND p.justifie = 1 THEN 1 ELSE 0 END) AS absences_justifiees,
            SUM(CASE WHEN p.statut = 'R' THEN 1 ELSE 0 END) AS total_retards,
            COUNT(p.id_presence) AS total_sessions_enregistrees
        FROM 
            etudiants e
        JOIN classes c ON e.id_classe = c.id_classe
        LEFT JOIN presences p ON e.id_etudiant = p.id_etudiant
        GROUP BY e.id_etudiant
        ORDER BY total_absences DESC, e.nom
    ");
    ?>
    
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Matricule</th>
                    <th>Nom & Prénom</th>
                    <th>Classe</th>
                    <th class="text-center">Sessions Enregistrées</th>
                    <th class="text-center bg-danger text-white">Absences Totales</th>
                    <th class="text-center bg-warning text-dark">Absences Justifiées</th>
                    <th class="text-center bg-info text-white">Retards Totaux</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rapport_absences_query->num_rows > 0): ?>
                    <?php while ($rapport = $rapport_absences_query->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rapport['matricule']); ?></td>
                        <td><?php echo htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($rapport['nom_classe']); ?></td>
                        <td class="text-center"><?php echo $rapport['total_sessions_enregistrees']; ?></td>
                        <td class="text-center fw-bold text-danger"><?php echo $rapport['total_absences']; ?></td>
                        <td class="text-center"><?php echo $rapport['absences_justifiees']; ?></td>
                        <td class="text-center"><?php echo $rapport['total_retards']; ?></td>
                        <td class="text-center">
                            <!-- Ici, on pourrait ajouter un lien vers un rapport détaillé par étudiant -->
                            <a href="#" class="btn btn-sm btn-outline-secondary disabled">Détails</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">Aucun étudiant ou donnée de présence enregistrée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php include 'footer_ecole.php'; ?>
