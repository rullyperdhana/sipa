<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('_edit_header'); ?>
<?php $canEdit = in_array($usulan->status,['draft','revisi']) && in_array($this->auth->user()->role,['admin','skpd']); ?>

<?php if ($canEdit): ?>
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-plus-circle me-2"></i>Tambah Item Pemeliharaan</strong></div>
    <div class="card-body">
        <form method="post" action="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="add_detail">
            <div class="row g-2">
                <div class="col-md-12 mb-1">
                    <label class="form-label small">Nama Barang <span class="text-danger">*</span></label>
                    <select name="barang_id" class="form-select select2-barang" required>
                        <option value="">-- Pilih Barang BMD --</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label small">Program Kegiatan</label><input type="text" name="program_kegiatan" class="form-control form-control-sm" maxlength="255"></div>
                <div class="col-md-4"><label class="form-label small">Sub Kegiatan</label><input type="text" name="sub_kegiatan" class="form-control form-control-sm" maxlength="255"></div>
                <div class="col-md-4"><label class="form-label small">Output Kegiatan</label><input type="text" name="output_kegiatan" class="form-control form-control-sm" maxlength="255"></div>
                <div class="col-md-2"><label class="form-label small">Jml Barang</label><input type="number" name="jumlah_barang" class="form-control form-control-sm" min="0" value="1"></div>
                <div class="col-md-2"><label class="form-label small">Satuan</label><input type="text" name="satuan_barang" class="form-control form-control-sm" value="Unit"></div>
                <div class="col-md-4"><label class="form-label small">Status Barang</label>
                    <select name="status_barang" class="form-select form-select-sm">
                        <option>Digunakan Sendiri</option>
                        <option>Digunakan Pihak Ketiga</option>
                        <option>Tidak Digunakan</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label small">Kondisi B / RR / RB</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="kondisi_b" class="form-control" min="0" value="0" placeholder="B">
                        <input type="number" name="kondisi_rr" class="form-control" min="0" value="0" placeholder="RR">
                        <input type="number" name="kondisi_rb" class="form-control" min="0" value="0" placeholder="RB">
                    </div>
                </div>
                <div class="col-md-6"><label class="form-label small">Nama Pemeliharaan <span class="text-danger">*</span></label><input type="text" name="nama_pemeliharaan" class="form-control form-control-sm" maxlength="255" required></div>
                <div class="col-md-2"><label class="form-label small">Jml Pemeliharaan</label><input type="number" name="jumlah_pemeliharaan" class="form-control form-control-sm" min="1" value="1" id="jml_pml"></div>
                <div class="col-md-2"><label class="form-label small">Satuan</label><input type="text" name="satuan_pemeliharaan" class="form-control form-control-sm" value="Unit"></div>
                <div class="col-md-3"><label class="form-label small">Harga Satuan</label><input type="number" name="harga_satuan" class="form-control form-control-sm" min="0" step="0.01" inputmode="decimal" value="0.00" id="harga_pml"></div>
                <div class="col-md-3"><label class="form-label small">Total</label><input type="text" class="form-control form-control-sm bg-light" id="preview_pml" readonly value="Rp 0"></div>
                <div class="col-md-6"><label class="form-label small">Keterangan</label><input type="text" name="keterangan" class="form-control form-control-sm" maxlength="500"></div>
            </div>
            <div class="mt-3"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Item</button></div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-list-ol me-2"></i>Daftar Item Pemeliharaan (<?= count($detail) ?> item)</strong>
        <strong class="text-primary"><?= rupiah($usulan->total_nilai) ?></strong>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th>No</th><th>Nama Barang</th><th>Program</th><th>Nama Pemeliharaan</th>
                    <th class="text-center">Jml</th><th class="text-center">Kondisi (B/RR/RB)</th>
                    <th class="text-end">Harga</th><th class="text-end">Total</th>
                    <?php if ($canEdit): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($detail)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="bi bi-inbox d-block fs-2 mb-1"></i>Belum ada item.</td></tr>
                <?php else: foreach ($detail as $i => $d): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= e($d->barang_nama) ?><br><small class="text-muted"><?= e($d->kode_barang) ?></small></td>
                    <td><small><?= e($d->program_kegiatan) ?></small></td>
                    <td><?= e($d->nama_pemeliharaan) ?></td>
                    <td class="text-center"><?= (int)$d->jumlah_pemeliharaan ?> <?= e($d->satuan_pemeliharaan) ?></td>
                    <td class="text-center"><span class="badge bg-success"><?= (int)$d->kondisi_b ?></span> <span class="badge bg-warning text-dark"><?= (int)$d->kondisi_rr ?></span> <span class="badge bg-danger"><?= (int)$d->kondisi_rb ?></span></td>
                    <td class="text-end"><?= rupiah($d->harga_satuan) ?></td>
                    <td class="text-end"><strong><?= rupiah($d->total_harga) ?></strong></td>
                    <?php if ($canEdit): ?>
                    <td>
                        <form method="post" action="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>" onsubmit="return confirm('Hapus item?')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="delete_detail">
                            <input type="hidden" name="detail_id" value="<?= (int)$d->id ?>">
                            <button type="submit" class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($detail)): ?>
            <tfoot class="table-secondary">
                <tr><td colspan="7" class="text-end fw-bold">Total:</td><td class="text-end fw-bold"><?= rupiah($usulan->total_nilai) ?></td><?php if($canEdit): ?><td></td><?php endif; ?></tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php $this->load->view('_edit_footer'); ?>
<script>
window.addEventListener('load', function() {
    $('.select2-barang').select2({theme:'bootstrap-5',width:'100%',placeholder:'-- Pilih Barang BMD --',minimumInputLength:0,ajax:{url:'<?= site_url("ajax/barang/search") ?>',dataType:'json',delay:300,data:p=>({q:p.term}),processResults:d=>({results:d.results})}});
    function updateTotal(){ const jml=parseInt($('#jml_pml').val())||0; const h=parseFloat($('#harga_pml').val())||0; $('#preview_pml').val('Rp '+(jml*h).toLocaleString('id-ID')); }
    $('#jml_pml,#harga_pml').on('input',updateTotal);
});
</script>
