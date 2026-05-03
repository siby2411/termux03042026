  </div><!-- .page-body -->
  <div class="page-footer">
    <div><span class="gd">Ω Omega Informatique Consulting</span> · Mohamet Siby · Consultant en Informatique · Dakar, Sénégal</div>
    <div>PharmaSen v<?=APP_VERSION?> · <?=date('Y')?></div>
  </div>
</div><!-- .main-wrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Horloge bandeau
function ck(){
  const c=document.getElementById('band-clock');
  if(c) c.textContent=new Date().toLocaleString('fr-SN');
}
setInterval(ck,1000); ck();
</script>
