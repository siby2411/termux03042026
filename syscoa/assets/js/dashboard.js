// assets/js/dashboard.js
class Dashboard {
    constructor() {
        this.init();
    }
    
    init() {
        this.initSidebar();
        this.initTheme();
        this.initNotifications();
        this.initQuickSearch();
        this.initDataTables();
        this.initCharts();
    }
    
    initSidebar() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                // Sauvegarder l'état
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
            
            // Restaurer l'état
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
        }
    }
    
    initTheme() {
        const themeToggle = document.querySelector('.theme-toggle');
        const html = document.documentElement;
        
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
            
            // Restaurer le thème
            const savedTheme = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-theme', savedTheme);
        }
    }
    
    initNotifications() {
        const notificationBtn = document.querySelector('.btn-quick:nth-child(2)');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', () => {
                this.showNotifications();
            });
        }
    }
    
    showNotifications() {
        Swal.fire({
            title: 'Notifications',
            html: `
                <div class="notifications-list">
                    <div class="notification-item">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <div>
                            <strong>Rapprochement bancaire</strong>
                            <p>3 comptes nécessitent un rapprochement</p>
                            <small>Il y a 2 heures</small>
                        </div>
                    </div>
                    <div class="notification-item">
                        <i class="fas fa-boxes text-danger"></i>
                        <div>
                            <strong>Stock minimum</strong>
                            <p>5 articles atteignent le stock minimum</p>
                            <small>Il y a 1 jour</small>
                        </div>
                    </div>
                    <div class="notification-item">
                        <i class="fas fa-calendar-times text-info"></i>
                        <div>
                            <strong>Clôture exercice</strong>
                            <p>Échéance dans 15 jours</p>
                            <small>Il y a 3 jours</small>
                        </div>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: 500
        });
    }
    
    initQuickSearch() {
        const searchBtn = document.querySelector('.btn-quick:nth-child(1)');
        const modal = document.getElementById('quickSearchModal');
        
        if (searchBtn && modal) {
            searchBtn.addEventListener('click', () => {
                modal.style.display = 'flex';
                modal.querySelector('.search-input').focus();
            });
            
            const closeBtn = modal.querySelector('.modal-close');
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
            
            // Fermer en cliquant à l'extérieur
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Recherche en temps réel
            const searchInput = modal.querySelector('.search-input');
            searchInput.addEventListener('input', (e) => {
                this.performSearch(e.target.value);
            });
        }
    }
    
    performSearch(query) {
        if (query.length < 2) return;
        
        fetch(`api/search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(results => {
                this.displaySearchResults(results);
            });
    }
    
    displaySearchResults(results) {
        const container = document.querySelector('.search-results');
        container.innerHTML = '';
        
        if (results.length === 0) {
            container.innerHTML = '<div class="no-results">Aucun résultat trouvé</div>';
            return;
        }
        
        results.forEach(result => {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.innerHTML = `
                <i class="${result.icon}"></i>
                <div>
                    <strong>${result.title}</strong>
                    <p>${result.description}</p>
                </div>
            `;
            item.addEventListener('click', () => {
                window.location.href = result.url;
            });
            container.appendChild(item);
        });
    }
    
    initDataTables() {
        // Configuration globale pour DataTables
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            responsive: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            dom: '<"dt-header"lf>rt<"dt-footer"ip>',
            initComplete: function(settings, json) {
                this.api().columns().every(function() {
                    const column = this;
                    const header = $(column.header());
                    if (header.data('searchable') !== false) {
                        // Ajouter un champ de recherche par colonne si nécessaire
                    }
                });
            }
        });
    }
    
    initCharts() {
        // Initialisation des graphiques
        const chartElements = document.querySelectorAll('[data-chart]');
        chartElements.forEach(element => {
            const type = element.getAttribute('data-chart-type') || 'line';
            const config = JSON.parse(element.getAttribute('data-chart-config') || '{}');
            this.createChart(element, type, config);
        });
    }
    
    createChart(element, type, config) {
        const ctx = element.getContext('2d');
        const defaultConfig = {
            type: type,
            data: config.data || {},
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                }
                                if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + 'K';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        };
        
        const finalConfig = {...defaultConfig, ...config};
        return new Chart(ctx, finalConfig);
    }
    
    // Méthodes utilitaires
    static formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    }
    
    static formatCurrency(amount, currency = 'FCFA') {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'XOF',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    static formatDate(date) {
        return new Intl.DateTimeFormat('fr-FR').format(new Date(date));
    }
    
    static showLoading(element) {
        element.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner"></div>
                <p class="mt-3">Chargement...</p>
            </div>
        `;
    }
    
    static hideLoading(element, content) {
        element.innerHTML = content;
    }
    
    static confirmAction(message, callback) {
        Swal.fire({
            title: 'Confirmation',
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Confirmer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    }
    
    static showSuccess(message) {
        Swal.fire({
            title: 'Succès',
            text: message,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }
    
    static showError(message) {
        Swal.fire({
            title: 'Erreur',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

// Initialisation au chargement du document
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new Dashboard();
});
