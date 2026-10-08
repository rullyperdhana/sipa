<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('_edit_header'); ?>

<!-- Form Tambah Item Pengadaan -->
<?php
$canEdit = in_array($usulan->status, ['draft','revisi']) && in_array($this->auth->user()->role, ['admin','skpd']);
if ($canEdit):
?>
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-plus-circle me-2"></i>Tambah Item Pengadaan</strong></div>
    <div class="card-body">
        <form method="post" action="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="add_detail">
            <div class="row g-2">
                <div class="col-md-12 mb-2">
                    <label class="form-label small">Nama Barang <span class="text-danger">*</span></label>
                    <select name="barang_id" class="form-select select2-barang" required>
                        <option value="">-- Pilih / Cari Barang BMD --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Program Kegiatan</label>
                    <input type="text" name="program_kegiatan" class="form-control form-control-sm" maxlength="255" placeholder="Nama program">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Sub Kegiatan</label>
                    <input type="text" name="sub_kegiatan" class="form-control form-control-sm" maxlength="255" placeholder="Sub kegiatan">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Output Kegiatan</label>
                    <input type="text" name="output_kegiatan" class="form-control form-control-sm" maxlength="255" placeholder="Output">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Jml Usulan <span class="text-danger">*</span></label>
                    <input type="number" name="usulan_jumlah" class="form-control form-control-sm" min="0" value="0" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Satuan</label>
                    <input type="text" name="usulan_satuan" class="form-control form-control-sm" value="Unit" maxlength="30">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Jml Maks</label>
                    <input type="number" name="kebutuhan_maks_jumlah" class="form-control form-control-sm" min="0" value="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Satuan</label>
                    <input type="text" name="kebutuhan_maks_satuan" class="form-control form-control-sm" value="Unit" maxlength="30">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Optimalisasi</label>
                    <input type="number" name="optimalisasi_jumlah" class="form-control form-control-sm" min="0" value="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Satuan</label>
                    <input type="text" name="optimalisasi_satuan" class="form-control form-control-sm" value="Unit" maxlength="30">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Keb. Riil <span class="text-danger">*</span></label>
                    <input type="number" name="kebutuhan_riil_jumlah" class="form-control form-control-sm" min="1" value="1" required id="riil_jumlah">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Satuan</label>
                    <input type="text" name="kebutuhan_riil_satuan" class="form-control form-control-sm" value="Unit" maxlength="30">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Harga Satuan <span class="text-danger">*</span></label>
                    <input type="number" name="harga_satuan" class="form-control form-control-sm" min="0" step="0.01" value="0.00" inputmode="decimal" id="harga_satuan" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Total Harga</label>
                    <input type="text" class="form-control form-control-sm bg-light" id="preview_total" readonly value="Rp 0">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control form-control-sm" maxlength="500">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Item</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Daftar Item -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-list-ol me-2"></i>Daftar Item (<?= count($detail) ?> item)</strong>
        <strong class="text-primary"><?= rupiah($usulan->total_nilai) ?></strong>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th width="35">No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Program</th>
                    <th class="text-center">Jml Usulan</th>
                    <th class="text-center">Keb. Riil</th>
                    <th class="text-end">Harga Satuan</th>
                    <th class="text-end">Total Harga</th>
                    <th>Ket</th>
                    <?php if ($canEdit): ?><th width="80" class="text-center">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($detail)): ?>
                <tr><td colspan="10" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-2 d-block mb-1"></i>Belum ada item. Tambahkan item di atas.</td></tr>
                <?php else: foreach ($detail as $i => $d): ?>
                <tr>
                    <td class="text-center"><?= $i+1 ?></td>
                    <td><small><?= e($d->kode_barang) ?></small></td>
                    <td><?= e($d->barang_nama) ?></td>
                    <td><small><?= e($d->program_kegiatan) ?></small></td>
                    <td class="text-center"><?= (int)$d->usulan_jumlah ?> <?= e($d->usulan_satuan) ?></td>
                    <td class="text-center"><strong><?= (int)$d->kebutuhan_riil_jumlah ?> <?= e($d->kebutuhan_riil_satuan) ?></strong></td>
                    <td class="text-end"><?= rupiah($d->harga_satuan) ?></td>
                    <td class="text-end"><strong><?= rupiah($d->total_harga) ?></strong></td>
                    <td><small><?= e($d->keterangan) ?></small></td>
                    <?php if ($canEdit): ?>
                    <td class="text-center">
                        <form method="post" action="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>" onsubmit="return confirm('Hapus item ini?')">
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
                <tr>
                    <td colspan="7" class="text-end fw-bold">Total:</td>
                    <td class="text-end fw-bold"><?= rupiah($usulan->total_nilai) ?></td>
                    <td colspan="<?= $canEdit ? 2 : 1 ?>"></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php $this->load->view('_edit_footer'); ?>

<script>
window.addEventListener('load', function() {
    // Select2 AJAX search barang
    $('.select2-barang').select2({
        theme:'bootstrap-5', width:'100%',
        placeholder:'-- Pilih / Cari Barang BMD --',
        minimumInputLength: 0,
        ajax:{
            url: '<?= site_url("ajax/barang/search") ?>',
            dataType:'json', delay:300,
            data: p => ({q: p.term}),
            processResults: d => ({results: d.results})
        }
    }).on('select2:select', function(e){
        const d = e.params.data;
        if(d.harga) $('#harga_satuan').val(d.harga).trigger('input');
    });

    // Preview total
    function updateTotal(){
        const jml = parseInt($('input[name=kebutuhan_riil_jumlah]').val())||0;
        const harga = parseFloat($('#harga_satuan').val())||0;
        const total = jml*harga;
        $('#preview_total').val('Rp '+total.toLocaleString('id-ID'));
    }
    $('input[name=kebutuhan_riil_jumlah], #harga_satuan').on('input', updateTotal);
});
</script>
