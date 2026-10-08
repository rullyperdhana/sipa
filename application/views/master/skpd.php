<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="bi bi-bank me-2"></i>Master Data SKPD</h1>
            <p class="page-subtitle">Kelola data Satuan Kerja Perangkat Daerah.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSkpd" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i>Tambah SKPD
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt-skpd">
                <thead class="table-light">
                    <tr><th>#</th><th>Kode SKPD</th><th>Nama SKPD</th><th>Kepala SKPD</th><th>Pengurus</th><th>Telp</th><th class="text-center">Status</th><th class="text-center" width="120">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data SKPD.</td></tr>
                    <?php else: foreach ($list as $i => $s): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><strong><?= e($s->kode_skpd) ?></strong></td>
                        <td><?= e($s->nama_skpd) ?></td>
                        <td><?= e($s->kepala_skpd ?: '-') ?><?php if ($s->nip_kepala): ?><br><small class="text-muted">NIP: <?= e($s->nip_kepala) ?></small><?php endif; ?></td>
                        <td><?= e($s->nama_pengurus ?: '-') ?><?php if ($s->nip_pengurus): ?><br><small class="text-muted">NIP: <?= e($s->nip_pengurus) ?></small><?php endif; ?></td>
                        <td><?= e($s->telepon ?: '-') ?></td>
                        <td class="text-center"><span class="badge bg-<?= $s->is_active ? 'success' : 'secondary' ?>"><?= $s->is_active ? 'Aktif' : 'Nonaktif' ?></span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary btn-edit-skpd"
                                data-id="<?= (int)$s->id ?>"
                                data-kode="<?= e($s->kode_skpd) ?>"
                                data-nama="<?= e($s->nama_skpd) ?>"
                                data-kepala="<?= e($s->kepala_skpd) ?>"
                                data-nip="<?= e($s->nip_kepala) ?>"
                                data-jabatan="<?= e($s->jabatan_kepala) ?>"
                                data-peng="<?= e($s->nama_pengurus) ?>"
                                data-npeng="<?= e($s->nip_pengurus) ?>"
                                data-alamat="<?= e($s->alamat) ?>"
                                data-telp="<?= e($s->telepon) ?>"
                                data-active="<?= (int)$s->is_active ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="post" action="<?= site_url('master/skpd') ?>" class="d-inline" onsubmit="return confirm('Hapus SKPD ini?')">
                                <?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$s->id ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalSkpd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= site_url('master/skpd') ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="skpd_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Tambah SKPD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Kode SKPD <span class="text-danger">*</span></label>
                            <input type="text" name="kode_skpd" id="kode_skpd" class="form-control" required maxlength="20" placeholder="1.05.01">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nama SKPD <span class="text-danger">*</span></label>
                            <input type="text" name="nama_skpd" id="nama_skpd" class="form-control" required maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Kepala SKPD</label>
                            <input type="text" name="kepala_skpd" id="kepala_skpd" class="form-control" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP Kepala</label>
                            <input type="text" name="nip_kepala" id="nip_kepala" class="form-control" maxlength="25">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Jabatan Kepala</label>
                            <input type="text" name="jabatan_kepala" id="jabatan_kepala" class="form-control" maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Pengurus Barang</label>
                            <input type="text" name="nama_pengurus" id="nama_pengurus" class="form-control" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP Pengurus Barang</label>
                            <input type="text" name="nip_pengurus" id="nip_pengurus" class="form-control" maxlength="25">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" id="telepon_skpd" class="form-control" maxlength="20">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat_skpd" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="is_active_skpd" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
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
    window.openAddModal = function() {
        $('#modal-title').text('Tambah SKPD');
        $('#skpd_id').val('');
        $('#kode_skpd,#nama_skpd,#kepala_skpd,#nip_kepala,#jabatan_kepala,#telepon_skpd,#alamat_skpd,#nama_pengurus,#nip_pengurus').val('');
        $('#is_active_skpd').val('1');
    };

    if($.fn.DataTable) $('#dt-skpd').DataTable({paging:true,ordering:true,language:{url:'//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'}});
    $(document).on('click','.btn-edit-skpd',function(){
        const d=$(this).data();
        $('#modal-title').text('Edit SKPD');
        $('#skpd_id').val(d.id);
        $('#kode_skpd').val(d.kode);
        $('#nama_skpd').val(d.nama);
        $('#kepala_skpd').val(d.kepala);
        $('#nip_kepala').val(d.nip);
        $('#jabatan_kepala').val(d.jabatan);
        $('#telepon_skpd').val(d.telp);
        $('#nama_pengurus').val(d.peng);
        $('#nip_pengurus').val(d.npeng);
        $('#alamat_skpd').val(d.alamat);
        $('#is_active_skpd').val(d.active);
        new bootstrap.Modal(document.getElementById('modalSkpd')).show();
    });
});
</script>
