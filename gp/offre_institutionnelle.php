<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .offre-container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
    .offre-header { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); color: white; padding: 40px; text-align: center; }
    .offre-header h1 { font-size: 2.5rem; font-weight: 800; }
    .offre-header h1 span { color: #ff8c00; }
    .offre-section { padding: 30px; border-bottom: 1px solid #eee; }
    .offre-section h2 { color: #ff8c00; border-left: 4px solid #ff8c00; padding-left: 15px; margin-bottom: 20px; }
    .feature-card { background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 20px; transition: transform 0.2s; }
    .feature-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .feature-icon { font-size: 2.5rem; color: #ff8c00; margin-bottom: 15px; }
    .btn-print-offre { background: #ff8c00; color: white; border: none; padding: 12px 30px; border-radius: 30px; font-weight: bold; margin: 20px; }
    .table-modules { width: 100%; border-collapse: collapse; }
    .table-modules th, .table-modules td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    .table-modules th { background: #ff8c00; color: white; }
    @media print {
        .no-print, .btn-print-offre, nav, footer, .top-bar { display: none !important; }
        body { background: white; padding: 0; margin: 0; }
        .offre-container { box-shadow: none; margin: 0; }
    }
</style>

<div class="offre-container">
    <div class="offre-header">
        <img src="logo.jpg" alt="Logo" style="max-height: 80px; margin-bottom: 20px;">
        <h1>Dieynaba <span>GP Holding</span></h1>
        <p>Solutions intégrées de fret, logistique, e-commerce et communication</p>
        <p class="mt-3"><strong>Siège social :</strong> Hann Maristes – À côté École Franco-Japonaise, Dakar, Sénégal</p>
        <p><strong>Antenne :</strong> Saint-Denis, Île-de-France, France</p>
    </div>

    <div class="offre-section">
        <h2>📋 Synthèse de l’offre</h2>
        <p>Dieynaba GP Holding propose une plateforme technologique complète couvrant :</p>
        <ul>
            <li><strong>Transport international</strong> – Gestion bidirectionnelle des colis entre la France et le Sénégal</li>
            <li><strong>4 boutiques en ligne intégrées</strong> – Épicerie, Mode, Joaillerie, Négoce</li>
            <li><strong>Communication client</strong> – WhatsApp Business API, QR codes, notifications automatiques</li>
            <li><strong>Gestion financière</strong> – États consolidés, charges, bénéfices par secteur</li>
            <li><strong>Géolocalisation</strong> – Suivi GPS des colis, carte interactive</li>
        </ul>
    </div>

    <div class="offre-section">
        <h2>✈️ Module Fret & Logistique</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-exchange-alt"></i></div>
                    <h5>Gestion bidirectionnelle</h5>
                    <p>Expéditions de colis Paris ↔ Dakar avec tarifs différenciés (TVA 20% / 18%).</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-qrcode"></i></div>
                    <h5>QR codes personnalisés</h5>
                    <p>Chaque colis génère des QR codes distincts pour expéditeur et destinataire.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fab fa-whatsapp"></i></div>
                    <h5>Notifications WhatsApp</h5>
                    <p>Alertes automatiques à chaque changement de statut (départ, transit, arrivée, livré).</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-map-marked-alt"></i></div>
                    <h5>Suivi GPS temps réel</h5>
                    <p>Localisation précise des colis via carte Leaflet interactive.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="offre-section">
        <h2>🛍️ Boutiques en ligne (4 secteurs)</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="feature-card text-center">
                    <i class="fas fa-shopping-basket fa-3x text-success"></i>
                    <h5>Épicerie</h5>
                    <p>Produits sénégalais : huile de palme, miel, crevettes, attiéké</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card text-center">
                    <i class="fas fa-tshirt fa-3x text-primary"></i>
                    <h5>Mode</h5>
                    <p>Boubous, kaftans, chemises en tissu wax</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card text-center">
                    <i class="fas fa-gem fa-3x text-warning"></i>
                    <h5>Joaillerie</h5>
                    <p>Bijoux haut de gamme : bagues, colliers, montres</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card text-center">
                    <i class="fas fa-laptop fa-3x text-info"></i>
                    <h5>Négoce</h5>
                    <p>High-Tech, électroménager, mobilier de luxe</p>
                </div>
            </div>
        </div>
    </div>

    <div class="offre-section">
        <h2>📊 Tableau de bord consolidé</h2>
        <table class="table-modules">
            <thead>
                <tr><th>Module</th><th>Fonctionnalités</th><th>Accès</th></tr>
            </thead>
            <tbody>
                <tr><td><strong>Colis</strong></td><td>Création, suivi, admin, QR codes, géolocalisation</td><td>Web / WhatsApp</td></tr>
                <tr><td><strong>Boutiques</strong></td><td>CRUD produits, galerie, commandes WhatsApp</td><td>Web</td></tr>
                <tr><td><strong>Clients</strong></td><td>Gestion fret, mode, prospects</td><td>Web</td></tr>
                <tr><td><strong>Finance</strong></td><td>États fret, holding, charges, statistiques</td><td>Dashboard</td></tr>
                <tr><td><strong>WhatsApp</strong></td><td>Envoi QR codes, notifications statut</td><td>API / Web</td></tr>
            </tbody>
        </table>
    </div>

    <div class="offre-section">
        <h2>💰 Tarifs & Conditions</h2>
        <div class="row">
            <div class="col-md-6">
                <h5>Service fret</h5>
                <ul>
                    <li>Colis &lt; 5 kg : <strong>45 €</strong></li>
                    <li>Colis 5-10 kg : <strong>65 €</strong></li>
                    <li>Abonnement annuel (10 colis) : <strong>350 €</strong></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5>Boutiques</h5>
                <ul>
                    <li>Commission sur vente : <strong>15%</strong></li>
                    <li>Création catalogue : <strong>Offerte</strong></li>
                    <li>Support technique : <strong>Inclus</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="offre-section">
        <h2>📞 Contacts institutionnels</h2>
        <p><strong>Madame Dieynaba Keita</strong> – Directrice générale</p>
        <p>📱 WhatsApp / Tél : <strong>+221 77 654 28 03</strong> | <strong>+33 7 58 68 63 48</strong></p>
        <p>📧 Email : <strong>contact@dieynaba.com</strong></p>
        <p>🌐 Site : <strong>http://127.0.0.1:8000</strong></p>
        <p>📍 Showroom : Hann Maristes – À côté École Franco-Japonaise, Dakar</p>
        <p>📍 Antenne France : Saint-Denis, Île-de-France</p>
        <hr>
        <p><em>“Dieynaba GP Holding – Le pont entre l’Afrique et l’Europe”</em></p>
    </div>
</div>

<div class="text-center no-print mt-4">
    <button onclick="window.print()" class="btn-print-offre"><i class="fas fa-print"></i> Imprimer / PDF</button>
    <button onclick="window.close()" class="btn-print-offre" style="background:#666;">Fermer</button>
</div>

<?php include('footer.php'); ?>
