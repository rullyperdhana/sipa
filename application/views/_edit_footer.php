<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
// Partial: footer aksi submit usulan
$canEdit = in_array($usulan->status, ['draft','revisi']) && in_array($this->auth->user()->role, ['admin','skpd']);
?>

<?php if ($canEdit): ?>
<div class="card mt-3 border-primary">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-1"><i class="bi bi-send-check"></i> Submit ke Verifikator</h6>
                <small class="text-muted">
                    Setelah disubmit, usulan tidak dapat diedit kecuali diminta revisi oleh BPKAD.
                    Pastikan semua data sudah benar.
                </small>
            </div>
            <form method="post" action="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>" id="formSubmitUsulan">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="submit">
                <button type="button" class="btn btn-success btn-lg" id="btnSubmitUsulan" <?= empty($detail) ? 'disabled' : '' ?>>
                    <i class="bi bi-send-fill"></i> Submit Usulan
                </button>
            </form>
        </div>
        <?php if (empty($detail)): ?>
        <small class="text-danger d-block mt-2">
            <i class="bi bi-exclamation-circle"></i> Tambahkan minimal 1 item terlebih dahulu sebelum submit.
        </small>
        <?php endif; ?>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    $('#btnSubmitUsulan').on('click', function(){
        Swal.fire({
            icon: 'question',
            title: 'Submit Usulan?',
            html: 'Usulan <strong><?= e($usulan->nomor_usulan) ?></strong> akan diteruskan ke BPKAD untuk diverifikasi.<br>Anda tidak akan dapat mengedit lagi setelah submit.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Submit',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#198754'
        }).then(r => { if (r.isConfirmed) $('#formSubmitUsulan').submit(); });
    });
});
</script>
<?php endif; ?>

<?php if ($usulan->status === 'disetujui' || $usulan->status === 'ditolak'): ?>
<div class="alert alert-<?= $usulan->status === 'disetujui' ? 'success' : 'danger' ?> mt-3">
    <strong><i class="bi bi-<?= $usulan->status === 'disetujui' ? 'check-circle' : 'x-circle' ?>-fill"></i>
        Usulan <?= $usulan->status === 'disetujui' ? 'Disetujui' : 'Ditolak' ?></strong>
    <?php if ($usulan->catatan_verifikator): ?>
    <div class="mt-1"><?= nl2br(e($usulan->catatan_verifikator)) ?></div>
    <?php endif; ?>
    <?php if ($usulan->verified_at): ?>
    <small class="d-block mt-1">Diverifikasi pada <?= tanggal_id($usulan->verified_at) ?></small>
    <?php endif; ?>
</div>
<?php endif; ?>
