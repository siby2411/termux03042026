<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirection si non connecté ou rôle non géré
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

include 'header_ecole.php';
?>

<div class="container mt-5">
    <h1>Tableau de Bord Administrateur</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> mt-3">
            <?php echo $_SESSION['message']; ?>
        </div>
        <?php 
            unset($_SESSION['message']);
            unset($_SESSION['msg_type']);
        endif; 
    ?>

    <div class="row mt-4">
        
        <!-- GESTION ÉTUDIANTS / INSCRIPTION -->
        <div class="col-md-4 mb-3">
            <div class="card shadow h-100 border-success border-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-fill fs-4 me-2"></i> Gestion des Étudiants</h5>
                    <p class="card-text">Inscription, modification des données personnelles et affectation de classe.</p>
                    <a href="crud_etudiants.php" class="btn btn-success">Accéder aux Étudiants</a>
                </div>
            </div>
        </div>

        <!-- GESTION PAIEMENTS / SCOLARITÉ -->
        <div class="col-md-4 mb-3">
            <div class="card shadow h-100 border-warning border-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-cash-stack fs-4 me-2"></i> Paiements & Scolarité</h5>
                    <p class="card-text">Enregistrer les paiements des droits d'inscription et de la scolarité. Consulter les soldes.</p>
                    <a href="crud_paiements.php" class="btn btn-warning">Gérer la Scolarité</a>
                </div>
            </div>
        </div>

        <!-- SAISIE DE NOTES -->
        <div class="col-md-4 mb-3">
            <div class="card shadow h-100 border-info border-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-pencil-square fs-4 me-2"></i> Saisie des Notes</h5>
                    <p class="card-text">Entrer et mettre à jour les notes par unité de valeur (UV) pour les étudiants.</p>
                    <a href="notes_edit.php" class="btn btn-info">Saisir les Notes</a>
                </div>
            </div>
        </div>

        <!-- CONFIGURATION ACADÉMIQUE -->
        <div class="col-md-12 mt-4">
            <h3>Configuration et Organisation Académique</h3>
            <hr>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-diagram-3-fill me-1"></i> Cycles & Filières</h6>
                            <p class="card-text"><small>Définir les niveaux (L1, M2) et les spécialités.</small></p>
                            <a href="crud_cycles.php" class="btn btn-sm btn-outline-primary me-2">Cycles</a>
                            <a href="crud_filieres.php" class="btn btn-sm btn-outline-primary">Filières</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-house-door-fill me-1"></i> Gestion des Classes</h6>
                            <p class="card-text"><small>Créer, modifier et lister les classes.</small></p>
                            <a href="crud_classes.php" class="btn btn-sm btn-outline-secondary">Accéder aux Classes</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-book-fill me-1"></i> Gestion des Matières</h6>
                            <p class="card-text"><small>Définir les matières, UV, coefficients et crédits.</small></p>
                            <a href="crud_matieres.php" class="btn btn-sm btn-outline-secondary">Accéder aux Matières</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-link-45deg me-1"></i> Affectations</h6>
                            <p class="card-text"><small>Lier Matières/Classes et assigner les professeurs.</small></p>
                            <a href="crud_classe_matiere.php" class="btn btn-sm btn-outline-secondary">Gérer Affectations</a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- AUTRES GESTIONS -->
        <div class="col-md-12 mt-4">
            <h3>Gestion du Personnel et Rapports</h3>
            <hr>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-person-badge-fill me-1"></i> Gestion des Professeurs</h6>
                            <p class="card-text"><small>Gérer le personnel enseignant et leurs accès.</small></p>
                            <a href="crud_professeurs.php" class="btn btn-sm btn-outline-dark">Gérer Professeurs</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-file-earmark-bar-graph-fill me-1"></i> Consultation Bulletins</h6>
                            <p class="card-text"><small>Consulter les résultats finaux et les moyennes.</small></p>
                            <a href="bulletin_view.php" class="btn btn-sm btn-outline-dark">Voir Bulletins</a>
                        </div>
                    </div>
                </div>

                 <div class="col-md-4 mb-3">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-calculator-fill me-1"></i> Calcul des Moyennes</h6>
                            <p class="card-text"><small>Outil pour déclencher les calculs de moyennes et de statuts (utile après la saisie).</small></p>
                            <a href="calcul_moyenne.php" class="btn btn-sm btn-outline-dark">Lancer Calcul</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
