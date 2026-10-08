<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title"><i class="bi bi-check2-square me-2"></i>Verifikasi Usulan</h1>
            <p class="page-subtitle"><strong><?= e($usulan->nomor_usulan) ?></strong> &middot; <?= badge_status($usulan->status) ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('verifikasi') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
            <a href="<?= site_url("rkbmd/{$usulan->jenis_usulan}/cetak/{$usulan->id}") ?>" target="_blank" class="btn btn-outline-info"><i class="bi bi-printer me-1"></i>Cetak</a>
        </div>
    </div>
</div>

<!-- Info Usulan -->
<div class="card mb-3">
    <div class="card-header"><strong>Informasi Usulan</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><small class="text-muted d-block">Nomor Usulan</small><strong><?= e($usulan->nomor_usulan) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Jenis</small><strong><?= label_jenis($usulan->jenis_usulan) ?></strong></div>
            <div class="col-md-3"><small class="text-muted d-block">SKPD</small><strong><?= e($usulan->nama_skpd) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Tahun Anggaran</small><strong><?= (int)$usulan->tahun_anggaran ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Tanggal</small><strong><?= tanggal_id($usulan->tanggal_usulan) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Total Item</small><strong><?= count($detail) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Total Nilai</small><strong class="text-primary"><?= rupiah($usulan->total_nilai) ?></strong></div>
            <?php if ($usulan->keterangan): ?><div class="col-md-8"><small class="text-muted d-block">Keterangan SKPD</small><?= e($usulan->keterangan) ?></div><?php endif; ?>
        </div>
    </div>
</div>

<?php if ($usulan->catatan_verifikator): ?>
<div class="alert alert-<?= $usulan->status==='ditolak' ? 'danger' : ($usulan->status==='revisi' ? 'warning' : 'info') ?> mb-3">
    <strong>Catatan Verifikator Sebelumnya:</strong><br>
    <?= nl2br(e($usulan->catatan_verifikator)) ?>
</div>
<?php endif; ?>

<!-- Detail Item -->
<div class="card mb-3">
    <div class="card-header"><strong>Daftar Item (<?= count($detail) ?> item)</strong></div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th>No</th><th>Kode Barang</th><th>Nama Barang</th>
                    <?php if ($usulan->jenis_usulan==='pengadaan'): ?><th>Program</th><th class="text-center">Jml Usulan</th><th class="text-center">Keb. Riil</th><th class="text-end">Harga Satuan</th><th class="text-end">Total</th>
                    <?php elseif ($usulan->jenis_usulan==='pemeliharaan'): ?><th>Pemeliharaan</th><th class="text-center">Jml</th><th class="text-center">Kondisi</th><th class="text-end">Total</th>
                    <?php else: ?><th>Detail</th><th>No Register</th><th class="text-end">Harga</th>
                    <?php endif; ?>
                    <th>Ket</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($detail)): ?><tr><td colspan="9" class="text-center py-3 text-muted">Tidak ada item.</td></tr>
                <?php else: foreach ($detail as $i => $d): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><small><?= e($d->kode_barang) ?></small></td>
                    <td><?= e($d->barang_nama) ?></td>
                    <?php if ($usulan->jenis_usulan==='pengadaan'): ?>
                    <td><small><?= e($d->program_kegiatan) ?></small></td>
                    <td class="text-center"><?= (int)$d->usulan_jumlah ?> <?= e($d->usulan_satuan) ?></td>
                    <td class="text-center"><strong><?= (int)$d->kebutuhan_riil_jumlah ?></strong></td>
                    <td class="text-end"><?= rupiah($d->harga_satuan) ?></td>
                    <td class="text-end"><strong><?= rupiah($d->total_harga) ?></strong></td>
                    <?php elseif ($usulan->jenis_usulan==='pemeliharaan'): ?>
                    <td><?= e($d->nama_pemeliharaan) ?></td>
                    <td class="text-center"><?= (int)$d->jumlah_pemeliharaan ?></td>
                    <td class="text-center"><span class="badge bg-success"><?= (int)$d->kondisi_b ?></span> <span class="badge bg-warning text-dark"><?= (int)$d->kondisi_rr ?></span> <span class="badge bg-danger"><?= (int)$d->kondisi_rb ?></span></td>
                    <td class="text-end"><strong><?= rupiah($d->total_harga) ?></strong></td>
                    <?php else: ?>
                    <td><?php
                        if ($usulan->jenis_usulan==='pemanfaatan') echo e(ucwords(str_replace('_',' ',$d->bentuk_pemanfaatan)));
                        elseif ($usulan->jenis_usulan==='pemindahtanganan') echo e(ucwords(str_replace('_',' ',$d->bentuk_pemindahtanganan)));
                        elseif ($usulan->jenis_usulan==='penghapusan') echo e(ucwords(str_replace('_',' ',$d->kategori_penghapusan)));
                    ?></td>
                    <td><?= e($d->no_register ?? '-') ?></td>
                    <td class="text-end"><?= rupiah($d->harga_perolehan ?? 0) ?></td>
                    <?php endif; ?>
                    <td><small><?= e($d->keterangan) ?></small></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Panel Verifikasi -->
