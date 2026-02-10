<?php
// partials/footer.php
?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Scripts personnalisés -->
<script src="assets/js/main.js"></script>

<script>
// Toggle sidebar
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.body.classList.toggle('sidebar-collapsed');
    
    // Sauvegarder dans un cookie
    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
    document.cookie = `sidebar-collapsed=${isCollapsed}; path=/; max-age=31536000`;
    
    // Changer l'icône
    const icon = this.querySelector('i');
    const text = this.querySelector('span');
    if (isCollapsed) {
        icon.className = 'fas fa-chevron-right';
        text.textContent = 'Étendre';
    } else {
        icon.className = 'fas fa-chevron-left';
        text.textContent = 'Réduire';
    }
});

// Initialiser DataTables
$(document).ready(function() {
    $('.datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        pageLength: 25,
        responsive: true
    });
});

// Gestion des messages flash
<?php if (isset($_SESSION['success'])): ?>
Swal.fire({
    title: 'Succès !',
    text: '<?php echo addslashes($_SESSION['success']); ?>',
    icon: 'success',
    timer: 3000
});
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
Swal.fire({
    title: 'Erreur !',
    text: '<?php echo addslashes($_SESSION['error']); ?>',
    icon: 'error'
});
<?php unset($_SESSION['error']); endif; ?>
</script>

</body>
</html>
