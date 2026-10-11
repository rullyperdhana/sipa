<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    </div><!-- /content-wrapper -->
</main><!-- /main-wrapper -->

<!-- Global WhatsApp Notification Modal -->
<?php $this->load->view('templates/wa_modal'); ?>

<!-- Global Evidence Document Preview Modal -->
<?php $this->load->view('templates/preview_modal'); ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
$idleEnabled = function_exists('get_setting') ? (get_setting('idle_timeout_enabled', '1') === '1') : TRUE;
$idleMinutes = function_exists('get_setting') ? (int) get_setting('idle_timeout_minutes', 15) : 15;
if ($idleMinutes < 1) $idleMinutes = 15;
$idleWarningSec = function_exists('get_setting') ? (int) get_setting('idle_warning_seconds', 60) : 60;
if ($idleWarningSec < 0) $idleWarningSec = 60;
?>
<!-- App Configuration (Loaded before scripts) -->
<script>
window.appConfig = {
    baseUrl: '<?= site_url() ?>',
    csrfName: '<?= $this->security->get_csrf_token_name() ?>',
    csrfHash: '<?= $this->security->get_csrf_hash() ?>',
    userRole: '<?= ($this->auth && $this->auth->user()) ? $this->auth->user()->role : "" ?>',
    idleTimeout: {
        enabled: <?= $idleEnabled ? 'true' : 'false' ?>,
        timeoutMinutes: <?= $idleMinutes ?>,
        warningSeconds: <?= $idleWarningSec ?>,
        logoutUrl: '<?= site_url("logout?reason=idle") ?>',
        pingUrl: '<?= site_url("ajax/ping_session") ?>'
    }
};
</script>

<!-- App JS -->
<script src="<?= base_url('assets/js/app.js?v=' . (file_exists(FCPATH . 'assets/js/app.js') ? filemtime(FCPATH . 'assets/js/app.js') : '2.8.2')) ?>"></script>
<!-- SSH & SBU Module JS -->
<script src="<?= base_url('assets/js/ssh_module.js?v=' . (file_exists(FCPATH . 'assets/js/ssh_module.js') ? filemtime(FCPATH . 'assets/js/ssh_module.js') : '2.8.2')) ?>"></script>

</body>
</html>