<?php if (in_array($usulan->status, ['diajukan','diverifikasi','disetujui'])): ?>
<div class="card border-primary">
    <div class="card-header bg-primary text-white">
        <strong><i class="bi bi-check2-circle me-2"></i><?= $usulan->status === 'disetujui' ? 'Buka Kembali / Revisi Usulan' : 'Proses Verifikasi' ?></strong>
    </div>
    <div class="card-body">
        <?php if ($usulan->status === 'disetujui'): ?>
            <div class="alert alert-warning small">Usulan ini sudah <strong>Disetujui</strong>. Gunakan tombol "Minta Revisi" untuk membuka kembali akses edit bagi SKPD jika ada pembatalan/perubahan.</div>
        <?php endif; ?>
        <form method="post" action="<?= site_url("verifikasi/proses/{$usulan->id}") ?>" id="form-verif">
            <?= csrf_input() ?>
            <div class="mb-3">
                <label class="form-label">Catatan Verifikator</label>
                <textarea name="catatan" class="form-control" rows="3" id="catatan-verif" placeholder="Isi catatan jika diperlukan (wajib untuk tolak/revisi)..."></textarea>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <?php if ($usulan->status !== 'disetujui'): ?>
                <button type="button" class="btn btn-success" onclick="prosesVerif('setuju')">
                    <i class="bi bi-check-circle-fill me-1"></i>Setujui
                </button>
                <button type="button" class="btn btn-primary" onclick="prosesVerif('verifikasi')">
                    <i class="bi bi-check2 me-1"></i>Tandai Diverifikasi
                </button>
                <button type="button" class="btn btn-warning" onclick="prosesVerif('revisi')">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Minta Revisi
                </button>
                <button type="button" class="btn btn-danger" onclick="prosesVerif('tolak')">
                    <i class="bi bi-x-circle-fill me-1"></i>Tolak
                </button>
                <?php else: ?>
                <button type="button" class="btn btn-warning" onclick="prosesVerif('revisi')">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Minta Revisi
                </button>
                <?php endif; ?>
            </div>
            <input type="hidden" name="aksi" id="aksi-input">
        </form>
    </div>
</div>
<?php elseif ($usulan->status === 'disetujui'): ?>
<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><strong>Usulan ini telah disetujui.</strong> <?= $usulan->approved_at ? 'Disetujui pada ' . tanggal_id($usulan->approved_at) : '' ?></div>
<?php elseif ($usulan->status === 'ditolak'): ?>
<div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i><strong>Usulan ini telah ditolak.</strong></div>
<?php endif; ?>

<script>
function prosesVerif(aksi){
    const labels = {setuju:'Setujui',verifikasi:'Tandai Diverifikasi',revisi:'Minta Revisi',tolak:'Tolak'};
    const colors = {setuju:'#198754',verifikasi:'#0d6efd',revisi:'#ffc107',tolak:'#dc3545'};
    const catatan = $('#catatan-verif').val();
    if((aksi==='tolak'||aksi==='revisi') && !catatan.trim()){
        Swal.fire({icon:'warning',title:'Catatan wajib diisi',text:'Mohon isi catatan untuk aksi '+labels[aksi]});
        return;
    }
    Swal.fire({
        icon:'question', title:labels[aksi]+' Usulan?',
        html:'Usulan <strong><?= e($usulan->nomor_usulan) ?></strong> akan diproses: <strong>'+labels[aksi]+'</strong>',
        showCancelButton:true, confirmButtonText:'Ya, Lanjutkan', cancelButtonText:'Batal',
        confirmButtonColor: colors[aksi]
    }).then(r=>{
        if(r.isConfirmed){ $('#aksi-input').val(aksi); $('#form-verif').submit(); }
    });
}
</script>
