<?php 
require_once 'db_connect.php';
include('header.php'); 
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .vetement-card { transition: transform 0.3s; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px; }
    .vetement-card:hover { transform: translateY(-10px); }
    .vetement-img { height: 300px; object-fit: cover; width: 100%; }
    .categorie-badge { position: absolute; top: 10px; right: 10px; background: #ff8c00; color: white; padding: 5px 12px; border-radius: 20px; }
    .btn-wa { background: #25D366; color: white; border: none; padding: 8px 15px; border-radius: 30px; }
    .gallery-title { background: linear-gradient(135deg, #8E2DE2, #4A00E0); color: white; padding: 40px; text-align: center; border-radius: 20px; margin-bottom: 40px; }
</style>

<div class="gallery-title">
    <h1><i class="fas fa-tshirt"></i> Dieynaba Fashion – Tenues Traditionnelles Africaines</h1>
    <p>Authenticité, élégance et savoir-faire artisanal – Livraison en France</p>
</div>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-6 offset-md-3">
            <form method="get" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un vêtement..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <select name="categorie" class="form-select w-auto">
                    <option value="">Toutes catégories</option>
                    <option value="Homme" <?= ($_GET['categorie']??'')=='Homme'?'selected':'' ?>>Homme</option>
                    <option value="Femme" <?= ($_GET['categorie']??'')=='Femme'?'selected':'' ?>>Femme</option>
                </select>
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <div class="row">
        <?php
        $search = $_GET['search'] ?? '';
        $categorie = $_GET['categorie'] ?? '';
        $sql = "SELECT * FROM vetements WHERE stock > 0";
        $params = [];
        if ($search) {
            $sql .= " AND (nom LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($categorie) {
            $sql .= " AND categorie = ?";
            $params[] = $categorie;
        }
        $sql .= " ORDER BY date_ajout DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $produits = $stmt->fetchAll();

        if (count($produits) == 0): ?>
            <div class="col-12 text-center"><p>Aucun vêtement disponible pour le moment.</p></div>
        <?php else: foreach ($produits as $p): 
            $img = (!empty($p['image']) && file_exists($p['image'])) ? $p['image'] : 'https://placehold.co/400x300?text=Vetement+Africain';
        ?>
        <div class="col-md-4 col-lg-3">
            <div class="vetement-card card h-100">
                <div style="position: relative;">
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top vetement-img" alt="<?= htmlspecialchars($p['nom']) ?>">
                    <span class="categorie-badge"><?= htmlspecialchars($p['categorie']) ?></span>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($p['nom']) ?></h5>
                    <p class="card-text small"><?= nl2br(htmlspecialchars(substr($p['description'],0,100))) ?>...</p>
                    <div class="mb-2"><strong>Tailles :</strong> <?= $p['tailles'] ?></div>
                    <div class="mb-2"><strong>Couleurs :</strong> <?= $p['couleurs'] ?></div>
                    <h4 class="text-warning"><?= number_format($p['prix'],2) ?> €</h4>
                    <div class="d-grid">
                        <button class="btn-wa" data-bs-toggle="modal" data-bs-target="#commandeModal" data-id="<?= $p['id'] ?>" data-nom="<?= htmlspecialchars($p['nom']) ?>" data-prix="<?= $p['prix'] ?>">
                            <i class="fab fa-whatsapp"></i> Commander
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Modal pour la commande WhatsApp -->
<div class="modal fade" id="commandeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Commande par WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCommande">
                    <input type="hidden" id="produit_id">
                    <input type="hidden" id="produit_nom">
                    <input type="hidden" id="produit_prix">
                    <div class="mb-3">
                        <label>Votre numéro WhatsApp</label>
                        <input type="tel" id="tel_client" class="form-control" placeholder="ex: 33758686348" required>
                    </div>
                    <div class="mb-3">
                        <label>Taille souhaitée</label>
                        <input type="text" id="taille" class="form-control" placeholder="ex: M">
                    </div>
                    <div class="mb-3">
                        <label>Couleur</label>
                        <input type="text" id="couleur" class="form-control" placeholder="ex: Rouge">
                    </div>
                    <div class="mb-3">
                        <label>Quantité</label>
                        <input type="number" id="quantite" class="form-control" value="1" min="1">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="envoyerWA">Envoyer la commande</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const commandeModal = document.getElementById('commandeModal');
commandeModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const nom = button.getAttribute('data-nom');
    const prix = button.getAttribute('data-prix');
    document.getElementById('produit_id').value = id;
    document.getElementById('produit_nom').value = nom;
    document.getElementById('produit_prix').value = prix;
});
document.getElementById('envoyerWA').addEventListener('click', function() {
    let tel = document.getElementById('tel_client').value;
    let nom = document.getElementById('produit_nom').value;
    let prix = document.getElementById('produit_prix').value;
    let taille = document.getElementById('taille').value;
    let couleur = document.getElementById('couleur').value;
    let quantite = document.getElementById('quantite').value;
    if (!tel) { alert('Veuillez entrer votre numéro WhatsApp'); return; }
    let message = `Bonjour, je souhaite commander : ${nom} (${prix}€). Taille: ${taille}, Couleur: ${couleur}, Quantité: ${quantite}. Mon numéro pour livraison : ${tel}`;
    let whatsappUrl = `https://wa.me/33758686348?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
    bootstrap.Modal.getInstance(commandeModal).hide();
});
</script>
<?php include('footer.php'); ?>
