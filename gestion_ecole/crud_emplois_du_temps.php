<?php
// Fichier : crud_emplois_du_temps.php - Gestion des Emplois du Temps (EDT)
// RÔLE D'ACCÈS : ADMINISTRATEUR UNIQUEMENT

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php'; 

// Vérification de la session Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrateur') {
    $_SESSION['message'] = "Accès non autorisé. Seul un administrateur peut gérer l'emploi du temps.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php"); 
    exit();
}

$conn = db_connect_ecole();
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Variables pour le formulaire
$id_edt = 0;
$id_classe = '';
$jour_semaine = '';
$heure_debut = '08:00';
$heure_fin = '10:00';
$id_matiere = '';
$id_professeur = '';
$salle = '';
$update = false;

$annee_academique_en_cours = date('Y') . '-' . (date('Y') + 1); 

// --- Fonction de suppression ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM emplois_du_temps WHERE id_edt = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Créneau horaire supprimé avec succès!";
        $_SESSION['msg_type'] = "warning";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: crud_emplois_du_temps.php");
    exit();
}

// --- Fonction d'édition (Pré-remplissage du formulaire) ---
if (isset($_GET['edit'])) {
    $id_edt = $_GET['edit'];
    $update = true;
    $result = $conn->query("SELECT * FROM emplois_du_temps WHERE id_edt=$id_edt");
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $id_classe = $row['id_classe'];
        $jour_semaine = $row['jour_semaine'];
        $heure_debut = $row['heure_debut'];
        $heure_fin = $row['heure_fin'];
        $id_matiere = $row['id_matiere'];
        $id_professeur = $row['id_professeur'];
        $salle = $row['salle'];
    }
}

