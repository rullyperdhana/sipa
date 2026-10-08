<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-check2-square me-2"></i>Verifikasi RKBMD</h1>
        <p class="page-subtitle">Kelola verifikasi usulan RKBMD dari seluruh SKPD.</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select class="form-select" name="status">
                    <?php foreach (['diajukan'=>'Diajukan (Menunggu)','diverifikasi'=>'Sudah Diverifikasi','disetujui'=>'Disetujui','ditolak'=>'Ditolak','revisi'=>'Perlu Revisi',''=>'Semua Status'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= ($filter['status']??'') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Jenis Usulan</label>
                <select class="form-select" name="jenis">
                    <option value="">Semua Jenis</option>
                    <?php foreach (['pengadaan','pemeliharaan','pemanfaatan','pemindahtanganan','penghapusan'] as $j): ?>
                    <option value="<?= $j ?>" <?= ($filter['jenis']??'') === $j ? 'selected' : '' ?>><?= label_jenis($j) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="<?= (int)($filter['tahun']??0) ?: '' ?>" placeholder="Semua" min="2020" max="2099">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Cari</label>
                <input type="text" name="q" class="form-control" value="<?= e($filter['q']??'') ?>" placeholder="No usulan, SKPD...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i> Filter</button>
                <a href="<?= site_url('verifikasi') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nomor Usulan</th>
                        <th>Jenis</th>
                        <th>SKPD</th>
                        <th>Tgl Diajukan</th>
                        <th class="text-center">Item</th>
                        <th class="text-end">Total Nilai</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usulan)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada usulan yang perlu diverifikasi.</td></tr>
                    <?php else: foreach ($usulan as $i => $u): ?>
                    <tr>
                        <td class="text-muted"><?= $i+1 ?></td>
                        <td><strong><?= e($u->nomor_usulan) ?></strong></td>
                        <td><?= label_jenis($u->jenis_usulan) ?></td>
                        <td><small><?= e($u->kode_skpd) ?></small><br><?= e($u->nama_skpd) ?></td>
                        <td><?= $u->submitted_at ? tanggal_id($u->submitted_at) : '-' ?></td>
                        <td class="text-center"><?= (int)$u->total_item ?></td>
                        <td class="text-end"><?= rupiah($u->total_nilai) ?></td>
                        <td class="text-center"><?= badge_status($u->status) ?></td>
                        <td class="text-center">
                            <a href="<?= site_url("verifikasi/detail/{$u->id}") ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-check2-circle me-1"></i>Proses
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
