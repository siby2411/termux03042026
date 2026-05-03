// Fonction pour vérifier les rendez-vous urgents
function checkUrgentAppointments() {
    fetch('modules/statistics/urgent_appointments.php')
        .then(response => response.json())
        .then(data => {
            if (data.urgentCount > 0) {
                alert(`⚠️ Vous avez ${data.urgentCount} rendez-vous urgents aujourd'hui !`);
            }
        });
}

// Vérifier toutes les 5 minutes
setInterval(checkUrgentAppointments, 300000);

// Vérifier au chargement de la page
document.addEventListener('DOMContentLoaded', checkUrgentAppointments);
