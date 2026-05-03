function initCalendar(calendarId, eventsUrl) {
    const calendarEl = document.getElementById(calendarId);
    if(!calendarEl) return null;
    return new FullCalendar.Calendar(calendarEl, {
        locale: 'fr',
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
        events: eventsUrl
    });
}
