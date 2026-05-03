<?php
// includes/footer.php
?>
        </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById("menu-toggle").addEventListener("click", (e) => {
        e.preventDefault();
        document.getElementById("wrapper").classList.toggle("toggled");
    });

    // Gérer l'état actif des liens de la sidebar
    document.addEventListener("DOMContentLoaded", () => {
        // Obtenir le chemin relatif, ex: /gestion_previsionnelle/dashboard_pilote.php
        let path = window.location.pathname;
        
        // Retirer le slash final si présent pour la comparaison
        if (path.endsWith('/')) {
            path = path.substring(0, path.length - 1);
        }

        const links = document.querySelectorAll('#sidebar-wrapper a');
        links.forEach(link => {
            link.classList.remove('active');
            
            // Si le lien correspond au chemin, l'activer
            if (link.getAttribute('href') === path) {
                link.classList.add('active');
            }
        });
    });
</script>

</body>
</html>
