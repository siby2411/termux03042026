<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['secretaire', 'admin', 'medecin', 'sagefemme'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_service = $_SESSION['user_service'] ?? null;

// Récupérer les services pour les listes déroulantes
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();

// Récupérer les médecins par service
$medecins_par_service = [];
$medecins = $pdo->query("SELECT id, prenom, nom, service_id FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();
foreach ($medecins as $m) {
    $medecins_par_service[$m['service_id']][] = $m;
}

// Récupérer les catégories de documents
$categories = $pdo->query("SELECT * FROM document_categories ORDER BY nom")->fetchAll();

$message = '';
$message_type = '';

// TRAITEMENT UPLOAD DE DOCUMENT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // UPLOAD DE DOCUMENT
    if ($_POST['action'] == 'upload_document' && isset($_FILES['fichier'])) {
        $fichier = $_FILES['fichier'];
        $titre = $_POST['titre'];
        $description = $_POST['description'];
        $categorie_id = $_POST['categorie_id'];
        $destinataire_type = $_POST['destinataire_type'];
        $destinataire_service = $_POST['destinataire_service'] ?? null;
        $destinataire_medecin = $_POST['destinataire_medecin'] ?? null;
        $est_public = isset($_POST['est_public']) ? 1 : 0;
        $date_expiration = $_POST['date_expiration'] ?? null;
        
        // Validation
        $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
        $file_ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            $message = "Type de fichier non autorisé. Types acceptés: " . implode(', ', $allowed_types);
            $message_type = "danger";
        } elseif ($fichier['size'] > 20 * 1024 * 1024) { // 20MB max
            $message = "Fichier trop volumineux (max 20MB)";
            $message_type = "danger";
        } else {
            // Générer un nom unique
            $nom_unique = uniqid() . '_' . date('Ymd') . '.' . $file_ext;
            $chemin = 'uploads/documents/' . $nom_unique;
            
            if (move_uploaded_file($fichier['tmp_name'], '../../' . $chemin)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO documents (
                            titre, description, fichier_nom, fichier_chemin,
                            fichier_taille, fichier_type, categorie_id, expediteur_id,
                            destinataire_service_id, destinataire_medecin_id, est_public,
                            date_expiration, created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                        )
                    ");
                    
                    $stmt->execute([
                        $titre,
                        $description,
                        $fichier['name'],
                        $chemin,
                        $fichier['size'],
                        $file_ext,
                        $categorie_id ?: null,
                        $user_id,
                        $destinataire_type == 'service' ? $destinataire_service : null,
                        $destinataire_type == 'medecin' ? $destinataire_medecin : null,
                        $est_public,
                        $date_expiration
                    ]);
                    
                    $document_id = $pdo->lastInsertId();
                    
                    // Créer des notifications pour les destinataires
                    if ($destinataire_type == 'service' && $destinataire_service) {
                        // Notifier tous les médecins du service
                        $stmt_notif = $pdo->prepare("
                            INSERT INTO document_notifications (document_id, destinataire_id)
                            SELECT ?, id FROM users WHERE service_id = ? AND role IN ('medecin', 'sagefemme')
                        ");
                        $stmt_notif->execute([$document_id, $destinataire_service]);
                    } elseif ($destinataire_type == 'medecin' && $destinataire_medecin) {
                        $stmt_notif = $pdo->prepare("INSERT INTO document_notifications (document_id, destinataire_id) VALUES (?, ?)");
                        $stmt_notif->execute([$document_id, $destinataire_medecin]);
                    }
                    
                    $message = "Document uploadé avec succès !";
                    $message_type = "success";
                    
                } catch (Exception $e) {
                    $message = "Erreur: " . $e->getMessage();
                    $message_type = "danger";
                }
            } else {
                $message = "Erreur lors de l'upload";
                $message_type = "danger";
            }
        }
    }
    
    // MARQUER COMME LU
    if ($_POST['action'] == 'marquer_lu') {
        $notif_id = $_POST['notif_id'];
        $stmt = $pdo->prepare("UPDATE document_notifications SET lu = 1, date_lecture = NOW() WHERE id = ?");
        $stmt->execute([$notif_id]);
        exit();
    }
    
    // SUPPRIMER DOCUMENT
    if ($_POST['action'] == 'supprimer_document') {
        $doc_id = $_POST['doc_id'];
        // Vérifier les permissions (seulement l'expéditeur ou admin)
        $check = $pdo->prepare("SELECT fichier_chemin, expediteur_id FROM documents WHERE id = ?");
        $check->execute([$doc_id]);
        $doc = $check->fetch();
        
        if ($doc && ($doc['expediteur_id'] == $user_id || $user_role == 'admin')) {
            // Supprimer le fichier physique
            if (file_exists('../../' . $doc['fichier_chemin'])) {
                unlink('../../' . $doc['fichier_chemin']);
            }
            // Supprimer de la BDD
            $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([$doc_id]);
            $message = "Document supprimé";
            $message_type = "success";
        }
    }
}

