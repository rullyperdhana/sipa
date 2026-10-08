<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title"><i class="bi bi-file-earmark-text me-2"></i>Detail Usulan</h1>
            <p class="page-subtitle"><strong><?= e($usulan->nomor_usulan) ?></strong> &middot; <?= badge_status($usulan->status) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url("rkbmd/{$jenis}") ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
            <?php if (in_array($usulan->status,['draft','revisi']) && in_array($this->auth->user()->role,['admin','skpd'])): ?>
            <a href="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
            <?php endif; ?>
            <a href="<?= site_url("rkbmd/{$jenis}/cetak/{$usulan->id}") ?>" target="_blank" class="btn btn-outline-info"><i class="bi bi-printer me-1"></i>Cetak</a>
            <a href="<?= site_url("rkbmd/{$jenis}/excel/{$usulan->id}") ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        </div>
    </div>
</div>

<?php if ($usulan->catatan_verifikator): ?>
<div class="alert alert-<?= $usulan->status === 'ditolak' ? 'danger' : ($usulan->status === 'revisi' ? 'warning' : 'info') ?> d-flex gap-3">
    <i class="bi bi-chat-left-text-fill fs-4"></i>
    <div>
        <strong>Catatan Verifikator BPKAD:</strong>
        <div class="mt-1"><?= nl2br(e($usulan->catatan_verifikator)) ?></div>
        <?php if ($usulan->verified_at): ?><small class="text-muted">Diverifikasi: <?= tanggal_id($usulan->verified_at) ?></small><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Info Usulan -->
<div class="card mb-3">
    <div class="card-header"><strong>Informasi Usulan</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><small class="text-muted d-block">Nomor Usulan</small><strong><?= e($usulan->nomor_usulan) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Jenis</small><strong><?= label_jenis($jenis) ?></strong></div>
            <div class="col-md-3"><small class="text-muted d-block">SKPD</small><strong><?= e($usulan->nama_skpd) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Tahun Anggaran</small><strong><?= (int)$usulan->tahun_anggaran ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Tanggal Usulan</small><strong><?= tanggal_id($usulan->tanggal_usulan) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Total Item</small><strong><?= count($detail) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Total Nilai</small><strong class="text-primary"><?= rupiah($usulan->total_nilai) ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Laporan Nihil</small><strong><?= $usulan->is_nihil ? 'Ya' : 'Tidak' ?></strong></div>
            <div class="col-md-2"><small class="text-muted d-block">Status</small><?= badge_status($usulan->status) ?></div>
            <div class="col-md-6"><small class="text-muted d-block">Keterangan</small><?= e($usulan->keterangan ?: '-') ?></div>
        </div>
    </div>
</div>

