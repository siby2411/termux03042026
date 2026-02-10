<?php
// /var/www/syscoa/includes/footer_complet.php
?>
        </div>
    </main>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    
    <script>
    // Gestion du sidebar
    document.addEventListener('DOMContentLoaded', function() {
        // Activer les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Gérer le collapse du sidebar
        $('.nav-link[data-bs-toggle="collapse"]').on('click', function() {
            $(this).toggleClass('active');
        });
        
        // Auto-activer le module courant
        var currentModule = '<?php echo $current_module; ?>';
        if (currentModule && currentModule !== 'dashboard') {
            $('a[href*="module=' + currentModule + '"]').addClass('active');
            $('#' + currentModule + 'Submenu').addClass('show');
        }
        
        // Notifications
        $('.dropdown-toggle').dropdown();
    });
    </script>
</body>
</html>
