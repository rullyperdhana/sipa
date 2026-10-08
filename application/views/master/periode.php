<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="bi bi-calendar3 me-2"></i>Master Periode RKBMD</h1>
            <p class="page-subtitle">Kelola periode penyusunan RKBMD.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPeriode" onclick="resetForm()">
            <i class="bi bi-plus-lg me-1"></i>Tambah Periode
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Tahun</th><th>Nama Periode</th><th>Mulai</th><th>Selesai</th><th class="text-center">Status</th><th>Keterangan</th><th class="text-center" width="80">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?><tr><td colspan="8" class="text-center py-4 text-muted">Belum ada periode.</td></tr>
                <?php else: foreach ($list as $i => $p): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><strong><?= (int)$p->tahun ?></strong></td>
                    <td><?= e($p->nama_periode) ?></td>
                    <td><?= tanggal_id($p->tanggal_mulai) ?></td>
                    <td><?= tanggal_id($p->tanggal_selesai) ?></td>
                    <td class="text-center"><span class="badge bg-<?= $p->status==='open' ? 'success' : 'secondary' ?>"><?= $p->status==='open' ? 'Buka' : 'Tutup' ?></span></td>
                    <td><small><?= e($p->keterangan ?: '-') ?></small></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-edit-periode"
                            data-id="<?= (int)$p->id ?>"
                            data-tahun="<?= (int)$p->tahun ?>"
                            data-nama="<?= e($p->nama_periode) ?>"
                            data-mulai="<?= e($p->tanggal_mulai) ?>"
                            data-selesai="<?= e($p->tanggal_selesai) ?>"
                            data-status="<?= e($p->status) ?>"
                            data-ket="<?= e($p->keterangan) ?>"><i class="bi bi-pencil"></i></button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalPeriode" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= site_url('master/periode') ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="id" id="periode_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-periode">Tambah Periode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Tahun <span class="text-danger">*</span></label><input type="number" name="tahun" id="periode_tahun" class="form-control" required min="2020" max="2099" value="<?= date('Y')+1 ?>"></div>
                        <div class="col-md-8"><label class="form-label">Nama Periode <span class="text-danger">*</span></label><input type="text" name="nama_periode" id="periode_nama" class="form-control" required maxlength="100"></div>
                        <div class="col-md-6"><label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label><input type="date" name="tanggal_mulai" id="periode_mulai" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label><input type="date" name="tanggal_selesai" id="periode_selesai" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Status</label><select name="status" id="periode_status" class="form-select"><option value="open">Buka</option><option value="closed">Tutup</option></select></div>
                        <div class="col-md-8"><label class="form-label">Keterangan</label><input type="text" name="keterangan" id="periode_ket" class="form-control" maxlength="255"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    window.resetForm = function() {
        $('#modal-title-periode').text('Tambah Periode');
        $('#periode_id').val('');
        $('#periode_nama,#periode_ket').val('');
        $('#periode_tahun').val(<?= date('Y')+1 ?>);
        $('#periode_mulai').val('<?= date('Y') ?>-01-01');
        $('#periode_selesai').val('<?= date('Y') ?>-12-31');
        $('#periode_status').val('open');
    };

    $(document).on('click','.btn-edit-periode',function(){
        const d=$(this).data();
        $('#modal-title-periode').text('Edit Periode');
        $('#periode_id').val(d.id);
        $('#periode_tahun').val(d.tahun);
        $('#periode_nama').val(d.nama);
        $('#periode_mulai').val(d.mulai);
        $('#periode_selesai').val(d.selesai);
        $('#periode_status').val(d.status);
        $('#periode_ket').val(d.ket);
        new bootstrap.Modal(document.getElementById('modalPeriode')).show();
    });
});
</script>
