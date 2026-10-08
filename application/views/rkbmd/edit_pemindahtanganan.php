<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('_edit_header'); ?>
<?php $canEdit = in_array($usulan->status,['draft','revisi']) && in_array($this->auth->user()->role,['admin','skpd']); ?>

<?php if ($canEdit): ?>
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-plus-circle me-2"></i>Tambah Item Pemindahtanganan</strong></div>
    <div class="card-body">
        <form method="post" action="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="add_detail">
            <div class="row g-2">
                <div class="col-md-6"><label class="form-label small">Nama Barang <span class="text-danger">*</span></label>
                    <select name="barang_id" class="form-select select2-barang" required><option value="">-- Pilih Barang BMD --</option></select>
                </div>
                <div class="col-md-6"><label class="form-label small">Bentuk Pemindahtanganan</label>
                    <select name="bentuk_pemindahtanganan" class="form-select">
                        <option value="penjualan">Penjualan</option>
                        <option value="tukar_menukar">Tukar Menukar</option>
                        <option value="hibah">Hibah</option>
                        <option value="penyertaan_modal">Penyertaan Modal</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label small">No. Register <span class="text-danger">*</span></label><input type="text" name="no_register" class="form-control form-control-sm" required maxlength="50"></div>
                <div class="col-md-4"><label class="form-label small">Spesifikasi</label><input type="text" name="spesifikasi" class="form-control form-control-sm" maxlength="255"></div>
                <div class="col-md-2"><label class="form-label small">Thn Perolehan</label><input type="number" name="tahun_perolehan" class="form-control form-control-sm" required min="1980" max="<?= date('Y') ?>"></div>
                <div class="col-md-3"><label class="form-label small">Harga Perolehan</label><input type="number" name="harga_perolehan" class="form-control form-control-sm" min="0" step="0.01" inputmode="decimal" value="0.00"></div>
                <div class="col-md-9"><label class="form-label small">Keterangan</label><input type="text" name="keterangan" class="form-control form-control-sm" maxlength="500"></div>
            </div>
            <div class="mt-3"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Item</button></div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><strong><i class="bi bi-list-ol me-2"></i>Daftar Item Pemindahtanganan (<?= count($detail) ?> item)</strong></div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0" style="font-size:13px;">
            <thead class="table-light">
                <tr><th>No</th><th>Nama Barang</th><th>Bentuk</th><th>No Register</th><th>Spesifikasi</th><th class="text-center">Thn</th><th class="text-end">Harga Perolehan</th><th>Ket</th><?php if($canEdit): ?><th>Aksi</th><?php endif; ?></tr>
            </thead>
            <tbody>
                <?php if (empty($detail)): ?><tr><td colspan="9" class="text-center py-4 text-muted"><i class="bi bi-inbox d-block fs-2 mb-1"></i>Belum ada item.</td></tr>
                <?php else: foreach ($detail as $i => $d): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= e($d->barang_nama) ?><br><small class="text-muted"><?= e($d->kode_barang) ?></small></td>
                    <td><span class="badge bg-warning text-dark"><?= e(ucwords(str_replace('_',' ',$d->bentuk_pemindahtanganan))) ?></span></td>
                    <td><?= e($d->no_register) ?></td>
                    <td><small><?= e($d->spesifikasi) ?></small></td>
                    <td class="text-center"><?= (int)$d->tahun_perolehan ?></td>
                    <td class="text-end"><?= rupiah($d->harga_perolehan) ?></td>
                    <td><small><?= e($d->keterangan) ?></small></td>
                    <?php if($canEdit): ?>
                    <td><form method="post" action="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>" onsubmit="return confirm('Hapus?')"><?= csrf_input() ?><input type="hidden" name="action" value="delete_detail"><input type="hidden" name="detail_id" value="<?= (int)$d->id ?>"><button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->load->view('_edit_footer'); ?>
<script>window.addEventListener('load', function(){ $('.select2-barang').select2({theme:'bootstrap-5',width:'100%',placeholder:'-- Pilih --',minimumInputLength:0,ajax:{url:'<?= site_url("ajax/barang/search") ?>',dataType:'json',delay:300,data:p=>({q:p.term}),processResults:d=>({results:d.results})}}); });</script>