// Récupérer les documents pour l'utilisateur connecté
if ($user_role == 'secretaire' || $user_role == 'admin') {
    // Les secrétaires et admin voient tous les documents
    $documents = $pdo->prepare("
        SELECT d.*, u.prenom as exp_prenom, u.nom as exp_nom,
               s.name as service_dest_nom,
               c.nom as categorie_nom
        FROM documents d
        JOIN users u ON d.expediteur_id = u.id
        LEFT JOIN services s ON d.destinataire_service_id = s.id
        LEFT JOIN document_categories c ON d.categorie_id = c.id
        WHERE d.statut = 'actif'
        ORDER BY d.created_at DESC
        LIMIT 50
    ");
    $documents->execute();
} else {
    // Les médecins et sages-femmes voient leurs documents
    $documents = $pdo->prepare("
        SELECT d.*, u.prenom as exp_prenom, u.nom as exp_nom,
               s.name as service_dest_nom,
               c.nom as categorie_nom,
               (SELECT COUNT(*) FROM document_notifications WHERE document_id = d.id AND destinataire_id = ? AND lu = 0) as non_lu
        FROM documents d
        JOIN users u ON d.expediteur_id = u.id
        LEFT JOIN services s ON d.destinataire_service_id = s.id
        LEFT JOIN document_categories c ON d.categorie_id = c.id
        WHERE (d.est_public = 1 
               OR d.destinataire_service_id = ? 
               OR d.destinataire_medecin_id = ?)
        AND d.statut = 'actif'
        ORDER BY d.created_at DESC
        LIMIT 50
    ");
    $documents->execute([$user_id, $user_service, $user_id]);
}

$documents_list = $documents->fetchAll();

// Récupérer les notifications non lues
$notifications = $pdo->prepare("
    SELECT n.*, d.titre, d.fichier_nom
    FROM document_notifications n
    JOIN documents d ON n.document_id = d.id
    WHERE n.destinataire_id = ? AND n.lu = 0
    ORDER BY n.date_envoi DESC
");
$notifications->execute([$user_id]);
$notifs_non_lues = $notifications->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Documentaire - Centre de Santé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px;
            color: white;
        }
        .navbar a { color: white; text-decoration: none; }
        .container-fluid { padding: 20px; }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            margin: -25px -25px 20px -25px;
            padding: 15px 25px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .upload-area {
            border: 2px dashed #1e3c72;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            background: #e9ecef;
            border-color: #2a5298;
        }
        
        .document-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .document-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .document-item.non-lu {
            border-left: 4px solid #ffc107;
            background: #fff3cd;
        }
        
        .badge-notif {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .notification-bell {
            position: relative;
            margin-right: 20px;
            cursor: pointer;
        }
        
        .notifications-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 300px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            display: none;
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
        }
        .notification-item:hover {
            background: #f8f9fa;
        }
        .notification-item.non-lu {
            background: #e3f2fd;
        }
        
        .notification-bell:hover .notifications-dropdown {
            display: block;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div>
                    <h3><i class="fas fa-file-alt"></i> Gestion Documentaire</h3>
                    <small><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?> (<?= ucfirst($user_role) ?>)</small>
                </div>
                <div class="d-flex align-items-center">
                    <!-- Notifications -->
                    <div class="notification-bell">
                        <i class="fas fa-bell fa-lg"></i>
                        <?php if (count($notifs_non_lues) > 0): ?>
                            <span class="badge-notif"><?= count($notifs_non_lues) ?></span>
                        <?php endif; ?>
                        
                        <div class="notifications-dropdown">
                            <div class="p-2 bg-light border-bottom">
                                <strong>Notifications</strong>
                            </div>
                            <?php if (empty($notifs_non_lues)): ?>
                                <div class="p-3 text-center text-muted">
                                    Aucune notification
                                </div>
                            <?php else: ?>
                                <?php foreach ($notifs_non_lues as $notif): ?>
                                    <div class="notification-item non-lu" onclick="marquerLu(<?= $notif['id'] ?>, this)">
                                        <strong><?= htmlspecialchars($notif['titre']) ?></strong>
                                        <br><small><?= htmlspecialchars($notif['fichier_nom']) ?></small>
                                        <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($notif['date_envoi'])) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <a href="javascript:history.back()" class="btn btn-sm btn-light me-2">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <a href="../../modules/auth/logout.php" class="btn btn-sm btn-light">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Colonne de gauche : Formulaire d'upload -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-upload"></i> Uploader un document
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_document">
                        
                        <div class="mb-3">
                            <label>Titre du document *</label>
                            <input type="text" name="titre" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label>Catégorie</label>
                            <select name="categorie_id" class="form-select">
                                <option value="">Sélectionner</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Destinataire</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="destinataire_type" value="public" id="destPublic" checked>
                                <label class="form-check-label" for="destPublic">
                                    Public (tous les utilisateurs)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="destinataire_type" value="service" id="destService">
                                <label class="form-check-label" for="destService">
                                    Service spécifique
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="destinataire_type" value="medecin" id="destMedecin">
                                <label class="form-check-label" for="destMedecin">
                                    Médecin spécifique
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="serviceSelector" style="display: none;">
                            <label>Sélectionner le service</label>
                            <select name="destinataire_service" class="form-select" onchange="loadMedecins(this.value)">
                                <option value="">Choisir...</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="medecinSelector" style="display: none;">
                            <label>Sélectionner le médecin</label>
                            <select name="destinataire_medecin" class="form-select" id="medecinSelect">
                                <option value="">Choisir d'abord un service</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Date d'expiration (optionnelle)</label>
                            <input type="date" name="date_expiration" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <div class="upload-area" onclick="document.getElementById('fichier').click()">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-2" style="color: #1e3c72;"></i>
                                <p>Cliquez ou glissez-déposez un fichier ici</p>
                                <p class="text-muted small">PDF, DOC, XLS, JPG, PNG (Max 20MB)</p>
                                <input type="file" name="fichier" id="fichier" style="display: none;" required>
                                <div id="file-name" class="mt-2"></div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-upload"></i> Uploader le document
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Colonne de droite : Liste des documents -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Documents récents
                    </div>
                    
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchDoc" placeholder="Rechercher un document...">
                    </div>
                    
                    <div style="max-height: 600px; overflow-y: auto;">
                        <?php if (empty($documents_list)): ?>
                            <div class="alert alert-info text-center">
                                Aucun document disponible
                            </div>
                        <?php else: ?>
                            <?php foreach ($documents_list as $doc): ?>
                                <div class="document-item <?= ($doc['non_lu'] ?? 0) > 0 ? 'non-lu' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div style="flex: 1;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-<?= $doc['fichier_type'] == 'pdf' ? 'pdf' : 'alt' ?> fa-2x me-3" style="color: #1e3c72;"></i>
                                                <div>
                                                    <strong><?= htmlspecialchars($doc['titre']) ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> <?= htmlspecialchars($doc['exp_prenom'] . ' ' . $doc['exp_nom']) ?> |
                                                        <i class="fas fa-folder"></i> <?= htmlspecialchars($doc['categorie_nom'] ?? 'Non catégorisé') ?> |
                                                        <i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php if ($doc['description']): ?>
                                                <p class="mt-2 mb-1 small"><?= nl2br(htmlspecialchars($doc['description'])) ?></p>
                                            <?php endif; ?>
                                            <?php if ($doc['destinataire_service_id'] || $doc['destinataire_medecin_id']): ?>
                                                <small class="text-primary">
                                                    <i class="fas fa-share-alt"></i> Partagé avec : 
                                                    <?= $doc['service_dest_nom'] ?? 'Médecin spécifique' ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="btn-group">
                                            <a href="../../<?= $doc['fichier_chemin'] ?>" class="btn btn-sm btn-primary" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <?php if ($doc['expediteur_id'] == $user_id || $user_role == 'admin'): ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce document ?')">
                                                    <input type="hidden" name="action" value="supprimer_document">
                                                    <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestionnaire pour l'affichage du nom du fichier sélectionné
        document.getElementById('fichier').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            document.getElementById('file-name').innerHTML = fileName ? 
                '<span class="badge bg-success">Fichier sélectionné: ' + fileName + '</span>' : '';
        });

        // Gestionnaires pour les options de destinataire
        document.querySelectorAll('input[name="destinataire_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('serviceSelector').style.display = 
                    this.value === 'service' ? 'block' : 'none';
                document.getElementById('medecinSelector').style.display = 
                    this.value === 'medecin' ? 'block' : 'none';
            });
        });

        // Charger les médecins d'un service
        function loadMedecins(serviceId) {
            const medecinSelect = document.getElementById('medecinSelect');
            medecinSelect.innerHTML = '<option value="">Chargement...</option>';
            
            // Récupérer les médecins du service (données statiques)
            const medecins = <?= json_encode($medecins_par_service) ?>;
            
            if (medecins[serviceId]) {
                let options = '<option value="">Sélectionner un médecin</option>';
                medecins[serviceId].forEach(m => {
                    options += `<option value="${m.id}">Dr. ${m.prenom} ${m.nom}</option>`;
                });
                medecinSelect.innerHTML = options;
            } else {
                medecinSelect.innerHTML = '<option value="">Aucun médecin dans ce service</option>';
            }
        }

        // Recherche de documents
        document.getElementById('searchDoc').addEventListener('keyup', function() {
            const search = this.value.toLowerCase();
            document.querySelectorAll('.document-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(search) ? 'block' : 'none';
            });
        });

        // Marquer une notification comme lue
        function marquerLu(notifId, element) {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=marquer_lu&notif_id=' + notifId
            }).then(() => {
                element.classList.remove('non-lu');
                element.innerHTML += ' <span class="badge bg-success">Lu</span>';
                
                // Mettre à jour le compteur
                const badge = document.querySelector('.badge-notif');
                if (badge) {
                    const count = parseInt(badge.textContent) - 1;
                    if (count <= 0) {
                        badge.remove();
                    } else {
                        badge.textContent = count;
                    }
                }
            });
        }
    </script>
</body>
</html>
