<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// --- TRAITEMENT AJOUT CLIENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])) {
    $mesures = json_encode([
        'cou' => $_POST['m_cou'] ?? 0,
        'epaule' => $_POST['m_epaule'] ?? 0,
        'poitrine' => $_POST['m_poitrine'] ?? 0,
        'longueur_boubou' => $_POST['m_longueur'] ?? 0,
        'taille' => $_POST['m_taille'] ?? 0
    ]);

    $code = genererCode('CLI', 'clients', 'code_client');
    
    $stmt = $pdo->prepare("INSERT INTO clients (code_client, nom, prenom, genre, telephone, adresse, mesures) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$code, $_POST['nom'], $_POST['prenom'], $_POST['genre'], $_POST['telephone'], $_POST['adresse'], $mesures]);
    
    setFlash('success', "Client ajouté avec succès !");
    header("Location: clients.php"); exit;
}

// --- RECHERCHE ET LISTE ---
$search = $_GET['q'] ?? '';
$sql = "SELECT c.*, 
        (SELECT SUM(reste) FROM factures WHERE client_id = c.id AND statut != 'payée') as dette_totale
        FROM clients c";
if($search) {
    $sql .= " WHERE c.nom LIKE :q OR c.prenom LIKE :q OR c.telephone LIKE :q";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['q' => "%$search%"]);
} else {
    $stmt = $pdo->query($sql);
}
$clients = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Gestion des Clients & Mesures</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalClient">
        <i class="bi bi-person-plus"></i> Nouveau Client
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control" placeholder="Chercher un nom, un prénom ou un numéro..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-dark px-4">Rechercher</button>
            <?php if($search): ?><a href="clients.php" class="btn btn-light border">X</a><?php endif; ?>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php foreach($clients as $cl): 
        $m = json_decode($cl['mesures'], true);
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
                <span class="badge bg-light text-dark border"><?= $cl['code_client'] ?></span>
                <span class="badge bg-secondary"><?= $cl['genre'] == 'H' ? '♂ Homme' : '♀ Femme' ?></span>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1"><?= strtoupper($cl['nom']) ?> <?= $cl['prenom'] ?></h5>
                <p class="text-muted small"><i class="bi bi-telephone"></i> <?= $cl['telephone'] ?></p>
                
                <div class="bg-light p-2 rounded mb-3">
                    <div class="row g-2 text-center small">
                        <div class="col-4 border-end"><strong>Cou</strong><br><?= $m['cou'] ?? '-' ?></div>
                        <div class="col-4 border-end"><strong>Epaule</strong><br><?= $m['epaule'] ?? '-' ?></div>
                        <div class="col-4"><strong>Long.</strong><br><?= $m['longueur_boubou'] ?? '-' ?></div>
                    </div>
                </div>

                <?php if($cl['dette_totale'] > 0): ?>
                    <div class="d-flex justify-content-between align-items-center text-danger small fw-bold">
                        <span>Reliquat à payer:</span>
                        <span><?= number_format($cl['dette_totale'], 0, ',', ' ') ?> F</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white border-0 pb-3 d-flex gap-2">
                <a href="<?= lienWhatsApp($cl['telephone'], "Salam " . $cl['prenom'] . ", c'est OMEGA COUTURE...") ?>" target="_blank" class="btn btn-sm btn-outline-success w-100">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
                <button class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-pencil"></i> Mesures</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="modalClient" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fiche Nouveau Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Prénom</label><input type="text" name="prenom" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
                    <div class="col-md-4">
                        <label class="form-label">Genre</label>
                        <select name="genre" class="form-select">
                            <option value="F">Femme</option>
                            <option value="H">Homme</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control" placeholder="77XXXXXXX" required></div>
                    <div class="col-md-6"><label class="form-label">Adresse</label><input type="text" name="adresse" class="form-control"></div>
                    
                    <div class="col-12"><hr><h6 class="fw-bold">Prise de Mesures (cm)</h6></div>
                    <div class="col-md-2"><label class="small">Cou</label><input type="number" name="m_cou" class="form-control"></div>
                    <div class="col-md-2"><label class="small">Épaule</label><input type="number" name="m_epaule" class="form-control"></div>
                    <div class="col-md-2"><label class="small">Poitrine</label><input type="number" name="m_poitrine" class="form-control"></div>
                    <div class="col-md-3"><label class="small">Taille/Ceinture</label><input type="number" name="m_taille" class="form-control"></div>
                    <div class="col-md-3"><label class="small">Long. Boubou/Robe</label><input type="number" name="m_longueur" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary px-4">Enregistrer le client</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
