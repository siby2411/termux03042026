<footer style="text-align:center;padding:40px 20px;color:var(--muted);font-size:0.75rem;border-top:1px solid var(--border);margin-top:40px">
  <div style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;letter-spacing:0.1em;margin-bottom:4px">
    ✈ OMEGA <span style="color:var(--gold)">AGENCE VOYAGE</span>
  </div>
  Dakar, Sénégal — IATA Certifié · © 2026 · Tous droits réservés
</footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Counter animation
document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseInt(el.dataset.count);
  const duration = 1200;
  const step = target / (duration / 16);
  let current = 0;
  const timer = setInterval(() => {
    current = Math.min(current + step, target);
    el.textContent = Math.floor(current).toLocaleString('fr-FR');
    if (current >= target) clearInterval(timer);
  }, 16);
});
</script>
</body></html>
