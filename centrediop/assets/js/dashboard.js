let refreshInterval = null;

function startRefresh(seconds) {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    if (seconds > 0) {
        refreshInterval = setInterval(function() {
            // Rafraîchir uniquement les données sans recharger toute la page
            fetchData();
        }, seconds * 1000);
    }
}

function fetchData() {
    // Récupérer les nouvelles données via AJAX
    fetch('/modules/admin/get_stats.php')
        .then(response => response.json())
        .then(data => {
            updateStats(data);
        })
        .catch(error => console.error('Erreur:', error));
}

function updateStats(data) {
    // Mettre à jour les KPIs sans recharger la page
    if (data.stats) {
        document.querySelectorAll('.kpi-value').forEach((el, index) => {
            // Mise à jour des valeurs
        });
    }
    
    // Mettre à jour la file d'attente
    if (data.queue) {
        // Mise à jour de la liste
    }
}

function showRefreshOptions() {
    const options = [
        { label: 'Désactivé', value: 0 },
        { label: '30 secondes', value: 30 },
        { label: '1 minute', value: 60 },
        { label: '5 minutes', value: 300 }
    ];
    
    // Afficher un modal de sélection
    // ...
}
