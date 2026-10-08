<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
    /* Definisi warna ungu kustom karena tidak ada di default Bootstrap 5 */
    .bg-purple { background-color: #6f42c1 !important; color: #fff !important; }
    .border-purple { border-color: #6f42c1 !important; }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan & Rekap RKBMD</h1>
        <p class="page-subtitle">Laporan rekap seluruh usulan RKBMD.</p>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Tahun Anggaran</label>
                <input type="number" name="tahun" class="form-control" value="<?= (int)$tahun ?>" min="2020" max="2099" required>
            </div>
            <?php if ($this->currentUser->role !== 'skpd'): ?>
            <div class="col-md-4">
                <label class="form-label small mb-1">SKPD</label>
                <select name="skpd_id" class="form-select">
                    <option value="">-- Semua SKPD --</option>
                    <?php foreach ($skpd_list as $s): ?>
                    <option value="<?= (int)$s->id ?>" <?= (int)$skpd_id === (int)$s->id ? 'selected' : '' ?>><?= e($s->kode_skpd) ?> - <?= e($s->nama_skpd) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <label class="form-label small mb-1">Cari Keyword</label>
                <input type="text" name="q" class="form-control" value="<?= e($this->input->get('q')) ?>" placeholder="No. Usulan / Keterangan">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Rekap Summary -->
<h5 class="mb-3">Rekap Tahun <?= (int)$tahun ?></h5>
<div class="row g-3 mb-4">
    <?php
    $jenisColors = ['pengadaan'=>'primary','pemeliharaan'=>'info','pemanfaatan'=>'purple','pemindahtanganan'=>'warning','penghapusan'=>'danger'];
    foreach ($rekap as $jenis => $r):
        $color = $jenisColors[$jenis] ?? 'secondary';
    ?>
    <div class="col-lg-4 col-md-6">
        <div class="card border-<?= $color ?>">
            <div class="card-header bg-<?= $color ?> <?= in_array($color,['warning']) ? 'text-dark' : 'text-white' ?> d-flex justify-content-between align-items-center">
                <strong><?= label_jenis($jenis) ?></strong>
                <span class="badge bg-white text-dark"><?= $r['total'] ?> usulan</span>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-4"><div class="small text-muted">Total</div><div class="h5 mb-0"><?= $r['total'] ?></div></div>
                    <div class="col-4"><div class="small text-muted">Disetujui</div><div class="h5 mb-0 text-success"><?= $r['disetujui'] ?></div></div>
                    <div class="col-4"><div class="small text-muted">Nilai (Rp)</div><div class="small fw-bold text-primary"><?= rupiah($r['nilai']) ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Tabel Usulan -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Daftar Semua Usulan (<?= count($usulan) ?>)</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="dt-laporan">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nomor Usulan</th>
                    <th>Jenis</th>
                    <?php if ($this->auth->user()->role !== 'skpd'): ?><th>SKPD</th><?php endif; ?>
                    <th>Tanggal</th>
                    <th class="text-center">Item</th>
                    <th class="text-end">Total Nilai</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usulan as $i => $u): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><strong><?= e($u->nomor_usulan) ?></strong></td>
                    <td><?= label_jenis($u->jenis_usulan) ?></td>
                    <?php if ($this->auth->user()->role !== 'skpd'): ?><td><small><?= e($u->nama_skpd) ?></small></td><?php endif; ?>
                    <td><?= tanggal_id($u->tanggal_usulan) ?></td>
                    <td class="text-center"><?= (int)$u->total_item ?></td>
                    <td class="text-end"><?= rupiah($u->total_nilai) ?></td>
                    <td class="text-center"><?= badge_status($u->status) ?></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="<?= site_url("rkbmd/{$u->jenis_usulan}/detail/{$u->id}") ?>" class="btn btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="<?= site_url("rkbmd/{$u->jenis_usulan}/cetak/{$u->id}") ?>" target="_blank" class="btn btn-outline-info" title="Cetak"><i class="bi bi-printer"></i></a>
                            <a href="<?= site_url("rkbmd/{$u->jenis_usulan}/excel/{$u->id}") ?>" class="btn btn-outline-success" title="Excel"><i class="bi bi-file-earmark-excel"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    if($.fn.DataTable){
        $('#dt-laporan').DataTable({
            paging:true, searching:true, ordering:true, info:true, order:[],
            language:{
                url:'//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Tidak ada data usulan.</div>'
            }
        });
    }
});
</script>
