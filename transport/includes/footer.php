<?php
// includes/footer.php - Version finale OMEGA CONSULTING
// Dernière mise à jour: Avril 2025
?>
    </main>
    
    <!-- Footer professionnel transport -->
    <footer style="background: #001f3f; color: #ddd; padding: 50px 20px 25px; margin-top: 60px;">
        <div class="container">
            <div class="row">
                <!-- Colonne 1: Informations société -->
                <div class="col-md-3 mb-4">
                    <h4 style="color: #ff9900; margin-bottom: 20px;">
                        <i class="fas fa-bus"></i> OMEGA Transport
                    </h4>
                    <p>Solution de transport scolaire sécurisée à Dakar et dans toute l'Afrique de l'Ouest.</p>
                    <p>
                        <i class="fas fa-map-marker-alt"></i> Dakar, Sénégal<br>
                        <i class="fas fa-phone-alt"></i> +221 77 654 28 03<br>
                        <i class="fab fa-whatsapp"></i> WhatsApp: +221 77 654 28 03<br>
                        <i class="fas fa-envelope"></i> transport@omega-consulting.sn
                    </p>
                </div>
                
                <!-- Colonne 2: Horaires et services -->
                <div class="col-md-3 mb-4">
                    <h4 style="color: #ff9900; margin-bottom: 20px;">
                        <i class="fas fa-clock"></i> Horaires
                    </h4>
                    <p>
                        <strong>Matin (École):</strong><br>
                        06h30 - 08h30<br>
                        <strong>Soir (Domicile):</strong><br>
                        16h00 - 18h30<br>
                        <strong>Secrétariat:</strong><br>
                        Lun-Sam: 8h - 18h
                    </p>
                </div>
                
                <!-- Colonne 3: Liens rapides -->
                <div class="col-md-3 mb-4">
                    <h4 style="color: #ff9900; margin-bottom: 20px;">
                        <i class="fas fa-link"></i> Liens rapides
                    </h4>
                    <ul style="list-style: none; padding-left: 0;">
                        <li><a href="/modules/parents/inscription_parent.php" style="color: #ddd; text-decoration: none;"><i class="fas fa-user-plus"></i> Inscription</a></li>
                        <li><a href="/modules/parents/liste_parents.php" style="color: #ddd; text-decoration: none;"><i class="fas fa-users"></i> Liste des parents</a></li>
                        <li><a href="/modules/paiements/gestion_paiement.php" style="color: #ddd; text-decoration: none;"><i class="fas fa-credit-card"></i> Gestion paiements</a></li>
                        <li><a href="/modules/recherche/recherche_par_code.php" style="color: #ddd; text-decoration: none;"><i class="fas fa-qrcode"></i> Recherche par code</a></li>
                        <li><a href="/modules/eleves/liste_eleves.php" style="color: #ddd; text-decoration: none;"><i class="fas fa-child"></i> Liste des élèves</a></li>
                        <li><a href="/modules/bus/galerie_bus.php" style="color: #ddd; text-decoration: none;"><i class="fas fa-bus"></i> Galerie du parc</a></li>
                    </ul>
                </div>
                
                <!-- Colonne 4: Inscriptions et contact -->
                <div class="col-md-3 mb-4">
                    <h4 style="color: #ff9900; margin-bottom: 20px;">
                        <i class="fas fa-calendar-alt"></i> Inscriptions 2025-2026
                    </h4>
                    <p>
                        <strong>Début des inscriptions:</strong><br>
                        1er Juin 2025<br>
                        <strong>Date limite:</strong><br>
                        30 Septembre 2025
                    </p>
                    <a href="/modules/parents/inscription_parent.php" class="btn btn-warning btn-sm" style="background:#ff9900; color:#001f3f; border-radius: 30px;">
                        <i class="fas fa-pen"></i> Inscrire mon enfant
                    </a>
                    <div class="mt-3">
                        <p><strong>Partenaires agréés:</strong></p>
                        <p>
                            <i class="fab fa-wave"></i> Wave<br>
                            <i class="fas fa-mobile-alt"></i> Orange Money<br>
                            <i class="fas fa-shield-alt"></i> Assurance ALLIANZ Sénégal
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Ligne de séparation -->
            <div class="row mt-3">
                <div class="col-12">
                    <hr style="border-color: #444;">
                </div>
            </div>
            
            <!-- Copyright et mentions légales -->
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p style="font-size: 0.85rem;">
                        &copy; 2025 OMEGA INFORMATIQUE CONSULTING<br>
                        <small>Tous droits réservés | Conçu avec <i class="fas fa-heart" style="color: #ff4444;"></i> pour l'excellence du transport scolaire au Sénégal</small>
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p style="font-size: 0.85rem;">
                        <i class="fas fa-certificate"></i> Certifié ISO 9001<br>
                        <i class="fas fa-code-branch"></i> Version 2.0 | Développé sous PROOT-DISTRO Termux
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        // Tooltip auto-initialisation
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Fonction de copie de texte
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Notification toast optionnelle
                console.log('Copié: ' + text);
            });
        }
        
        // Détection de la connexion
        window.addEventListener('load', function() {
            console.log('OMEGA Transport - Application chargée');
        });
    </script>
</body>
</html>
