document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.5s ease';
        observer.observe(card);
    });
    
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if(!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
});

function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    if(!input) return;
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    if(!table) return;
    const tr = table.getElementsByTagName("tr");
    
    for(let i = 1; i < tr.length; i++) {
        let txtValue = tr[i].textContent || tr[i].innerText;
        tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
    }
}
