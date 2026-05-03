$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
    setTimeout(function() { $('.alert').fadeOut('slow'); }, 5000);
    $('.btn-delete').on('click', function(e) {
        if(!confirm('Êtes-vous sûr ?')) e.preventDefault();
    });
});
window.formatNumber = function(n) { return new Intl.NumberFormat('fr-FR').format(n); };
