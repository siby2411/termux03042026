                </div> <!-- Fin du contenu -->
            </div> <!-- Fin col-md-10 -->
        </div> <!-- Fin row -->
    </div> <!-- Fin container-fluid -->
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Scripts de base
        console.log('SYSCOHADA chargé - Module: <?php echo $current_module; ?>');
        
        // Auto-hide des alertes
        setTimeout(function() {
            document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
        
        // Formatage automatique des montants
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('format-amount')) {
                let value = e.target.value.replace(/[^\d]/g, '');
                if (value) {
                    e.target.value = new Intl.NumberFormat('fr-FR').format(parseInt(value));
                }
            }
        });
    </script>
</body>
</html>
