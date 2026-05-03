// Fonctions utilitaires pour le projet
document.addEventListener('DOMContentLoaded', function() {
    // Exemple : Gestion des onglets
    const tabs = document.querySelectorAll('.tab-button');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            document.getElementById(target).style.display = 'block';
        });
    });
});