// --- Fonction d'enregistrement/mise à jour ---
if (isset($_POST['save']) || isset($_POST['update'])) {
    $id_classe = $_POST['id_classe'];
    $jour_semaine = $_POST['jour_semaine'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $id_matiere = $_POST['id_matiere'];
    $id_professeur = $_POST['id_professeur'];
    $salle = $_POST['salle'];
    $annee = $_POST['annee_academique'];

    if (isset($_POST['save'])) {
        // Enregistrement
        $stmt = $conn->prepare("INSERT INTO emplois_du_temps (id_classe, jour_semaine, heure_debut, heure_fin, id_matiere, id_professeur, salle, annee_academique) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiiss", $id_classe, $jour_semaine, $heure_debut, $heure_fin, $id_matiere, $id_professeur, $salle, $annee);
        $action_message = "Créneau ajouté";
    } else {
        // Mise à jour
        $id_edt = $_POST['id_edt'];
        $stmt = $conn->prepare("UPDATE emplois_du_temps SET id_classe=?, jour_semaine=?, heure_debut=?, heure_fin=?, id_matiere=?, id_professeur=?, salle=?, annee_academique=? WHERE id_edt=?");
        $stmt->bind_param("isssiissi", $id_classe, $jour_semaine, $heure_debut, $heure_fin, $id_matiere, $id_professeur, $salle, $annee, $id_edt);
        $action_message = "Créneau mis à jour";
    }

    if ($stmt->execute()) {
         $_SESSION['message'] = $action_message . " avec succès!";
         $_SESSION['msg_type'] = "success";
    } else {
        $error = $conn->error;
        if (strpos($error, 'Duplicate entry') !== false) {
             $_SESSION['message'] = "Erreur: Un cours existe déjà pour cette classe à cette heure et ce jour précis.";
        } else {
             $_SESSION['message'] = "Erreur lors de l'opération: " . $error;
        }
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: crud_emplois_du_temps.php?id_classe_view=" . $id_classe); // Revenir à la vue de la classe éditée
    exit();
}

// --- Récupération des données pour les listes déroulantes ---
$classes_result = $conn->query("SELECT id_classe, nom_classe FROM classes ORDER BY nom_classe");
$matieres_result = $conn->query("SELECT id_matiere, nom_matiere FROM matieres ORDER BY nom_matiere");
$professeurs_result = $conn->query("SELECT id_professeur, nom, prenom FROM professeurs ORDER BY nom");

// --- Jours de la semaine ---
$jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

include 'header_ecole.php'; 
?>

<h1 class="mb-4"><i class="bi bi-calendar-week me-2"></i> Gestion des Emplois du Temps (EDT)</h1>

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
    <!-- Formulaire d'ajout/modification de créneau -->
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header bg-<?php echo $update ? 'warning' : 'primary'; ?> text-white">
                <h5 class="mb-0"><?php echo $update ? 'Modifier un Créneau' : 'Ajouter un Nouveau Créneau'; ?></h5>
            </div>
            <div class="card-body">
                <form action="crud_emplois_du_temps.php" method="POST">
                    <input type="hidden" name="id_edt" value="<?php echo $id_edt; ?>">
                    <input type="hidden" name="annee_academique" value="<?php echo $annee_academique_en_cours; ?>">
                    
                    <div class="form-group mb-3">
                        <label for="id_classe">Classe *</label>
                        <select name="id_classe" id="id_classe" class="form-control" required>
                            <option value="">Sélectionner la Classe</option>
                            <?php 
                                $classes_result->data_seek(0); // Reset pointer
                                while ($row = $classes_result->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $row['id_classe']; ?>" <?php echo $id_classe == $row['id_classe'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nom_classe']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="jour_semaine">Jour de la Semaine *</label>
                        <select name="jour_semaine" id="jour_semaine" class="form-control" required>
                            <option value="">Sélectionner le Jour</option>
                            <?php foreach ($jours as $jour): ?>
                            <option value="<?php echo $jour; ?>" <?php echo $jour_semaine == $jour ? 'selected' : ''; ?>>
                                <?php echo $jour; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="heure_debut">Heure de Début *</label>
                            <input type="time" name="heure_debut" id="heure_debut" class="form-control" value="<?php echo htmlspecialchars($heure_debut); ?>" required>
                        </div>
                        <div class="col-6">
                            <label for="heure_fin">Heure de Fin *</label>
                            <input type="time" name="heure_fin" id="heure_fin" class="form-control" value="<?php echo htmlspecialchars($heure_fin); ?>" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="id_matiere">Matière *</label>
                        <select name="id_matiere" id="id_matiere" class="form-control" required>
                            <option value="">Sélectionner la Matière</option>
                            <?php 
                                $matieres_result->data_seek(0);
                                while ($row = $matieres_result->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $row['id_matiere']; ?>" <?php echo $id_matiere == $row['id_matiere'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nom_matiere']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="id_professeur">Professeur *</label>
                        <select name="id_professeur" id="id_professeur" class="form-control" required>
                            <option value="">Sélectionner le Professeur</option>
                            <?php 
                                $professeurs_result->data_seek(0);
                                while ($row = $professeurs_result->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $row['id_professeur']; ?>" <?php echo $id_professeur == $row['id_professeur'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="salle">Salle (Optionnel)</label>
                        <input type="text" name="salle" id="salle" class="form-control" value="<?php echo htmlspecialchars($salle); ?>" placeholder="Ex: A101 ou Labo Info">
                    </div>

                    <?php if ($update): ?>
                        <button type="submit" name="update" class="btn btn-warning btn-block">Mettre à Jour le Créneau</button>
                        <a href="crud_emplois_du_temps.php" class="btn btn-secondary btn-block mt-2">Annuler</a>
                    <?php else: ?>
                        <button type="submit" name="save" class="btn btn-primary btn-block">Enregistrer le Créneau</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Affichage de l'Emploi du Temps -->
    <div class="col-md-8">
        <h2>Consultation de l'Emploi du Temps</h2>
        
        <!-- Sélection de la Classe à Afficher -->
        <form action="crud_emplois_du_temps.php" method="GET" class="mb-4 p-3 border rounded bg-white">
            <div class="input-group">
                <select name="id_classe_view" id="id_classe_view" class="form-select" required>
                    <option value="">-- Choisir la Classe à Afficher --</option>
                    <?php 
                        $classes_result->data_seek(0);
                        while ($row = $classes_result->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $row['id_classe']; ?>" <?php echo (isset($_GET['id_classe_view']) && $_GET['id_classe_view'] == $row['id_classe']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['nom_classe']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-info">Afficher l'EDT</button>
            </div>
        </form>

        <?php if (isset($_GET['id_classe_view'])): ?>
            <?php
            $id_classe_view = $_GET['id_classe_view'];
            
            // 1. Récupérer le nom de la classe
            $classe_info = $conn->query("SELECT nom_classe FROM classes WHERE id_classe = $id_classe_view")->fetch_assoc();
            $nom_classe_view = $classe_info ? $classe_info['nom_classe'] : 'Classe Inconnue';
            
            // 2. Récupérer tous les créneaux pour cette classe et l'année en cours
            $query_edt = $conn->query("
                SELECT 
                    e.*, m.nom_matiere, p.nom, p.prenom
                FROM 
                    emplois_du_temps e
                JOIN matieres m ON e.id_matiere = m.id_matiere
                JOIN professeurs p ON e.id_professeur = p.id_professeur
                WHERE e.id_classe = $id_classe_view AND e.annee_academique = '$annee_academique_en_cours'
                ORDER BY e.heure_debut, FIELD(e.jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi')
            ");

            $edt_data = [];
            $heures_uniques = [];

            while ($row = $query_edt->fetch_assoc()) {
                // Créer une clé combinée HeureDebut-HeureFin
                $heure_key = substr($row['heure_debut'], 0, 5) . ' - ' . substr($row['heure_fin'], 0, 5); 
                $heures_uniques[$row['heure_debut']] = $heure_key; // Utiliser heure_debut pour le tri

                // Organiser les données par Jour et Heure
                $edt_data[$row['jour_semaine']][$heure_key] = $row;
            }

            // Trier les heures uniques (pour l'axe des Y)
            ksort($heures_uniques);
            $heures_affichage = array_values($heures_uniques);

            $jours_affichage = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            ?>

            <div class="alert alert-success mt-3">
                Emploi du Temps pour la classe **<?php echo htmlspecialchars($nom_classe_view); ?>** (Année : <?php echo $annee_academique_en_cours; ?>)
            </div>

            <div class="table-responsive shadow-sm border rounded">
                <table class="table table-bordered table-striped table-hover align-middle text-center">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th style="width: 120px;">Heures</th>
                            <?php foreach ($jours_affichage as $jour): ?>
                                <th><?php echo $jour; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($heures_affichage)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Aucun cours enregistré pour cette classe durant cette année académique.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($heures_affichage as $plage_horaire): ?>
                                <tr>
                                    <th class="table-primary text-nowrap"><?php echo $plage_horaire; ?></th>
                                    <?php foreach ($jours_affichage as $jour): ?>
                                        <td style="min-width: 150px;">
                                            <?php 
                                            if (isset($edt_data[$jour][$plage_horaire])): 
                                                $creneau = $edt_data[$jour][$plage_horaire];
                                            ?>
                                                <div class="p-1 bg-light border rounded">
                                                    <strong class="text-primary"><?php echo htmlspecialchars($creneau['nom_matiere']); ?></strong><br>
                                                    <small>
                                                        <?php echo htmlspecialchars(substr($creneau['prenom'], 0, 1) . '. ' . $creneau['nom']); ?><br>
                                                        Salle: **<?php echo htmlspecialchars($creneau['salle']); ?>**
                                                    </small>
                                                    <div class="mt-1">
                                                        <a href="crud_emplois_du_temps.php?edit=<?php echo $creneau['id_edt']; ?>" class="btn btn-sm btn-outline-warning py-0 px-1 me-1" title="Modifier">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="crud_emplois_du_temps.php?delete=<?php echo $creneau['id_edt']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce créneau ?')" class="btn btn-sm btn-outline-danger py-0 px-1" title="Supprimer">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; // Fin de l'affichage de l'EDT ?>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
