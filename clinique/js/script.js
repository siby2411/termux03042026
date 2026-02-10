// Fonctions utilitaires pour le système de gestion
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des messages flash
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Confirmation des actions critiques
    const confirmLinks = document.querySelectorAll('a[data-confirm]');
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // Recherche en temps réel
    const searchInput = document.querySelector('input[type="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Auto-formatage des numéros de téléphone
    const telInputs = document.querySelectorAll('input[type="tel"]');
    telInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    });

    // Calcul automatique des montants dans les factures
    const calculateTotal = () => {
        const rows = document.querySelectorAll('.ligne-facture');
        let total = 0;
        
        rows.forEach(row => {
            const quantite = parseFloat(row.querySelector('.quantite').value) || 0;
            const prix = parseFloat(row.querySelector('.prix').value) || 0;
            const montant = quantite * prix;
            
            row.querySelector('.montant').textContent = montant.toLocaleString('fr-FR');
            total += montant;
        });
        
        document.getElementById('montant-total').value = total;
        document.getElementById('montant-total-display').textContent = total.toLocaleString('fr-FR') + ' FCFA';
    };

    // Initialiser le calcul si on est sur une page de facturation
    if (document.querySelector('.ligne-facture')) {
        calculateTotal();
        
        // Écouter les changements
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantite') || e.target.classList.contains('prix')) {
                calculateTotal();
            }
        });
    }
});

// Fonction pour afficher/masquer les sections
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    }
}

// Export des données en CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Nettoyer le texte
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '')
                                       .replace(/(\s\s)/gm, ' ')
                                       .replace(/"/g, '""');
            data = `"${data}"`;
            row.push(data);
        }
        
        csv.push(row.join(','));
    }
    
    // Télécharger le fichier
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Graphiques dynamiques
function initCharts() {
    // Exemple d'initialisation de graphique
    const ctx = document.getElementById('activityChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                datasets: [{
                    label: 'Consultations',
                    data: [12, 19, 15, 17, 14, 8],
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
}

// Initialiser les graphiques au chargement
document.addEventListener('DOMContentLoaded', initCharts);
