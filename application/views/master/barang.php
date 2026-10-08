<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title"><i class="bi bi-box-seam me-2"></i>Master Data Barang BMD</h1>
            <p class="page-subtitle">Kelola kode dan data barang milik daerah.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportBarang">
                <i class="bi bi-file-earmark-excel me-1"></i>Import Excel
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBarang" onclick="openAddModal()">
                <i class="bi bi-plus-lg me-1"></i>Tambah Barang
            </button>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small mb-1">Cari</label>
                <input type="text" name="q" class="form-control" value="<?= e($filter['q']??'') ?>" placeholder="Kode atau nama barang...">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach (['Tanah','Peralatan dan Mesin','Gedung dan Bangunan','Jalan, Jaringan dan Irigasi','Aset Tetap Lainnya','Konstruksi Dalam Pengerjaan'] as $k): ?>
                    <option value="<?= $k ?>" <?= ($filter['kategori']??'') === $k ? 'selected' : '' ?>><?= $k ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i> Filter</button>
                <a href="<?= site_url('master/barang') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt-barang">
                <thead class="table-light">
                    <tr><th>#</th><th>Kode Barang</th><th>Nama Barang</th><th>Satuan</th><th>Kategori</th><th class="text-end">Harga Standar</th><th class="text-center">Status</th><th class="text-center" width="100">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data barang.</td></tr>
                    <?php else: foreach ($list as $i => $b): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><code><?= e($b->kode_barang) ?></code></td>
                        <td><?= e($b->nama_barang) ?></td>
                        <td><?= e($b->satuan) ?></td>
                        <td><small><?= e($b->kategori) ?></small></td>
                        <td class="text-end"><?= rupiah($b->harga_standar) ?></td>
                        <td class="text-center"><span class="badge bg-<?= $b->is_active ? 'success' : 'secondary' ?>"><?= $b->is_active ? 'Aktif' : 'Nonaktif' ?></span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary btn-edit-barang"
                                data-id="<?= (int)$b->id ?>"
                                data-kode="<?= e($b->kode_barang) ?>"
                                data-nama="<?= e($b->nama_barang) ?>"
                                data-satuan="<?= e($b->satuan) ?>"
                                data-kategori="<?= e($b->kategori) ?>"
                                data-harga="<?= (float)$b->harga_standar ?>"
                                data-ket="<?= e($b->keterangan) ?>"
                                data-active="<?= (int)$b->is_active ?>"><i class="bi bi-pencil"></i>
                            </button>
                            <form method="post" action="<?= site_url('master/barang') ?>" class="d-inline" onsubmit="return confirm('Hapus barang ini?')">
                                <?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$b->id ?>">
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
<div class="modal fade" id="modalBarang" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= site_url('master/barang') ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="barang_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-barang">Tambah Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Kode Barang <span class="text-danger">*</span></label><input type="text" name="kode_barang" id="kode_barang" class="form-control" required maxlength="50" placeholder="1.3.2.10.01.02.001"></div>
                        <div class="col-md-8"><label class="form-label">Nama Barang <span class="text-danger">*</span></label><input type="text" name="nama_barang" id="nama_barang" class="form-control" required maxlength="200"></div>
                        <div class="col-md-3"><label class="form-label">Satuan <span class="text-danger">*</span></label><input type="text" name="satuan" id="satuan_barang" class="form-control" required maxlength="30" value="Unit"></div>
                        <div class="col-md-5"><label class="form-label">Kategori</label>
                            <select name="kategori" id="kategori_barang" class="form-select">
                                <?php foreach (['Tanah','Peralatan dan Mesin','Gedung dan Bangunan','Jalan, Jaringan dan Irigasi','Aset Tetap Lainnya','Konstruksi Dalam Pengerjaan'] as $k): ?>
                                <option value="<?= $k ?>"><?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Harga Standar (Rp)</label><input type="number" name="harga_standar" id="harga_standar_b" class="form-control" min="0" step="1000" value="0"></div>
                        <div class="col-md-8"><label class="form-label">Keterangan</label><input type="text" name="keterangan" id="ket_barang" class="form-control" maxlength="255"></div>
                        <div class="col-md-4"><label class="form-label">Status</label><select name="is_active" id="is_active_b" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
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

<!-- Modal Import -->
<div class="modal fade" id="modalImportBarang" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= site_url('master/barang') ?>" enctype="multipart/form-data">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="import">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i> Format file: <strong>.xlsx</strong> atau <strong>.xls</strong>. 
                        Urutan kolom: Kode Barang, Nama Barang, Satuan, Kategori, Harga Standar, Keterangan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel</label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="skip_duplicate" value="1" id="skipCheck" checked>
                        <label class="form-check-label" for="skipCheck">Lompati jika Kode Barang sudah ada</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i>Proses Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal(){
    $('#modal-title-barang').text('Tambah Barang');
    $('#barang_id').val('');
    $('#kode_barang,#nama_barang,#ket_barang').val('');
    $('#satuan_barang').val('Unit');
    $('#kategori_barang').val('Peralatan dan Mesin');
    $('#harga_standar_b').val('0');
    $('#is_active_b').val('1');
}
window.addEventListener('load', function() {
    if($.fn.DataTable) $('#dt-barang').DataTable({paging:true,ordering:true,language:{url:'//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'}});
    $(document).on('click','.btn-edit-barang',function(){
        const d=$(this).data();
        $('#modal-title-barang').text('Edit Barang');
        $('#barang_id').val(d.id);
        $('#kode_barang').val(d.kode);
        $('#nama_barang').val(d.nama);
        $('#satuan_barang').val(d.satuan);
        $('#kategori_barang').val(d.kategori);
        $('#harga_standar_b').val(d.harga);
        $('#ket_barang').val(d.ket);
        $('#is_active_b').val(d.active);
        new bootstrap.Modal(document.getElementById('modalBarang')).show();
    });
});
</script>
