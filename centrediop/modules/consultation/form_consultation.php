<?php require_once '../../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie Consultation - Centre Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="card shadow-sm mx-auto" style="max-width: 800px;">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Nouvelle Consultation</h4>
        </div>
        <div class="card-body">
            <form action="save_consultation.php" method="POST">
                <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($_GET['patient_id']); ?>">
                <input type="hidden" name="medecin_id" value="1"> <input type="hidden" name="service_id" value="1"> <div class="mb-3">
                    <label>Motif de la consultation</label>
                    <textarea class="form-control" name="motif_consultation" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Type</label>
                        <select class="form-select" name="type_consultation">
                            <option value="normale">Normale</option>
                            <option value="urgence">Urgence</option>
                            <option value="controle">Contrôle</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Statut</label>
                        <select class="form-select" name="statut">
                            <option value="en_cours">En cours</option>
                            <option value="planifiee">Planifiée</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Diagnostic</label>
                    <textarea class="form-control" name="diagnostic"></textarea>
                </div>
                <div class="mb-3">
                    <label>Traitement prescrit</label>
                    <textarea class="form-control" name="traitement_prescrit"></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100">Enregistrer</button>
            </form>
        </div>
    </div>
</body>
</html>
