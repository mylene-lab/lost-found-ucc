    </div><!-- /content-area -->
</div><!-- /main-wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<script>
// Auto-dismiss alerts
setTimeout(()=>{ document.querySelectorAll('.alert').forEach(a=>new bootstrap.Alert(a).close()) }, 4000);

// Confirm delete
$('[data-confirm]').on('click',function(e){
    if(!confirm($(this).data('confirm'))) e.preventDefault();
});
</script>

<?php if(isset($extraJs)) echo $extraJs; ?>
</body>
</html>
