// Fonctions utilitaires pour OMEGA CONSULTING Librairie

// Formatage des nombres en FCFA
function formatFCFA(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0
    }).format(amount);
}

// Confirmation avant action
function confirmAction(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir effectuer cette action ?');
}

// Notifications
function showNotification(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Recherche en temps réel
function liveSearch(inputId, resultContainerId, searchUrl) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(resultContainerId);
    let timeoutId;
    
    input.addEventListener('keyup', function() {
        clearTimeout(timeoutId);
        const searchTerm = this.value;
        
        if (searchTerm.length < 2) {
            container.innerHTML = '';
            return;
        }
        
        timeoutId = setTimeout(() => {
            fetch(`${searchUrl}?q=${encodeURIComponent(searchTerm)}`)
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
        }, 300);
    });
}

// Export en PDF
function exportToPDF(elementId, filename = 'document.pdf') {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const opt = {
        margin: 1,
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    
    html2pdf().set(opt).from(element).save();
}

// Graphiques
function initCharts() {
    // Graphique des ventes
    const ctx = document.getElementById('ventesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'Chiffre d\'affaires',
                    data: [12000, 19000, 15000, 17000, 21000, 25000, 28000, 26000, 30000, 32000, 35000, 40000],
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Évolution du chiffre d\'affaires'
                    }
                }
            }
        });
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialiser les graphiques
    initCharts();
    
    // Gestion des formulaires
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Date picker automatique
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.value = new Date().toISOString().split('T')[0];
        }
    });
});
