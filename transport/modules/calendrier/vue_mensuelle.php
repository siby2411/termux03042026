<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="container mt-4">
    <h2><i class="fas fa-calendar-alt"></i> Calendrier des déplacements</h2>
    <div class="card"><div class="card-body"><div id="calendar"></div></div></div>
</div>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            locale: 'fr', initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
            events: [
                { title: 'Trajet Matin - Bus DK-123-AB', start: '2025-04-15T07:30:00' },
                { title: 'Trajet Soir - Bus DK-123-AB', start: '2025-04-15T16:00:00' }
            ]
        });
        calendar.render();
    });
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