<!-- Detail Item -->
<div class="card">
    <div class="card-header"><strong><i class="bi bi-list-ol me-2"></i>Daftar Item (<?= count($detail) ?> item)</strong></div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0 align-middle" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <?php if ($jenis === 'pengadaan'): ?>
                    <th>Program</th><th class="text-center">Jml Usulan</th><th class="text-center">Keb. Riil</th><th class="text-end">Harga Satuan</th><th class="text-end">Total</th>
                    <?php elseif ($jenis === 'pemeliharaan'): ?>
                    <th>Nama Pemeliharaan</th><th class="text-center">Jml</th><th class="text-center">Kondisi (B/RR/RB)</th><th class="text-end">Total</th>
                    <?php elseif ($jenis === 'pemanfaatan'): ?>
                    <th>Bentuk</th><th>No Register</th><th class="text-end">Harga Perolehan</th><th>Kondisi</th>
                    <?php elseif ($jenis === 'pemindahtanganan'): ?>
                    <th>Bentuk</th><th>No Register</th><th>Spesifikasi</th><th class="text-center">Thn</th><th class="text-end">Harga</th>
                    <?php elseif ($jenis === 'penghapusan'): ?>
                    <th>Kategori</th><th>No Register</th><th>Spesifikasi</th><th class="text-center">Thn</th><th class="text-end">Harga</th>
                    <?php endif; ?>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($detail)): ?>
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                        <?= $usulan->is_nihil ? 'Laporan nihil telah dipilih. Tidak ada item usulan untuk periode ini.' : 'Belum ada item.' ?>
                    </td>
                </tr>
                <?php else: foreach ($detail as $i => $d): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><small><?= e($d->kode_barang) ?></small></td>
                    <td><?= e($d->barang_nama) ?></td>
                    <?php if ($jenis === 'pengadaan'): ?>
                    <td><small><?= e($d->program_kegiatan) ?></small></td>
                    <td class="text-center"><?= (int)$d->usulan_jumlah ?> <?= e($d->usulan_satuan) ?></td>
                    <td class="text-center"><strong><?= (int)$d->kebutuhan_riil_jumlah ?> <?= e($d->kebutuhan_riil_satuan) ?></strong></td>
                    <td class="text-end"><?= rupiah($d->harga_satuan) ?></td>
                    <td class="text-end"><strong><?= rupiah($d->total_harga) ?></strong></td>
                    <?php elseif ($jenis === 'pemeliharaan'): ?>
                    <td><?= e($d->nama_pemeliharaan) ?></td>
                    <td class="text-center"><?= (int)$d->jumlah_pemeliharaan ?> <?= e($d->satuan_pemeliharaan) ?></td>
                    <td class="text-center"><span class="badge bg-success"><?= (int)$d->kondisi_b ?></span> <span class="badge bg-warning text-dark"><?= (int)$d->kondisi_rr ?></span> <span class="badge bg-danger"><?= (int)$d->kondisi_rb ?></span></td>
                    <td class="text-end"><strong><?= rupiah($d->total_harga) ?></strong></td>
                    <?php elseif ($jenis === 'pemanfaatan'): ?>
                    <td><span class="badge bg-info text-dark"><?= e(ucwords(str_replace('_',' ',$d->bentuk_pemanfaatan))) ?></span></td>
                    <td><?= e($d->no_register) ?></td>
                    <td class="text-end"><?= rupiah($d->harga_perolehan) ?></td>
                    <td class="text-center"><span class="badge bg-success"><?= (int)$d->kondisi_b ?></span> <span class="badge bg-warning text-dark"><?= (int)$d->kondisi_rr ?></span> <span class="badge bg-danger"><?= (int)$d->kondisi_rb ?></span></td>
                    <?php elseif ($jenis === 'pemindahtanganan'): ?>
                    <td><span class="badge bg-warning text-dark"><?= e(ucwords(str_replace('_',' ',$d->bentuk_pemindahtanganan))) ?></span></td>
                    <td><?= e($d->no_register) ?></td>
                    <td><small><?= e($d->spesifikasi) ?></small></td>
                    <td class="text-center"><?= (int)$d->tahun_perolehan ?></td>
                    <td class="text-end"><?= rupiah($d->harga_perolehan) ?></td>
                    <?php elseif ($jenis === 'penghapusan'): ?>
                    <td><span class="badge bg-danger"><?= e(ucwords(str_replace('_',' ',$d->kategori_penghapusan))) ?></span></td>
                    <td><?= e($d->no_register) ?></td>
                    <td><small><?= e($d->spesifikasi) ?></small></td>
                    <td class="text-center"><?= (int)$d->tahun_perolehan ?></td>
                    <td class="text-end"><?= rupiah($d->harga_perolehan) ?></td>
                    <?php endif; ?>
                    <td><small><?= e($d->keterangan) ?></small></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($detail) && in_array($jenis,['pengadaan','pemeliharaan'])): ?>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="<?= $jenis==='pengadaan' ? 7 : 6 ?>" class="text-end fw-bold">Total Nilai:</td>
                    <td class="text-end fw-bold"><?= rupiah($usulan->total_nilai) ?></td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
