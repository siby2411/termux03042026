
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-dismiss alerts after 4s
setTimeout(()=>{document.querySelectorAll('.flash-success,.flash-error').forEach(el=>el.style.display='none')},4000);
// Confirm deletes
document.querySelectorAll('.btn-delete').forEach(btn=>{
  btn.addEventListener('click',e=>{if(!confirm('Supprimer cet élément ?'))e.preventDefault()});
});
</script>
</body>
</html>
