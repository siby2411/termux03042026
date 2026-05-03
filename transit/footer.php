    </div> <!-- Fin container -->
    
    <!-- Footer -->
    <footer class="footer-omega">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-ship me-2"></i>OMEGA TRANSIT</h5>
                    <p>Solution innovante de gestion de transit maritime et aéroportuaire conforme aux standards internationaux.</p>
                    <div class="mt-3">
                        <i class="fab fa-whatsapp fa-2x me-2" style="color: #25D366;"></i>
                        <i class="fab fa-linkedin fa-2x me-2"></i>
                        <i class="fab fa-twitter fa-2x me-2"></i>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liens Rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">À propos</a></li>
                        <li><a href="#">Services</a></li>
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Conditions générales</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Newsletter</h5>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Votre email">
                        <button class="btn btn-warning" type="button">S'abonner</button>
                    </div>
                    <hr class="bg-light">
                    <small>© 2024 OMEGA INFORMATIQUE CONSULTING - Tous droits réservés</small>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Animation au scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.navbar-custom');
            if (window.scrollY > 100) {
                nav.style.position = 'fixed';
                nav.style.top = '0';
                nav.style.width = '100%';
                nav.style.zIndex = '1000';
            } else {
                nav.style.position = 'relative';
            }
        });
    </script>
</body>
</html>
