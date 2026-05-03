<?php
include '../../includes/header.php';
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Générer numéro de facture
        $query = "SELECT COUNT(*) as count FROM factures WHERE YEAR(date_facture) = YEAR(CURDATE())";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;
        $numero_facture = 'FACT-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Gérer la consultation (peut être NULL)
        $id_consultation = !empty($_POST['id_consultation']) ? $_POST['id_consultation'] : null;
        
        // Commencer une transaction
        $db->beginTransaction();
        
        // Insérer la facture
        $query = "INSERT INTO factures (numero_facture, id_patient, id_consultation, montant_total, statut, mode_paiement, notes) 
                  VALUES (:numero_facture, :id_patient, :id_consultation, :montant_total, :statut, :mode_paiement, :notes)";
        
        $stmt = $db->prepare($query);
        $montant_total = floatval($_POST['montant_total']);
        
        $stmt->bindParam(':numero_facture', $numero_facture);
        $stmt->bindParam(':id_patient', $_POST['id_patient']);
        $stmt->bindParam(':id_consultation', $id_consultation);
        $stmt->bindParam(':montant_total', $montant_total);
        $stmt->bindParam(':statut', $_POST['statut']);
        $stmt->bindParam(':mode_paiement', $_POST['mode_paiement']);
        $stmt->bindParam(':notes', $_POST['notes']);
        
        $stmt->execute();
        $facture_id = $db->lastInsertId();
        
        // Insérer les lignes de facture
        if (isset($_POST['designation']) && is_array($_POST['designation'])) {
            $query = "INSERT INTO lignes_facture (id_facture, designation, quantite, prix_unitaire) 
                      VALUES (:id_facture, :designation, :quantite, :prix_unitaire)";
            $stmt = $db->prepare($query);
            
            for ($i = 0; $i < count($_POST['designation']); $i++) {
                if (!empty($_POST['designation'][$i])) {
                    $quantite = floatval($_POST['quantite'][$i]);
                    $prix_unitaire = floatval($_POST['prix_unitaire'][$i]);
                    
                    $stmt->bindParam(':id_facture', $facture_id);
                    $stmt->bindParam(':designation', $_POST['designation'][$i]);
                    $stmt->bindParam(':quantite', $quantite);
                    $stmt->bindParam(':prix_unitaire', $prix_unitaire);
                    $stmt->execute();
                }
            }
        }
        
        $db->commit();
        
        header("Location: list.php?success=Facture créée avec succès");
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de la création de la facture: " . $e->getMessage();
    }
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les patients
$query = "SELECT id, code_patient, nom, prenom FROM patients ORDER BY nom, prenom";
$stmt = $db->prepare($query);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les consultations (uniquement celles sans facture)
$query = "SELECT c.id, p.nom, p.prenom, c.date_consultation 
          FROM consultations c 
          JOIN patients p ON c.id_patient = p.id 
          LEFT JOIN factures f ON c.id = f.id_consultation 
          WHERE f.id IS NULL 
          ORDER BY c.date_consultation DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-receipt me-2"></i>Nouvelle Facture
                </h2>
                <p class="text-muted mb-0">Créer une nouvelle facture pour un patient</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" id="factureForm">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="id_patient" class="form-label">Patient *</label>
                                <select id="id_patient" name="id_patient" class="form-select" required>
                                    <option value="">Sélectionner un patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo $patient['id']; ?>">
                                            <?php echo htmlspecialchars($patient['code_patient'] . ' - ' . $patient['prenom'] . ' ' . $patient['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="id_consultation" class="form-label">Consultation (optionnel)</label>
                                <select id="id_consultation" name="id_consultation" class="form-select">
                                    <option value="">Sélectionner une consultation</option>
                                    <?php foreach ($consultations as $consult): ?>
                                        <option value="<?php echo $consult['id']; ?>">
                                            <?php echo htmlspecialchars($consult['prenom'] . ' ' . $consult['nom'] . ' - ' . date('d/m/Y', strtotime($consult['date_consultation']))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Lignes de facture -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Prestations</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addLigne">
                                <i class="bi bi-plus me-1"></i>Ajouter une ligne
                            </button>
                        </div>
                        
                        <div id="lignesFacture">
                            <div class="row ligne-facture mb-2">
                                <div class="col-md-5">
                                    <input type="text" name="designation[]" class="form-control" placeholder="Désignation" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="quantite[]" class="form-control quantite" value="1" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="prix_unitaire[]" class="form-control prix" step="0.01" min="0" placeholder="Prix unitaire" required>
                                </div>
                                <div class="col-md-2">
                                    <span class="form-control-plaintext montant">0 FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="statut" class="form-label">Statut *</label>
                                <select id="statut" name="statut" class="form-select" required>
                                    <option value="Emise">Emise</option>
                                    <option value="Partiellement Payée">Partiellement Payée</option>
                                    <option value="Payée">Payée</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="mode_paiement" class="form-label">Mode de paiement</label>
                                <select id="mode_paiement" name="mode_paiement" class="form-select">
                                    <option value="">Sélectionner</option>
                                    <option value="Espèces">Espèces</option>
                                    <option value="Carte">Carte</option>
                                    <option value="Virement">Virement</option>
                                    <option value="Chèque">Chèque</option>
                                    <option value="Assurance">Assurance</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Notes supplémentaires..."></textarea>
                    </div>

                    <!-- Total -->
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Total:</strong>
                                        <strong id="montantTotal">0 FCFA</strong>
                                    </div>
                                    <input type="hidden" name="montant_total" id="montantTotalInput" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="list.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer la Facture</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lignesContainer = document.getElementById('lignesFacture');
    const addButton = document.getElementById('addLigne');
    const montantTotal = document.getElementById('montantTotal');
    const montantTotalInput = document.getElementById('montantTotalInput');

    // Ajouter une ligne
    addButton.addEventListener('click', function() {
        const newLigne = document.createElement('div');
        newLigne.className = 'row ligne-facture mb-2';
        newLigne.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="designation[]" class="form-control" placeholder="Désignation" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="quantite[]" class="form-control quantite" value="1" min="1" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="prix_unitaire[]" class="form-control prix" step="0.01" min="0" placeholder="Prix unitaire" required>
            </div>
            <div class="col-md-2">
                <span class="form-control-plaintext montant">0 FCFA</span>
                <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-ligne">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        lignesContainer.appendChild(newLigne);
        attachEventListeners(newLigne);
    });

    // Calculer le total
    function calculerTotal() {
        let total = 0;
        document.querySelectorAll('.ligne-facture').forEach(ligne => {
            const quantite = parseFloat(ligne.querySelector('.quantite').value) || 0;
            const prix = parseFloat(ligne.querySelector('.prix').value) || 0;
            const montant = quantite * prix;
            ligne.querySelector('.montant').textContent = montant.toLocaleString('fr-FR') + ' FCFA';
            total += montant;
        });
        montantTotal.textContent = total.toLocaleString('fr-FR') + ' FCFA';
        montantTotalInput.value = total;
    }

    // Attacher les événements
    function attachEventListeners(ligne) {
        ligne.querySelector('.quantite').addEventListener('input', calculerTotal);
        ligne.querySelector('.prix').addEventListener('input', calculerTotal);
        ligne.querySelector('.remove-ligne')?.addEventListener('click', function() {
            ligne.remove();
            calculerTotal();
        });
    }

    // Initialiser
    document.querySelectorAll('.ligne-facture').forEach(attachEventListeners);
    calculerTotal();
});
</script>

<?php include '../../includes/footer.php'; ?>
