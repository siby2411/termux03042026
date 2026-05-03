<?php if(!isset($no_navbar) || !$no_navbar): ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="footer text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><i class="fas fa-shield-alt"></i> Assurance Sénégal</h5>
                <p>Conforme aux normes IFRS 17 et Solvabilité II</p>
            </div>
            <div class="col-md-4">
                <h5>Contact</h5>
                <p><i class="fas fa-phone"></i> +221 33 123 45 67<br>
                <i class="fas fa-envelope"></i> contact@assurance.sn</p>
            </div>
            <div class="col-md-4">
                <h5>Horaires</h5>
                <p>Lun-Ven: 8h30 - 18h<br>
                Sam: 9h - 13h</p>
            </div>
        </div>
        <hr class="bg-light">
        <p>&copy; <?php echo date('Y'); ?> Assurance Sénégal - Tous droits réservés</p>
        <p class="small">Développé conformément aux réglementations de la CRCA et de la FANAF</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialisation DataTables
    $('.datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        responsive: true,
        pageLength: 25
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Toggle sidebar on mobile
    $('.navbar-toggler').on('click', function() {
        $('.sidebar').toggleClass('active');
    });
    
    // Confirmation de suppression
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
</script>
</body>
</html>
