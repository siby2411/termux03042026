<?php // includes/footer.php ?>
  </div><!-- /.content -->
</main><!-- /.main -->

<script>
// Confirmation suppression
document.querySelectorAll('.confirm-delete').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm('Confirmer la suppression ?')) e.preventDefault();
  });
});

// Auto-hide flash après 5s
const flash = document.querySelector('.flash');
if (flash) setTimeout(() => flash.style.opacity = '0', 5000);

// Ouvrir modal
document.querySelectorAll('[data-modal]').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.modal;
    document.getElementById(id)?.classList.add('open');
  });
});

// Fermer modal
document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
  el.addEventListener('click', e => {
    if (e.target === el) el.closest('.modal-overlay')?.classList.remove('open');
    el.closest('.modal-overlay')?.classList.remove('open');
  });
});
document.querySelectorAll('.modal').forEach(m => {
  m.addEventListener('click', e => e.stopPropagation());
});

// Recherche tableau dynamique
const searchInput = document.getElementById('tableSearch');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}
</script>
</body>
</html>
